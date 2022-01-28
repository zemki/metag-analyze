<?php

namespace App\Http\Controllers;

use App\AllCasesExport;
use App\Mail\VerificationEmail;
use App\Media;
use App\Project;
use App\Role;
use App\User;
use DB;
use Helper;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ProjectController extends Controller
{
    protected const UPDATESTRING = 'update';
    protected const PROJECT = 'project';
    protected const REQUIRED = 'required';
    protected const NULLABLE = 'nullable';
    protected const INPUTS = 'inputs';
    protected const MESSAGE = 'message';
protected const CASES = 'cases';

    public function index()
    {

        $data['projects'] = auth()->user()->projects()->get();
        $data['invites'] = auth()->user()->invites()->get();
        $data = $this->checkNewsletter($data);

        return view('projects.index', $data);
    }

    /**
     * @param Project $project
     * @return Factory|View
     * @throws AuthorizationException
     */
    public function show(Project $project)
    {
        if (auth()->user()->notOwnerNorInvited($project) && !auth()->user()->isAdmin())
        {
            abort(403);
        }
        $data['breadcrumb'] = [url('/') => 'Projects', '#' => substr($project->name, 0, 20) . '...'];
      //  $project->media = $project->media()->pluck('media.name')->toArray();
        //$data['data']['media'] = Media::all();
        $data[self::PROJECT] = $project;
        $data[self::CASES] = $project->cases;
        $data['casesWithUsers'] = $project->cases()->with('user')->get();
        $data[self::PROJECT.'media'] = $project->media()->pluck('media.name')->toArray();
        $data['invites'] = $project->invited()->get();

        return view('projects.show', $data);
    }

    /**
     * Show Create form
     * @return View return view with the form to insert a new project
     */
    public function create(Request $request)
    {
        if (auth()->user()->hasReachMaxNumberOfProjecs()) {
            abort(403, 'You reached the max number of projects');
        }
        $data['breadcrumb'] = [url('/') => 'Projects', '#' => 'Create'];
        return view('projects.create', $data);
    }

    public function store()
    {
        $media = request()->media;
        $attributes = request()->validate([
            'name' => self::REQUIRED,
            'description' => self::REQUIRED,
            'created_by' => self::REQUIRED,
            'is_locked' => self::NULLABLE,
            self::INPUTS => self::NULLABLE
        ]);
        $inputs = json_decode($attributes[self::INPUTS]);
        foreach ($inputs as $input) {
            $input->answers = array_filter($input->answers);
        }
        $attributes[self::INPUTS] = json_encode($inputs);
        $project = auth()->user()->projects()->create($attributes);
        $this->syncMedia($media, $project, $mToSync);

        $data['projects'] = auth()->user()->projects()->get();
        $data['invites'] = auth()->user()->invites()->get();
        $data[self::MESSAGE] = "project created!";
        $data = $this->checkNewsletter($data);
        return view('projects.index', $data);
    }

    /**
     * @param $media
     * @param $project
     * @param $mToSync
     */
    protected function syncMedia($media, $project, &$mToSync): void
    {
        if ($media) {
            $mToSync = array();
            foreach (array_filter($media) as $singleMedia) {
                $media = Media::where(DB::raw('BINARY name'), $singleMedia)->first();
                if(!$media){
                    $media = new Media;

                    $media->name = $singleMedia;

                    $media->save();
                }
                array_push($mToSync, $media->id);
            }
            $project->media()->sync(Media::whereIn('id', $mToSync)->get());
        }
    }

    public function update(Project $project)
    {
        $this->authorize(self::UPDATESTRING, $project);
        $media = request()->media;
        $attributes = request()->validate([
            'name' => self::REQUIRED,
            'description' => self::REQUIRED,
            'duration' => self::NULLABLE,
            'is_locked' => 'nullable ',
            self::INPUTS => self::NULLABLE
        ]);
        $project->update($attributes);
        $project->save();
        $this->syncMedia($media, $project, $mToSync);
        return response("Updated project successfully");
    }

    public function destroy(Project $project, Request $request)
    {

        if ($project->created_by == auth()->user()->id) {
            $project->delete();
            auth()->user()->addAction('delete project', $request->url(), 'user deleted project ' . $project->name);
        } else {
            return response()->json([self::MESSAGE => 'You can\'t delete this project'], 401);
        }
        return response()->json([self::MESSAGE => 'Project Deleted.'], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function inviteUser(Request $request)
    {

        $project = Project::where('id', $request->input(self::PROJECT))->first();
        $user = User::where('email', '=', $request->email)->first();
        if (!$user) {
            $user = new User();
            $user->email = $request->email;
            $user->password = Helper::random_str(60);
            $user->password_token = Helper::random_str(60);
            $user->save();
            Mail::to($user->email)->send(new VerificationEmail($user, $request->emailtext ? $request->emailtext : config('utilities.emailDefaultText')));
            $role = Role::where('name', '=', 'researcher')->first();
            $user->roles()->sync($role);
        }else if(!$user->hasVerifiedEmail())
        {
            $user->api_token = Helper::random_str(60);
            $user->password_token = Helper::random_str(60);
            $user->save();
            Mail::to($user->email)->send(new VerificationEmail($user, $request->emailtext ? $request->emailtext : config('utilities.emailDefaultText')));
            $role = Role::where('name', '=', 'researcher')->first();
            $user->roles()->sync($role);
        }
        $project->invited()->syncWithoutDetaching($user->id);
        return response()->json(['user' => $user, self::MESSAGE => 'user was invited!'], 200);
    }

    public function removeFromProject(Request $request)
    {

        $userWantsToBeRemovedFromStudy = $request->email != auth()->user()->email;
        if($userWantsToBeRemovedFromStudy){
            $this->authorize(self::UPDATESTRING, Project::where('id', $request->input(self::PROJECT))->first());
        }
        $user = User::where('email', '=', $request->email)->first();
        if ($user) {
            $user->invites()->detach($request->input(self::PROJECT));
            return response()->json([self::MESSAGE => 'user was removed from the project!'], 200);
        } else {
            return response()->json([self::MESSAGE => "The user doesn't exist!"], 403);
        }
    }

    public function export(Project $project)
    {
        if (auth()->user()->notOwnerNorInvited($project)) {
            abort(403, 'you can\'t see the data of this project.');
        }
        $headings = Project::getProjectInputHeadings($project);
        return (new AllCasesExport($project->id, $headings))->download('cases from ' . $project->name . ' project.xlsx');
    }

    /**
     * @param $data
     * @return mixed
     */
    private function checkNewsletter($data)
    {
        if (auth()->user()->profile()->exists())
        {
            $data['newsletter'] = auth()->user()->profile->newsletter === config('enums.newsletter_status.NOT DECIDED');
        } else
        {
            $profile = auth()->user()->addProfile(auth()->user());
            $data['newsletter'] = auth()->user()->profile->newsletter === config('enums.newsletter_status.NOT DECIDED');
        }
        return $data;
    }
}
