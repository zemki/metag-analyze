<?php

namespace App\Http\Controllers;

use App\Cases;
use App\CasesExport;
use App\Entry;
use App\Media;
use App\Project;
use App\User;
use Carbon\Carbon;
use Crypt;
use Exception;
use Helper;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Notifications\notifyUserforNewCaseWhenAlreadyRegistered;

class ProjectCasesController extends Controller
{
    protected const PROJECT = 'project';
    protected const MESSAGE = 'message';
    
    /**
     * @param Project $project
     * @param Cases   $case
     * @return Factory|\Illuminate\View\View
     */
    public function distinctshow(Project $project, Cases $case)
    {
        if (auth()->user()->notOwnerNorInvited($project) && !auth()->user()->isAdmin()) {
            abort(403);
        }
        list($mediaValues, $availableMedia) = Cases::getMediaValues($case);
        list($availableInputs, $data) = Cases::getInputValues($case, $data);
        $data['entries']['list'] = Entry::where('case_id', '=', $case->id)->get();
        foreach ($data['entries']['list'] as $entry) {
            $entry['inputs'] = json_decode($entry['inputs']);
            $entry['media_id'] = Media::firstWhere('id', $entry['media_id'])->name;
        }
        $data['entries']['media'] = $mediaValues;
        $data['entries']['availablemedia'] = $availableMedia;
        //$data['entries']['inputs'] = $inputValues;
        $data['entries']['availableinputs'] = $availableInputs;
        $data['case'] = $case;
        $data['project'] = $project;
        $data = $this->getGraphBreadcrumb($project, $case, $data);
        return view('entries.distinctcases', $data);
    }

    public function groupedshow(Project $project, Cases $case)
    {
        if (auth()->user()->notOwnerNorInvited($project) && !auth()->user()->isAdmin()) {
            abort(403);
        }
        $tempArray = Entry::where('case_id', '=', $case->id)->with('media')->get()->toArray();
        $data['entries'] = [];
        $data['availableInputs'] = $project->getAnswersInputs();
        //  $data['availableInputs'] = [];
        $textInputToUnset = [];
        foreach ($tempArray as $entry) {
            $tempInputsArray = json_decode($entry['inputs'], true);
            $inputEntry = [];
    

            $inputEntry['inputs'] = [];
            // format the inputs for the graph
            foreach ($tempInputsArray as $question => $answer) {
                if (is_array($answer)) {
                    foreach ($answer as $singularAnswer) {
                        $tempEntryInputs['id'] = $entry['id'];
                        $tempEntryInputs['name'] = $singularAnswer;
                        foreach ($data['availableInputs'] as $availableInput) {
                            if ($availableInput->name == $singularAnswer) {
                                $tempEntryInputs['color'] = $availableInput->color;
                                break;
                            }
                        }
                        array_push($inputEntry['inputs'], (object)$tempEntryInputs);
                    }
                } elseif (strlen($answer) > 1) {
                    #text & file
                    $tempEntryInputs['id'] = $entry['id'];
                    $tempEntryInputs['name'] = $question !== "file" ? $answer : "file";
                    foreach ($data['availableInputs'] as $key => $availableInput) {
                        if ($availableInput->name == $question) {
                            array_push($data['availableInputs'], (object)["id" => count($data['availableInputs']) + 1, "name" => $answer, "color" => $availableInput->color]);
                            $tempEntryInputs['color'] = $availableInput->color;
                            array_push($textInputToUnset, $key);
                            break;
                        }
                    }
                    array_push($inputEntry['inputs'], (object)$tempEntryInputs);
                } else {
                    # scale
                    $tempEntryInputs['id'] = $entry['id'];
                    $tempEntryInputs['name'] = $answer;
                    foreach ($data['availableInputs'] as $availableInput) {
                        if ($availableInput->name == $answer) {
                            $tempEntryInputs['color'] = $availableInput->color;
                            break;
                        }
                    }
                    array_push($inputEntry['inputs'], (object)$tempEntryInputs);
                }
            }
            // translate the object to array
            // check here input type file and remark when there's a file - now it shows only the id of the file

            foreach ($entry as $key => $property) {
                if ($key == "inputs") {
                    continue;
                }
                $inputEntry[$key] = $property;
            }
            $inputEntry['begin'] = strtotime($inputEntry['begin']);
            $inputEntry['end'] = strtotime($inputEntry['end']);
            $inputEntry['color'] = array_rand(array_flip(config('colors.chartCategories')), 1);
            array_push($data['entries'], $inputEntry);
        }
        $this->filterUnusedInputs($data, $textInputToUnset, $inputsToFilter);
        $this->assignColorsToMedia($case, $data);
        sort($data['availableInputs']);
        $data = $this->getGraphBreadcrumb($project, $case, $data);
        return view('entries.groupedcases', $data);
    }

    /**
     * Create a case belonging to a project
     * @param Project $project
     * @return Factory|\Illuminate\View\View
     */
    public function create(Project $project)
    {
        if (auth()->user()->notOwnerNorInvited($project)) {
            abort(403);
        }
        $data['breadcrumb'] = [
            url($project->path()) => strlen($project->name) > 20 ? substr($project->name, 0, 20) . '...' : $project->name,
            '#' => 'Create Case'
        ];
        $data['project'] = $project;
        return view('cases.create', $data);
    }

    /**
     * @param Project $project
     * @return RedirectResponse|Redirector
     * @throws AuthorizationException
     */
    public function store(Project $project)
    {
        if (auth()->user()->notOwnerNorInvited($project)) {
            abort(403);
        }
        

        if (request('name') == "") {
            return redirect($project->path() . '/cases/new')->with(['message' => __('Please fill all the required inputs.'), 'message_type' => 'error'])->withInput();
        }
        request()->validate(
            ['name' => 'required'],
            ['email' => 'required'],
            ['duration' => 'required']
        );
        $message="";
        if (request('backendCase')) {
            $user = auth()->user();
            $case = $project->addCase(request('name'), 'value:0|days:0|lastDay:' . Carbon::now()->subDay());
            $message = __("backend case created.");
        } else {
            $emails = Helper::multiexplode(array(";", ","," "), request('email'));


            $invalidEmails = [];

            foreach ($emails as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    array_push($invalidEmails, $email);
                }
            }

            if (count($invalidEmails) > 0) {
                return redirect($project->path() . '/cases/new')->with(['message' => __('Not valid emails: ').implode(',', $invalidEmails), 'message_type' => 'error'])->withInput();
            }

            $emailInput = request('email');
            
            $emailArray = Helper::multiexplode(array(";", ","," "), $emailInput);
            
            foreach ($emailArray as $singleEmail) {
                $user = User::createIfDoesNotExists(User::firstOrNew(['email' => $singleEmail]), request('sendanywayemail'), request('sendanywayemailsubject'), request('sendanywayemailmessage'));
                $case = $project->addCase(request('name'), request('duration'));
                $case->addUser($user);
                $message .= $user->email . " has been invited. \n";
            }

            foreach ($project->getInputs() as $object) {
                $hasFileUpload = property_exists($object, 'type') && $object->type === 'audio recording';
                if ($hasFileUpload) {
                    $encrypted = Crypt::encryptString(Helper::random_str(60));
                    $case->forceFill([
                        'file_token' => $encrypted,
                    ])->save();
                }
            }
        }

        if (request('sendanywayemail') == true) {
            //send a email to remind the user is assigned to a new case
        }
        
        return redirect($project->path())->with(['message' => $message,'message_type' => 'success']);
    }

    /**
     * @param Project $project
     * @param Cases   $case
     * @return RedirectResponse|Redirector
     * @throws AuthorizationException
     */
    public function update(Project $project, Cases $case)
    {
        $this->authorize('update', $case->project);
        request()->validate(['name' => 'required']);
        $case->update([
            'name' => request('name')
        ]);
        return redirect($project->path());
    }

    /**
     * @param Cases $case
     * @return Application|Factory|View
     * @throws Exception
     */
    public function destroy(Cases $case)
    {
        $project = $case->project;
        if ($project->created_by == auth()->user()->id) {
            $case->delete();
        } else {
            return response()->json(['message' => 'You can\'t delete this case'], 403);
        }
    
        return response()->json([self::MESSAGE => 'Case Deleted.'], 200);
    }

    /**
     * @param Cases $case
     * @return Response|BinaryFileResponse
     */
    public function export(Cases $case)
    {
        if (auth()->user()->notOwnerNorInvited($case->project)) {
            abort(403, __('you can\'t see the data of this project.'));
        }
        $headings = $this->getProjectInputHeadings($case->project);
        return (new CasesExport($case->id, $headings))->download('case ' . $case->name . '.xlsx');
    }

    /**
     * @param Project $project
     * @return array
     */
    private function getProjectInputHeadings(Project $project): array
    {
        $headings = [];
        foreach (json_decode($project->inputs) as $input) {
            $isMultipleOrOneChoice = property_exists($input, "numberofanswer") && $input->numberofanswer > 0;
            if ($isMultipleOrOneChoice) {
                for ($i = 0; $i < $input->numberofanswer; $i++) {
                    array_push($headings, $input->name);
                }
            } else {
                array_push($headings, $input->name);
            }
        }
        return $headings;
    }

    /**
     * @param Cases $case
     * @param array $data
     */
    private function assignColorsToMedia(Cases $case, array &$data): void
    {
        /* $data['media'] = $case->entries()
                     ->leftJoin('media', 'entries.media_id', '=', 'media.id')
                     ->get()->unique(); */
        $tempArray = $case->entries()
            ->leftJoin('media', 'entries.media_id', '=', 'media.id')
            ->get()->unique()->toArray();
        $data['media'] = [];
        foreach ($tempArray as $media) {
            $mediaEntry = [];
            foreach ($media as $key => $property) {
                $mediaEntry[$key] = $property;
            }
            $mediaEntry['color'] = array_rand(array_flip(config('colors.chartCategories')), 1);
            array_push($data['media'], $mediaEntry);
        }
    }

    /**
     * @param array $data
     * @param array $textInputToUnset
     * @param       $inputsToFilter
     */
    private function filterUnusedInputs(array &$data, array $textInputToUnset, &$inputsToFilter): void
    {
        $inputs = array_column($data['entries'], 'inputs');
        //  $inputs = array_map(function($o) { return $o['inputs']; }, $data['entries']);
        $inputs = array_map(function ($o) {
            $return = isset($o[0]) ? $o[0]->name : '';
            return $return;
        }, $inputs);
        // $inputsa = array_column($inputs, 'name');
        // dd($data['availableInputs']);
        $inputsToFilter = [];
        foreach ($data['availableInputs'] as $key => $availableInput) {
            if (!in_array($availableInput->name, $inputs)) {
                array_push($inputsToFilter, $key);
            }
        }
        foreach ($inputsToFilter as $key) {
            unset($data['availableInputs'][$key]);
        }
        foreach ($textInputToUnset as $key) {
            unset($data['availableInputs'][$key]);
        }
    }

    /**
     * @param Project $project
     * @param Cases   $case
     * @param mixed   $data
     * @return mixed
     */
    private function getGraphBreadcrumb(Project $project, Cases $case, mixed $data)
    {
        if (env('APP_ENV') === 'production') {
            $slug = '/metag';
        } else {
            $slug = '';
        }
        $data['breadcrumb'] = [
            url('/') => 'Metag',
            url('/') => 'Projects',
            $slug . $project->path() => $project->name,
            $slug . $case->path() => substr($case->name, 0, 15) . '...'
        ];
        return $data;
    }
}
