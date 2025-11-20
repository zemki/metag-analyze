<?php

namespace App\Http\Controllers;

use App\AllCasesExport;
use App\Cases;
use App\Enums\CaseStatus;
use App\Mail\VerificationEmail;
use App\Mart\MartPage;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\Media;
use App\Project;
use App\Role;
use App\User;
use DateTime;
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

        // Add isEditable property to the project object
        $project->isEditable = $project->isEditable();
        $data[self::PROJECT] = $project;

        // Optimized query with pagination and eager loading
        $data['casesWithEntries'] = $project->cases()
            ->with([
                'user:id,email',
                'user.profile:user_id,name',
                'entries' => function ($query) {
                    $query->select('id', 'case_id', 'media_id', 'begin', 'end', 'inputs')
                        ->with('media:id,name');
                },
                'files:id,case_id,path',
            ])
            ->select('id', 'name', 'duration', 'user_id', 'created_at', 'project_id')
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->through(function ($case) use ($project) {
                // Cache computed values to avoid repeated calculations
                $case->first_day = $case->firstDay();
                $case->start_day = $case->startDay();
                $case->duration_string = Helper::get_string_between($case->duration, 'duration:', '|');
                $case->last_day = $case->lastDay() ?: 'Case not started by the user';
                $case->is_consultable = $case->isConsultable();
                $case->consultable = $case->isConsultable() && ! $case->notYetStarted();
                $case->backend = $case->isBackend();

                // Attach project data for frontend access
                $case->project = (object) [
                    'id' => $project->id,
                    'is_mart_project' => $project->isMartProject(),
                    'use_entity' => $project->use_entity ?? true,
                    'entity_name' => $project->entity_name ?? 'media',
                ];

                // Optimize entry processing
                $case->entries->each(function ($entry) use ($case) {
                    $entry->media_name = $entry->media ? $entry->media->name : '';

                    if ($entry->inputs) {
                        $inputs = is_string($entry->inputs) ? json_decode($entry->inputs, true) : $entry->inputs;
                        $entry->inputs = $inputs;

                        // Only process file entries if they exist
                        if (isset($inputs['file'])) {
                            $fileObject = $case->files->where('id', $inputs['file'])->first();
                            if ($fileObject) {
                                $entry->file_object = $fileObject;
                                $entry->file_path = $fileObject->path;
                                // Note: File decryption moved to on-demand loading for performance
                            }
                        }

                        if (isset($inputs['firstValue']['media_id'])) {
                            // This should also be optimized with eager loading if frequently used
                            $entry->mediaforFirstValue = Media::where('id', $inputs['firstValue']['media_id'])->value('name') ?: '';
                        }
                    }
                });

                return $case;
            });

        $data[self::CASES] = $project->cases()->select('id', 'name', 'duration', 'user_id', 'created_at')->get();
        $data['casesWithUsers'] = $project->cases()->with(['user:id,email', 'user.profile:user_id,name'])->select('id', 'name', 'user_id')->get();

        // Only include media if project uses entity field or is legacy project
        $useEntity = $project->use_entity ?? true;
        $data[self::PROJECT . 'media'] = $useEntity ? $project->media()->pluck('media.name')->toArray() : [];
        $data['invites'] = $project->invited()->get();
        $data['inputs'] = config('inputs');

        return view('projects.show', $data);
    }

    /**
     * Get paginated cases for a project
     */
    public function getCases(Project $project, Request $request)
    {
        if (auth()->user()->notOwnerNorInvited($project) && ! auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = $project->cases()
            ->with([
                'user:id,email',
                'user.profile:user_id,name',
                'entries' => function ($query) {
                    $query->select('id', 'case_id', 'media_id', 'begin', 'end', 'inputs')
                        ->with('media:id,name');
                },
                'files:id,case_id,path',
            ])
            ->select('id', 'name', 'duration', 'user_id', 'created_at', 'project_id', 'qr_token_uuid', 'qr_token_revoked_at');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('email', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Apply status filter (keep existing SQL for performance, but use enum values)
        if ($request->filled('status')) {
            $status = $request->input('status');
            switch ($status) {
                case CaseStatus::ACTIVE->value:
                    $query->whereRaw("STR_TO_DATE(SUBSTRING_INDEX(SUBSTRING_INDEX(duration, 'lastDay:', -1), '|', 1), '%d.%m.%Y') >= CURDATE()");
                    break;
                case CaseStatus::COMPLETED->value:
                    $query->whereRaw("STR_TO_DATE(SUBSTRING_INDEX(SUBSTRING_INDEX(duration, 'lastDay:', -1), '|', 1), '%d.%m.%Y') < CURDATE()");
                    break;
                case CaseStatus::BACKEND->value:
                    $query->whereRaw("SUBSTRING_INDEX(SUBSTRING_INDEX(duration, 'value:', -1), '|', 1) = '0'");
                    break;
            }
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 50);
        $cases = $query->paginate($perPage);

        // Process cases with computed properties
        $cases->getCollection()->transform(function ($case) use ($project) {
            $case->first_day = $case->firstDay();
            $case->start_day = $case->startDay();
            $case->duration_string = Helper::get_string_between($case->duration, 'duration:', '|');
            $case->last_day = $case->lastDay() ?: 'Case not started by the user';
            $case->status = $case->getStatus()->value;
            $case->is_consultable = $case->isConsultable();
            $case->consultable = $case->isAccessible(); // Use new accessibility logic
            $case->backend = $case->isBackend();

            // Attach project data for frontend access
            $case->project = (object) [
                'id' => $project->id,
                'is_mart_project' => $project->isMartProject(),
                'use_entity' => $project->use_entity ?? true,
                'entity_name' => $project->entity_name ?? 'media',
            ];

            // Optimize entry processing
            $case->entries->each(function ($entry) use ($case) {
                $entry->media_name = $entry->media ? $entry->media->name : '';

                if ($entry->inputs) {
                    $inputs = is_string($entry->inputs) ? json_decode($entry->inputs, true) : $entry->inputs;
                    $entry->inputs = $inputs;

                    // Only process file entries if they exist
                    if (isset($inputs['file'])) {
                        $fileObject = $case->files->where('id', $inputs['file'])->first();
                        if ($fileObject) {
                            $entry->file_object = $fileObject;
                            $entry->file_path = $fileObject->path;
                        }
                    }

                    if (isset($inputs['firstValue']['media_id'])) {
                        $entry->mediaforFirstValue = Media::where('id', $inputs['firstValue']['media_id'])->value('name') ?: '';
                    }
                }
            });

            return $case;
        });

        return response()->json($cases);
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

        // Get setting and ensure it's boolean
        $martEnabledValue = \App\Setting::get('mart_enabled', true);
        $data['martEnabled'] = filter_var($martEnabledValue, FILTER_VALIDATE_BOOLEAN);

        return view('projects.create', $data);
    }

    public function store()
    {
        $media = request()->media;
        $attributes = request()->validate([
            'name' => self::REQUIRED,
            'description' => self::REQUIRED,
            self::INPUTS => self::NULLABLE,
            'entityName' => self::NULLABLE,
            'useEntity' => 'boolean|nullable',
            'isMart' => 'boolean|nullable',
            'startDate' => self::NULLABLE,
            'startTime' => self::NULLABLE,
            'endDate' => self::NULLABLE,
            'endTime' => self::NULLABLE,
        ]);

        // Set created_by from authenticated user
        $attributes['created_by'] = auth()->id();

        // Process inputs if provided (MART projects don't have inputs)
        if (isset($attributes[self::INPUTS])) {
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
        } else {
            // MART projects or projects without inputs - set empty JSON array
            $attributes[self::INPUTS] = json_encode([]);
        }

        // If this is a MART project, add the MART marker to inputs
        if (isset($attributes['isMart']) && $attributes['isMart'] === true) {
            $inputs = json_decode($attributes[self::INPUTS], true);
            $inputs[] = [
                'type' => 'mart',
                'name' => 'MART Configuration',
                'startDate' => $attributes['startDate'] ?? null,
                'startTime' => $attributes['startTime'] ?? null,
                'endDate' => $attributes['endDate'] ?? null,
                'endTime' => $attributes['endTime'] ?? null,
                'answers' => [],
            ];
            $attributes[self::INPUTS] = json_encode($inputs);
        }

        // Remove MART-specific fields that shouldn't be in the projects table
        unset($attributes['isMart'], $attributes['startDate'], $attributes['startTime'], $attributes['endDate'], $attributes['endTime']);

        // Only update entity_name and use_entity for non-legacy projects
        if (request()->has('entityName') || request()->has('useEntity')) {
            // For new projects, apply entity settings
            // Set default entity name if not provided
            $attributes['entity_name'] = $attributes['entityName'] ?? 'entity';
            unset($attributes['entityName']); // Remove the original key

            // Set use_entity flag
            $attributes['use_entity'] = $attributes['useEntity'];
            unset($attributes['useEntity']); // Remove the original key
        }

        $project = auth()->user()->projects()->create($attributes);

        // If this is a MART project, create the corresponding MART database record
        if ($project->isMartProject()) {
            DB::connection('mart')->beginTransaction();
            try {
                MartProject::create([
                    'main_project_id' => $project->id,
                ]);
                DB::connection('mart')->commit();
            } catch (\Exception $e) {
                DB::connection('mart')->rollBack();
                // Also delete the main project to maintain consistency
                $project->delete();
                throw new \Exception('Failed to create MART project: ' . $e->getMessage());
            }
        }

        // Handle media and entity synchronization
        // Only process media if useEntity is true or if it's a legacy project (no entity_name field)
        $useEntity = $attributes['use_entity'] ?? true; // Default to true for legacy projects
        if ($useEntity !== false && $media && is_array($media)) {
            // Filter out empty values
            $filteredMedia = array_filter($media, function ($value) {
                return ! empty(trim($value));
            });

            // Only sync if we have media items
            if (! empty($filteredMedia)) {
                $this->syncMedia($filteredMedia, $project, $mToSync);
            }
        } elseif ($useEntity === false) {
            // If use_entity is false, clear any existing media
            $project->media()->sync([]);
        }

        // Check if this is an AJAX request (from Vue frontend)
        if (request()->expectsJson() || request()->ajax()) {
            // Return JSON response for AJAX requests (e.g., MART project creation)
            return response()->json([
                'success' => true,
                'id' => $project->id,
                'project' => $project,
                'message' => 'Project created successfully'
            ], 201);
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
            $projectDate = new DateTime($project->created_at);
            $cutoffDate = new DateTime(config('app.api_v2_cutoff_date', '2025-03-21'));

            if ($projectDate >= $cutoffDate) {
                // Set default entity name if not provided
                if (request()->has('entityName')) {
                    $attributes['entity_name'] = $attributes['entityName'] ?? 'entity';
                    unset($attributes['entityName']); // Remove the original key
                }

                // Set use_entity flag
                if (request()->has('useEntity')) {
                    $attributes['use_entity'] = $attributes['useEntity'];
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

        // If this is a MART project and doesn't have a MartProject record yet, create it
        if ($project->isMartProject() && !$project->martProject()) {
            DB::connection('mart')->beginTransaction();
            try {
                \App\Mart\MartProject::create([
                    'main_project_id' => $project->id,
                ]);
                DB::connection('mart')->commit();
            } catch (\Exception $e) {
                DB::connection('mart')->rollBack();
                \Log::error('Failed to create MART project during update: ' . $e->getMessage());
                // Don't fail the whole update, just log the error
            }
        }

        // Handle media and entity synchronization
        // Only process media if useEntity is true or it's a legacy project
        $useEntity = $attributes['use_entity'] ?? true; // Default to true for legacy projects
        if ($useEntity !== false && $media && is_array($media)) {
            // Filter out empty values
            $filteredMedia = array_filter($media, function ($value) {
                return ! empty(trim($value));
            });

            // Only sync if we have media items
            if (! empty($filteredMedia)) {
                $this->syncMedia($filteredMedia, $project, $mToSync);
            }
        } elseif ($useEntity === false) {
            // If use_entity is false, clear any existing media
            $project->media()->sync([]);
        }

        return response('Updated project successfully');
    }

    public function duplicate(Project $project)
    {
        // Check if original project has MART data
        $originalMartProject = $project->martProject();
        $isMartProject = $originalMartProject !== null;

        // Start main DB transaction
        DB::connection('mysql')->beginTransaction();

        // Only start MART transaction if this is a MART project
        if ($isMartProject) {
            DB::connection('mart')->beginTransaction();
        }

        try {
            $copy = $project->replicate();
            $copy->save();
            $copy->refresh();
            $copy->media()->sync($project->media()->get());

            if ($isMartProject) {
                // Create new MART project for the copy
                $newMartProject = MartProject::create([
                    'main_project_id' => $copy->id,
                ]);

                // Copy all schedules
                $originalSchedules = MartSchedule::forProject($originalMartProject->id)
                    ->with('questions')
                    ->get();

                foreach ($originalSchedules as $originalSchedule) {
                    // Replicate schedule
                    $newSchedule = $originalSchedule->replicate();
                    $newSchedule->mart_project_id = $newMartProject->id;
                    $newSchedule->save();

                    // Copy all questions with NEW UUIDs
                    foreach ($originalSchedule->questions as $originalQuestion) {
                        MartQuestion::create([
                            'schedule_id' => $newSchedule->id,
                            'position' => $originalQuestion->position,
                            'text' => $originalQuestion->text,
                            'type' => $originalQuestion->type,
                            'config' => $originalQuestion->config,
                            'is_mandatory' => $originalQuestion->is_mandatory,
                            'version' => 1, // Reset version for new copy
                            // uuid will be auto-generated by MartQuestion model
                        ]);
                    }
                }

                // Copy all pages
                $originalPages = MartPage::forProject($originalMartProject->id)->get();
                foreach ($originalPages as $originalPage) {
                    $newPage = $originalPage->replicate();
                    $newPage->mart_project_id = $newMartProject->id;
                    $newPage->save();
                }
            }

            // Commit transactions
            DB::connection('mysql')->commit();
            if ($isMartProject) {
                DB::connection('mart')->commit();
            }

            return response('Project duplicated', 200);
        } catch (\Exception $e) {
            // Rollback transactions on error
            DB::connection('mysql')->rollBack();
            if ($isMartProject) {
                DB::connection('mart')->rollBack();
            }

            return response()->json([
                'message' => 'Failed to duplicate project: '.$e->getMessage(),
            ], 500);
        }
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

            // Add entity information for frontend compatibility
            $projects[$key]['entity_name'] = $project->entity_name ?? 'media';
            $projects[$key]['use_entity'] = $project->use_entity ?? true;

            // Add MART project detection for frontend
            $projects[$key]['is_mart_project'] = $project->isMartProject();
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
     * @param  string  $string  The string to check
     * @return bool True if the string is valid JSON, false otherwise
     */
    private function isJson($string)
    {
        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Display the treemap visualization for a project
     *
     * @return Factory|View
     *
     * @throws AuthorizationException
     */
    public function treemap(Project $project)
    {
        // Check authorization
        if (auth()->user()->notOwnerNorInvited($project) && ! auth()->user()->isAdmin()) {
            abort(403);
        }

        // Prepare breadcrumb
        $data['breadcrumb'] = [
            url('/projects') => 'Projects',
            url($project->path()) => strlen($project->name) > 20 ? substr($project->name, 0, 20) . '...' : $project->name,
            url($project->path() . '/treemap') => 'Treemap Visualization',
        ];

        // Get project data
        $data['project'] = $project;

        // Get all cases with their entries
        $data['cases'] = $project->cases()->with(['entries', 'user'])->get();

        // Get all entries for this project
        $data['entries'] = $project->cases()
            ->join('entries', 'cases.id', '=', 'entries.case_id')
            ->select('entries.*')
            ->get();

        // Get all media/entities used in this project
        $data['media'] = $project->media()->get();

        // Prepare data for JavaScript
        $data['projectJson'] = json_encode($project);
        $data['casesJson'] = json_encode($data['cases']);
        $data['entriesJson'] = json_encode($data['entries']);
        $data['mediaJson'] = json_encode($data['media']);

        return view('projects.treemap', $data);
    }
}
