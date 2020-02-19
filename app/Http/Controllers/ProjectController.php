<?php

namespace App\Http\Controllers;

use App\Mail\VerificationEmail;
use App\Media;
use App\Project;
use App\Role;
use App\User;
use Helper;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use App\AllCasesExport;

class ProjectController extends Controller
{


    public function index()
    {

        $data['projects'] = auth()->user()->projects()->get();
        $data['invites'] = auth()->user()->invites()->get();

        return view('projects.index', $data);
    }


    /**
     * @param Project $project
     * @return Factory|View
     * @throws AuthorizationException
     */
    public function show(Project $project)
    {

        $this->authorize('update', $project);
        $data['breadcrumb'] = [url('/') => 'Projects', '#' => substr($project->name, 0, 20) . '...'];

        $project->media = $project->media()->pluck('media.name')->toArray();

        $data['data']['media'] = Media::all();

        $data['project'] = $project;
        $data['invites'] = $project->invited()->get();

        return view('projects.show', $data);
    }

    /**
     * Show Create form
     *
     * @return View return view with the form to insert a new project
     */
    public function create(Request $request)
    {
        if (auth()->user()->hasReachMaxNumberOfProjecs()) {
            //auth()->user()->addAction('trying to create a study', $request->url(), 'Max numbers of studies reached for user ' . auth()->user()->email);
            abort(403, 'You reached the max number of projects');
        }
        $data['breadcrumb'] = [url('/') => 'Projects', '#' => 'Create'];

        return view('projects.create', $data);
    }


    public function store()
    {
        $media = request()->media;
        $attributes = request()->validate([
            'name' => 'required',
            'description' => 'required',
            'created_by' => 'required',
            'is_locked' => 'nullable ',
            'inputs' => 'nullable'
        ]);

        $inputs = json_decode($attributes['inputs']);
        foreach ($inputs as $input) {
            $input->answers = array_filter($input->answers);
        }
        $attributes['inputs'] = json_encode($inputs);

        $project = auth()->user()->projects()->create($attributes);

        $this->syncMedia($media, $project, $mToSync);

        return redirect('projects');

    }


    public function update(Project $project)
    {
        $this->authorize('update', $project);
        $media = request()->media;
        $attributes = request()->validate([
            'name' => 'required',
            'description' => 'required',
            'duration' => 'nullable',
            'is_locked' => 'nullable ',
            'inputs' => 'nullable'
        ]);
        $project->update($attributes);
        $project->save();
        $this->syncMedia($media, $project, $mToSync);
        return response("Updated project successfully");
    }


    public function destroy(Project $project)
    {

        if ($project->isEditable() && $project->created_by == auth()->user()->id) {
            $project->delete();
        } else {
            return response()->json(['message' => 'You can\'t delete this project'], 401);
        }
        return response()->json(['message' => 'Project Deleted.'], 200);


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
                array_push($mToSync, Media::firstOrCreate(['name' => $singleMedia])->id);

            }
            $project->media()->sync(Media::whereIn('id', $mToSync)->get());
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function inviteUser(Request $request)
    {

        $project = Project::where('id', $request->input('project'))->first();

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

        }

        $project->invited()->syncWithoutDetaching($user->id);

        return response()->json(['user' => $user, 'message' => 'user was invited!'], 200);

    }

    public function removeFromProject(Request $request)
    {

        $this->authorize('update', Project::where('id', $request->input('project'))->first());

        $user = User::where('email', '=', $request->email)->first();

        if ($user) {
            $user->invites()->detach($request->input('project'));
            return response()->json(['message' => 'user was removed from the project!'], 200);
        } else {
            return response()->json(['message' => "The user doesn't exist!"], 403);

        }

    }


    public function export(Project $project)
    {
        if (auth()->user()->notOwnerNorInvited($project)) {
            abort(403, 'you can\'t see the data of this project.');
        }

        $headings = Project::getProjectInputHeadings($project);

        return (new AllCasesExport($project->id, $headings))->download('cases from '. $project->name .' project.xlsx');
    }



}
