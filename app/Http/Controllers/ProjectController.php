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
use Log;

class ProjectController extends Controller
{
    protected const string UPDATESTRING = 'update';

    protected const string PROJECT = 'project';

    protected const string REQUIRED = 'required';

    protected const string NULLABLE = 'nullable';

    protected const string INPUTS = 'inputs';

    protected const string MESSAGE = 'message';

    protected const string CASES = 'cases';

    public function index()
    {
        $data['projects'] = auth()->user()->projects()->get();
        $data['invites'] = auth()->user()->invites()->get();

        $data['projects'] = $this->prepareProjects($data['projects']);
        $data['invites'] = $this->prepareInvites($data['invites']);

        $data['invited_projects'] = auth()->user()->invites;
        $data['projects'] = $data['projects']->merge($data['invites']);

        $data = $this->checkNewsletter($data);

        return view('projects.index', $data);
    }

    /**
     * @return Factory|View
     *
     * @throws AuthorizationException
     */
    public function show(Project $project)
    {
        if (auth()->user()->notOwnerNorInvited($project) && ! auth()->user()->isAdmin()) {
            abort(403);
        }
        $data['breadcrumb'] = [url($project->path()) => strlen($project->name) > 20 ? substr($project->name, 0, 20) . '...' : $project->name];
        $data[self::PROJECT] = $project;
        $data[self::CASES] = $project->cases;
        $data['casesWithUsers'] = $project->cases()->with('user')->get();
        $data['casesWithEntries'] = $project->cases()->with('user', 'project', 'entries')->orderBy('created_at', 'desc')->get()->map(function ($case) {
            $case->first_day = $case->firstDay();
            $case->start_day = $case->startDay();
            $case->duration_string = Helper::get_string_between($case->duration, 'duration:', '|');
            $case->last_day = $case->lastDay() ?: 'Case not started by the user';
            $case->is_consultable = $case->isConsultable();

            return $case;
        });

        foreach ($data['casesWithEntries'] as $case) {
            $case->entries->map(function ($entry) {
                $entry->media = $entry->media()->first() ? $entry->media()->first()->name : '';
            });
            // parse the lastday: string in the duration field, delimited by '|'

            $case->consultable = $case->isConsultable() && ! $case->notYetStarted();
            $case->backend = $case->isBackend();

            // check the inputs in the entries of the case, if it contains the file property, resolve it to the file address
            $case->entries->map(function ($entry) use ($case) {
                if ($entry->inputs) {
                    $entry->inputs = json_decode($entry->inputs, true);
                    foreach ($entry->inputs as $key => $value) {
                        if ($key == 'file') {
                            $entry->file_object = Files::find($value);

                            if ($entry->file_object) {
                                try {
                                    $entry->file_object['audiofile'] = decrypt(file_get_contents($entry->file_object['path']));
                                } catch (\Exception $e) {
                                    // Log the exception or handle it as you need
                                    Log::error("Could not decrypt the file: {$e->getMessage()}");
                                }

                                $entry->file_object['entry'] = $case->entries()->whereJsonContains('inputs->file', $entry->file_object['id'])->first();

                                if (! empty($entry->file_object['entry'])) {
                                    $entry->file_object['entry']->media_id = optional(Media::where('id', '=', $entry->file_object['entry']->media_id)->first())->name;
                                }

                                $entry->file_path = $entry->file_object->path;
                            } else {
                                // Handle the condition where file_object is not found
                                Log::warning("File object not found for entry id: {$entry->id}");
                            }
                        }
                    }
                }

                if (array_key_exists('firstValue', $entry->inputs)) {
                    $temp = $entry->inputs['firstValue'];
                    $entry->mediaforFirstValue = Media::where('id', '=', $temp['media_id'])->first() ? Media::where('id', '=', $temp['media_id'])->first()->name : '';
                }
            });
        }

        $data[self::PROJECT . 'media'] = $project->media()->pluck('media.name')->toArray();
        $data['invites'] = $project->invited()->get();
        $data['inputs'] = config('inputs');

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
            abort(403, 'You reached the max number of projects');
        }
        $data['breadcrumb'] = ['#' => 'Create a Project'];

        return view('projects.create', $data);
    }

    public function store()
    {
        $media = request()->media;
        $attributes = request()->validate([
            'name' => self::REQUIRED,
            'description' => self::REQUIRED,
            'created_by' => self::REQUIRED,
            self::INPUTS => self::NULLABLE,
            'entityName' => self::NULLABLE,
            'useEntity' => 'boolean|nullable',
        ]);
        // Check if inputs is already a JSON string or an array
        if (is_string($attributes[self::INPUTS]) && $this->isJson($attributes[self::INPUTS])) {
            $inputs = json_decode($attributes[self::INPUTS], true);
        } else {
            $inputs = $attributes[self::INPUTS];
        }
        
        // Process each input to ensure answers are properly handled
        foreach ($inputs as &$input) {
            // Check if the input has 'answers' key before attempting to filter
            if (isset($input['answers'])) {
                $input['answers'] = array_filter($input['answers']);
            } else {
                // Initialize an empty array for non-choice type inputs
                $input['answers'] = [];
            }
        }
        
        $attributes[self::INPUTS] = json_encode($inputs);

        // Only update entity_name and use_entity for non-legacy projects
        if (request()->has('entityName') || request()->has('useEntity')) {
            // For new projects, apply entity settings
            // Set default entity name if not provided
            $attributes['entity_name'] = $attributes['entityName'] ?? 'entity';
            unset($attributes['entityName']); // Remove the original key

            // Set use_entity flag
            $attributes['use_entity'] = $attributes['useEntity'] ?? true;
            unset($attributes['useEntity']); // Remove the original key
        }

        $project = auth()->user()->projects()->create($attributes);

        // Handle media and entity synchronization
        // Only process media if useEntity is true or if it's a legacy project (no entity_name field)
        if ($attributes['use_entity'] !== false && $media && is_array($media)) {
            // Filter out empty values
            $filteredMedia = array_filter($media, function ($value) {
                return ! empty(trim($value));
            });

            // Only sync if we have media items
            if (! empty($filteredMedia)) {
                $this->syncMedia($filteredMedia, $project, $mToSync);
            }
        } elseif ($attributes['use_entity'] === false) {
            // If use_entity is false, clear any existing media
            $project->media()->sync([]);
        }

        // return all the users projects
        $data['projects'] = auth()->user()->projects()->get();
        $data['invites'] = auth()->user()->invites()->get();

        $data['projects'] = $this->prepareProjects($data['projects']);
        $data['invites'] = $this->prepareInvites($data['invites']);

        $data['invited_projects'] = auth()->user()->invites;
        $data['projects'] = $data['projects']->merge($data['invites']);

        $data[self::MESSAGE] = 'project created!';
        $data = $this->checkNewsletter($data);

        return view('projects.index', $data);
    }

    protected function syncMedia($media, $project, &$mToSync): void
    {
        if ($media) {
            $mToSync = [];
            foreach (array_filter($media) as $singleMedia) {
                $mediaItem = Media::where(DB::raw('BINARY name'), $singleMedia)->first();
                if (! $mediaItem) {
                    $mediaItem = new Media;
                    $mediaItem->name = $singleMedia;
                    $mediaItem->save();
                }
                array_push($mToSync, $mediaItem->id);
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
            self::INPUTS => self::NULLABLE,
            'entityName' => self::NULLABLE,
            'useEntity' => 'boolean|nullable',
        ]);
        // Check if inputs is already a JSON string or an array
        if (is_string($attributes[self::INPUTS]) && $this->isJson($attributes[self::INPUTS])) {
            $decodedAttributes = json_decode($attributes[self::INPUTS], true);
        } else {
            $decodedAttributes = $attributes[self::INPUTS];
        }
        
        foreach ($decodedAttributes as $input => $value) {
            if (isset($value['answers'])) {
                $decodedAttributes[$input]['answers'] = array_filter($value['answers'], fn ($v) => ! is_null($v) && $v !== '');
            } else {
                // Initialize an empty array for non-choice type inputs
                $decodedAttributes[$input]['answers'] = [];
            }
        }

        $attributes[self::INPUTS] = json_encode($decodedAttributes);

        // Only update entity_name and use_entity for non-legacy projects
        if (request()->has('entityName') || request()->has('useEntity')) {
            $projectDate = new \DateTime($project->created_at);
            $cutoffDate = new \DateTime(config('app.api_v2_cutoff_date', '2025-03-21'));

            if ($projectDate >= $cutoffDate) {
                // Set default entity name if not provided
                if (request()->has('entityName')) {
                    $attributes['entity_name'] = $attributes['entityName'] ?? 'entity';
                    unset($attributes['entityName']); // Remove the original key
                }

                // Set use_entity flag
                if (request()->has('useEntity')) {
                    $attributes['use_entity'] = $attributes['useEntity'] ?? true;
                    unset($attributes['useEntity']); // Remove the original key
                }
            } else {
                // For legacy projects, just remove these keys to avoid DB column errors
                if (isset($attributes['entityName'])) {
                    unset($attributes['entityName']);
                }
                if (isset($attributes['useEntity'])) {
                    unset($attributes['useEntity']);
                }
            }
        }

        $project->update($attributes);
        $project->save();

        // Handle media and entity synchronization
        // Only process media if useEntity is true or it's a legacy project
        if ($attributes['use_entity'] !== false && $media && is_array($media)) {
            // Filter out empty values
            $filteredMedia = array_filter($media, function ($value) {
                return ! empty(trim($value));
            });

            // Only sync if we have media items
            if (! empty($filteredMedia)) {
                $this->syncMedia($filteredMedia, $project, $mToSync);
            }
        } elseif ($attributes['use_entity'] === false) {
            // If use_entity is false, clear any existing media
            $project->media()->sync([]);
        }

        return response('Updated project successfully');
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
        $invites = auth()->user()->invites()->get();

        $projects = $this->prepareProjects($projects);
        $invites = $this->prepareInvites($invites);

        $data['invited_projects'] = auth()->user()->invites;
        $projects = $projects->merge($invites);

        return response()->json([self::MESSAGE => 'Project Deleted.', 'projects' => $projects], 200);
    }

    /**
     * @return JsonResponse
     */
    public function inviteUser(Request $request)
    {
        $project = Project::where('id', $request->input(self::PROJECT))->first();
        $user = User::where('email', '=', $request->email)->first();
        if (! $user) {
            $user = new User;
            $user->email = $request->email;
            $user->password = Helper::random_str(60);
            $user->password_token = Helper::random_str(60);
            $user->save();
            Mail::to($user->email)->send(new VerificationEmail($user, $request->emailtext ? $request->emailtext : config('utilities.emailDefaultText')));
            $role = Role::where('name', '=', 'researcher')->first();
            $user->roles()->sync($role);
        } elseif (! $user->hasVerifiedEmail()) {
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

    /**
     * Prepare Projects Data
     */
    private function prepareProjects($projects)
    {
        foreach ($projects as $key => $project) {
            $projects[$key]['authiscreator'] = auth()->user()->is($project->creator());
            $projects[$key]['entries'] = $project->cases->sum('entries_count');
            $projects[$key]['casescount'] = $project->cases()->count();
            $projects[$key]['editable'] = $project->isEditable();
        }

        return $projects;
    }

    /**
     * Prepare Invites Data
     */
    private function prepareInvites($invites)
    {
        foreach ($invites as $key => $invite) {
            $invites[$key]['casescount'] = $invite->cases()->count();
            $invites[$key]['authiscreator'] = false;
            $invites[$key]['editable'] = false;
            $invites[$key]['owner'] = $invite->creator()->email;
            $invites[$key]['entries'] = $invite->cases->sum('entries_count');
        }

        return $invites;
    }
    
    /**
     * Check if a string is valid JSON
     *
     * @param string $string The string to check
     * @return bool True if the string is valid JSON, false otherwise
     */
    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
