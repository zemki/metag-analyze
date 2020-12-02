<?php

namespace App\Http\Controllers;

use App\Cases;
use App\Entry;
use App\Exports\CasesExport;
use App\Media;
use App\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectCasesController extends Controller
{

    protected const PROJECT = 'project';
    
    /**
     * @param Project $project
     * @param Cases   $case
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function distinctshow(Project $project, Cases $case)
    {

        if (auth()->user()->notOwnerNorInvited($project) && !auth()->user()->isAdmin())
        {
            abort(403);
        }
        list($mediaValues, $availableMedia) = Cases::getMediaValues($case);
        list($availableInputs, $data) = Cases::getInputValues($case, $data);
        $data['entries']['list'] = Entry::where('case_id', '=', $case->id)->get();
        foreach ($data['entries']['list'] as $entry)
        {
            $entry['inputs'] = json_decode($entry['inputs']);
            $entry['media_id'] = Media::firstWhere('id', $entry['media_id'])->name;
        }
        $data['entries']['media'] = $mediaValues;
        $data['entries']['availablemedia'] = $availableMedia;
        //$data['entries']['inputs'] = $inputValues;
        $data['entries']['availableinputs'] = $availableInputs;
        $data['case'] = $case;
        $data['project'] = $project;
        $data['breadcrumb'] = [
            url('/') => 'Metag',
            url('/') => 'Projects',
            $project->path() => $project->name,
            $case->path() => substr($case->name, 0, 15) . '...'
        ];
        return view('entries.distinctcases', $data);
    }

    public function hariboshow(Project $project, Cases $case)
    {
        $tempArray = Entry::where('case_id', '=', $case->id)->with('media')->get()->toArray();
        $data['entries'] = [];
        $data['availableInputs'] = $project->getAnswersInputs();
        $textInputToUnset = [];

        foreach ($tempArray as $entry)
        {
            $tempInputsArray = json_decode($entry['inputs'], true);
            $inputEntry = [];
            $inputEntry['inputs'] = [];

            // format the inputs for the graph
            foreach ($tempInputsArray as $question => $answer)
            {
                if (is_array($answer))
                {

                    foreach ($answer as $singularAnswer)
                    {
                        $aritemp['id'] = $entry['id'];
                        $aritemp['name'] = $singularAnswer;
                        foreach ($data['availableInputs'] as $availableInput)
                        {
                            if($availableInput->name == $singularAnswer)
                            {
                                $aritemp['color'] = $availableInput->color;
                                break;
                            }
                        }
                        array_push($inputEntry['inputs'], (object)$aritemp);
                    }
                }else if(strlen($answer) > 1){
#text
                    $aritemp['id'] = $entry['id'];
                    $aritemp['name'] = $answer;

                    foreach ($data['availableInputs'] as $key => $availableInput)
                    {
                        if($availableInput->name == $question)
                        {
                            array_push($data['availableInputs'],(object)["id" => count($data['availableInputs'])+1,"name" => $answer,"color" => $availableInput->color]);
                            $aritemp['color'] = $availableInput->color;
                            array_push($textInputToUnset,$key);
                            break;
                        }
                    }
                    array_push($inputEntry['inputs'], (object)$aritemp);
                } else
                {
                    # scale
                    $aritemp['id'] = $entry['id'];
                    $aritemp['name'] = $answer;

                    foreach ($data['availableInputs'] as $availableInput)
                    {
                        if($availableInput->name == $answer)
                        {
                            $aritemp['color'] = $availableInput->color;
                            break;
                        }
                    }
                    array_push($inputEntry['inputs'], (object)$aritemp);
                }
            }
            // translate the object to array
            foreach ($entry as $key => $property)
            {
                if ($key == "inputs") continue;
                $inputEntry[$key] = $property;
            }
            $inputEntry['begin'] = strtotime($inputEntry['begin']);
            $inputEntry['end'] = strtotime($inputEntry['end']);
            $inputEntry['color'] = array_rand(array_flip(config('colors.chartCategories')), 1);
            array_push($data['entries'], $inputEntry);
        }
        foreach ($textInputToUnset as $key)
        {
            unset($data['availableInputs'][$key]);
        }
        /* $data['media'] = $case->entries()
             ->leftJoin('media', 'entries.media_id', '=', 'media.id')
             ->get()->unique(); */
        $tempArray = $case->entries()
            ->leftJoin('media', 'entries.media_id', '=', 'media.id')
            ->get()->unique()->toArray();
        $data['media'] = [];
        foreach ($tempArray as $media)
        {
            $mediaEntry = [];
            foreach ($media as $key => $property)
            {
                $mediaEntry[$key] = $property;
            }
            $mediaEntry['color'] = array_rand(array_flip(config('colors.chartCategories')), 1);
            array_push($data['media'], $mediaEntry);
        }

        sort($data['availableInputs']);

        return view('entries.haribocases', $data);
    }

    /**
     * Create a case belonging to a project
     * @param Project $project
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Project $project)
    {
        if (auth()->user()->notOwnerNorInvited($project))
        {
            abort(403);
        }
        $data['breadcrumb'] = [
            url('/') => 'Projects',
            url($project->path()) => $project->name,
            '#' => 'Create Case'
        ];
        $data['users'] = User::all();
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
        if (auth()->user()->notOwnerNorInvited($project))
        {
            abort(403);
        }        if (request('name') == "")
        {
            return redirect($project->path() . '/cases/new')->with(['message' => __('Please fill all the required inputs.')]);
        }
        request()->validate(
            ['name' => 'required']
        );
        if (request('backendCase'))
        {
            $user = auth()->user();
            $case = $project->addCase(request('name'), 'value:0|days:0|lastDay:' . Carbon::now()->subDay());
        } else
        {
            $email = request('email');
            $case = $project->addCase(request('name'), request('duration'));
            $user = User::createIfDoesNotExists(User::firstOrNew(['email' => $email]));
        }
        $case->addUser($user);
        return redirect($project->path())->with(['message' => $user->email . ' has been invited.']);
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
     * @return RedirectResponse|Redirector
     * @throws \Exception
     */
    public function destroy(Cases $case)
    {
        $project = $case->project;
        if ($project->created_by == auth()->user()->id)
        {

            $case->delete();
        } else
        {
            return response()->json(['message' => 'You can\'t delete this case'], 403);
        }
        $data['breadcrumb'] = [url('/') => 'Projects', '#' => substr($project->name, 0, 20) . '...'];
        $project->media = $project->media()->pluck('media.name')->toArray();
        $data['data']['media'] = Media::all();
        $data['project'] = $project;
        $data['invites'] = $project->invited()->get();
        $data[self::PROJECT.'media'] = $project->media()->pluck('media.name')->toArray();
        $data['message'] = "Case deleted";
        return view('projects.show', $data);
    }

    /**
     * @param Cases $case
     * @return Response|BinaryFileResponse
     */
    public function export(Cases $case)
    {
        if (auth()->user()->notOwnerNorInvited($case->project))
        {
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
        foreach (json_decode($project->inputs) as $input)
        {
            $isMultipleOrOneChoice = property_exists($input, "numberofanswer") && $input->numberofanswer > 0;
            if ($isMultipleOrOneChoice) for ($i = 0; $i < $input->numberofanswer; $i++) array_push($headings, $input->name);
            else array_push($headings, $input->name);
        }
        return $headings;
    }
}
