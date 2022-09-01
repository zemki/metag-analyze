<?php

namespace App\Http\Controllers;

use App\AllCasesExport;
use App\Files;
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
use Arr;

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
        // return all the users projects
        $data['projects'] = auth()->user()->projects()->get();
        foreach ($data['projects'] as $key => $value) {
            $data['projects'][$key]['authiscreator'] = auth()->user()->is($data['projects'][$key]->creator());
            foreach ($data['projects'][$key]->cases as $cases) {
                $data['projects'][$key]['entries'] += $cases->entries->count();
            }

            $data['projects'][$key]['casescount'] = $data['projects'][$key]->cases()->count();
            
 
            
            $data['projects'][$key]['editable'] =  $data['projects'][$key]->isEditable();
        }

        $data['invites'] = auth()->user()->invites()->get();
        
        foreach ($data['invites'] as $key => $value) {
            $data['invites'][$key]['casescount'] = $data['invites'][$key]->cases()->count();
            $data['invites'][$key]['authiscreator'] = false;
            $data['invites'][$key]['editable'] =  false;
            $data['invites'][$key]['owner'] =   $data['invites'][$key]->creator()->email;
            foreach ($data['projects'][$key]->cases() as $cases) {
                $data['projects'][$key]['entries'] += $cases->entries()->count();
            }
        }
        $data['invited_projects'] =  auth()->user()->invites;
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
        if (auth()->user()->notOwnerNorInvited($project) && !auth()->user()->isAdmin()) {
            abort(403);
        }
        $data['breadcrumb'] = [ url($project->path()) => strlen($project->name) > 20 ? substr($project->name, 0, 20) . '...' : $project->name];
        //  $project->media = $project->media()->pluck('media.name')->toArray();
        //$data['data']['media'] = Media::all();
        $data[self::PROJECT] = $project;
        $data[self::CASES] = $project->cases;
        $data['casesWithUsers'] = $project->cases()->with('user')->get();
        $data['casesWithEntries'] = $project->cases()->with('user', 'project', 'entries')->orderBy('created_at', 'desc')->get();
        // $data['notifications'] = [];
        // $data['plannedNotifications'] = [];
        // foreach ($data['casesWithUsers'] as $cases) {
        //     if (!$cases->isBackend()) {
        //         $cases->notifications = $cases->notifications();
        //         $cases->planned_notifications = $cases->plannedNotifications();
        //         array_push($data['notifications'], $cases->notifications());
        //         array_push($data['plannedNotifications'], $cases->plannedNotifications());
        //         $cases->user->profile = $cases->user->profile;
        //     }
        // }
        // $data['notifications'] = json_encode(Arr::flatten($data['notifications']));
        // $data['plannedNotifications'] = json_encode(Arr::flatten($data['plannedNotifications']));

        foreach ($data['casesWithEntries'] as $case) {
            $case->entries->map(function ($entry) {
                // if $entry->media()->first()->name is empty, assign an empty string to $entry->media
                $entry->media = $entry->media()->first() ? $entry->media()->first()->name : '';
            });
            // parse the lastday: string in the duration field, delimited by '|'
            
            $case->consultable = $case->isConsultable() && !$case->notYetStarted();

            // check the inputs in the entries of the case, if it contains the file property, resolve it to the file address
            $case->entries->map(function ($entry) use ($case) {
                if ($entry->inputs) {
                    $entry->inputs = json_decode($entry->inputs, true);
                    foreach ($entry->inputs as $key => $value) {
                        if ($key == 'file') {
                            $entry->file_object =  Files::find($value);

                            $entry->file_object['audiofile'] = decrypt(file_get_contents($entry->file_object['path']));
                            $entry->file_object['entry'] = $case->entries()->whereJsonContains('inputs->file', $entry->file_object['id'])->first();
                            if (!empty($entry->file_object['entry'])) {
                                $entry->file_object['entry']->media_id = Media::where('id', $entry->file_object['entry']->media_id)->first()->name;
                            }
                            $entry->file_path =  Files::find($value)->path;
                        }
                    }
                }
            });
        }




        
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
        $data['breadcrumb'] = [ '#' => 'Create a Project'];
        return view('projects.create', $data);
    }

    public function store()
    {
        $media = request()->media;
        $attributes = request()->validate([
            'name' => self::REQUIRED,
            'description' => self::REQUIRED,
            'created_by' => self::REQUIRED,
            self::INPUTS => self::NULLABLE
        ]);
        $inputs = json_decode($attributes[self::INPUTS]);
        foreach ($inputs as $input) {
            $input->answers = array_filter($input->answers);
        }
        $attributes[self::INPUTS] = json_encode($inputs);
        $project = auth()->user()->projects()->create($attributes);
        $this->syncMedia($media, $project, $mToSync);

        // return all the users projects
        $data['projects'] = auth()->user()->projects()->get();
        foreach ($data['projects'] as $key => $value) {
            $data['projects'][$key]['authiscreator'] = auth()->user()->is($data['projects'][$key]->creator());
            foreach ($data['projects'][$key]->cases as $cases) {
                $data['projects'][$key]['entries'] += $cases->entries->count();
            }

            $data['projects'][$key]['casescount'] = $data['projects'][$key]->cases()->count();
            
 
            
            $data['projects'][$key]['editable'] =  $data['projects'][$key]->isEditable();
        }

        $data['invites'] = auth()->user()->invites()->get();
        
        foreach ($data['invites'] as $key => $value) {
            $data['invites'][$key]['casescount'] = $data['invites'][$key]->cases()->count();
            $data['invites'][$key]['authiscreator'] = false;
            $data['invites'][$key]['editable'] =  false;
            $data['invites'][$key]['owner'] =   $data['invites'][$key]->creator()->email;
            foreach ($data['projects'][$key]->cases() as $cases) {
                $data['projects'][$key]['entries'] += $cases->entries()->count();
            }
        }
        $data['invited_projects'] =  auth()->user()->invites;

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
                if (!$media) {
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


    public function duplicate(Project $project)
    {
        $copy = $project->replicate();
        $copy->save();
        $copy->media()->sync($project->media()->get());
        
        return response('Project duplicated', 200);
    }

    public function destroy(Project $project, Request $request)
    {
        if ($project->created_by == auth()->user()->id) {
            $project->delete();
            auth()->user()->addAction('delete project', $request->url(), 'user deleted project ' . $project->name);
        } else {
            return response()->json([self::MESSAGE => 'You can\'t delete this project'], 401);
        }
        $projects = auth()->user()->projects()->get();
        foreach ($projects as $key => $value) {
            $projects[$key]['authiscreator'] = auth()->user()->is($projects[$key]->creator());
            foreach ($projects[$key]->cases as $cases) {
                $projects[$key]['entries'] += $cases->entries->count();
            }

            $projects[$key]['casescount'] = $projects[$key]->cases()->count();
            
 
            
            $projects[$key]['editable'] =  $projects[$key]->isEditable();
        }

        $data['invites'] = auth()->user()->invites()->get();
        
        foreach ($data['invites'] as $key => $value) {
            $data['invites'][$key]['casescount'] = $data['invites'][$key]->cases()->count();
            $data['invites'][$key]['authiscreator'] = false;
            $data['invites'][$key]['editable'] =  false;
            $data['invites'][$key]['owner'] =   $data['invites'][$key]->creator()->email;
            foreach ($projects[$key]->cases() as $cases) {
                $projects[$key]['entries'] += $cases->entries()->count();
            }
        }
        $data['invited_projects'] =  auth()->user()->invites;

        return response()->json([self::MESSAGE => 'Project Deleted.','projects' => $projects], 200);
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
        } elseif (!$user->hasVerifiedEmail()) {
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
        if ($userWantsToBeRemovedFromStudy) {
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
        if (auth()->user()->profile()->exists()) {
            $data['newsletter'] = auth()->user()->profile->newsletter === config('enums.newsletter_status.NOT DECIDED');
        } else {
            $profile = auth()->user()->addProfile(auth()->user());
            $data['newsletter'] = auth()->user()->profile->newsletter === config('enums.newsletter_status.NOT DECIDED');
        }
        return $data;
    }
}
