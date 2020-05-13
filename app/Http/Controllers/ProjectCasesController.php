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
    /**
     * @param Project $project
     * @param Cases   $case
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Project $project, Cases $case)
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
            $entry['media_id'] = Media::firstWhere('id',$entry['media_id'])->name;
        }
        $data['entries']['media'] = $mediaValues;
        $data['entries']['availablemedia'] = $availableMedia;
        //$data['entries']['inputs'] = $inputValues;
        $data['entries']['availableinputs'] = $availableInputs;
        $data['case'] = $case;
        $data['breadcrumb'] = [
            url('/') => 'Metag',
            url('/') => 'Projects',
            $project->path() => $project->name,
            $case->path() => substr($case->name, 0, 15) . '...'
        ];
        return view('entries.index', $data);
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
        $this->authorize('update', $project);
        if (request('name') == "")
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
