<?php

namespace App\Http\Controllers;

use App\Cases;
use App\Mail\VerificationEmail;
use App\Project;
use App\Role;
use App\User;
use Helper;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Mail;

class ProjectCasesController extends Controller
{

    /**
     * @param Project $project
     * @param Cases $case
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Project $project, Cases $case)
    {


        if (auth()->user()->isNot($project->created_by()) && !in_array($project->id, auth()->user()->invites()->pluck('project_id')->toArray())) {
            abort(403);
        }
        $mediaEntries = $case->entries()
            ->join('media', 'entries.media_id', '=', 'media.id')
            ->get()
            ->map
            ->only(['name', 'begin', 'end'])
            ->flatten()
            ->chunk(3)
            ->toArray();
        // THEN GET ENTRIES BY INPUTS - EXTRACT FROM ENTRIES->INPUT THE LIST OF THEM, THEN DO THE SAME THING - DYNAMIC VAR NAME?


        $entries = $case->entries()
            ->join('cases', 'entries.case_id', '=', 'cases.id')
            ->join('projects', 'cases.project_id', '=', 'projects.id')
            ->select('entries.inputs', 'entries.begin', 'entries.end', 'projects.inputs as pr_inputs')
            ->where('entries.inputs', '<>', '[]')
            ->get()
            ->toArray();

        $getInputNameFunction = function ($o) {
            return $o->name;
        };

        $availableInputs = array_map($getInputNameFunction, json_decode($entries[0]['pr_inputs']));

        $inputValues = [];
        $mediaValues = [];

        // STRUCTURE CHANGE:
        // a graph for each type of input
        // complete multiple inputs and one choice with the pr_input field
        // complete scale with numbers 1 to 5
        // complete means at least one value for each available input

        foreach ($entries as $entry) {
            $inputs = json_decode($entry["inputs"], true);

            foreach ($inputs as $key => $index) {
                array_push($inputValues, ["value" => $index, "name" => $key, "start" => $entry["begin"], "end" => $entry["end"]]);
            }

        }


        foreach (array_map('array_values', $mediaEntries) as $media) {
            array_push($mediaValues, ["value" => $media[0], "start" => $media[1], "end" => $media[2]]);
        }

        $availableMedia = $case->entries()
            ->leftJoin('media', 'entries.media_id', '=', 'media.id')
            ->pluck('media.name')->unique()->toArray();


        $availableOptions = json_decode($entries[0]['pr_inputs']);
        foreach ($availableOptions as $availableOption) {
            $availableOptions[$availableOption->type] = $availableOption;
        }
        dd($availableOptions);

        foreach ($availableInputs as $availableInput) {
            //if ($availableInput == "text") continue;
            $data['entries']['inputs'][$availableInput] = array();
            $data['entries']['inputs'][$availableInput]['title'] = $availableInput;

            if ($availableInput == "multiple inputs") $data['entries']['inputs'][$availableInput]['available'] = $availableOptions["multiple choice"]->answers;
            else if ($availableInput == "one choice") $data['entries']['inputs'][$availableInput]['available'] = $availableOptions["one choice"]->answers;
            else if ($availableInput == "stars") $data['entries']['inputs'][$availableInput]['available'] = [1, 2, 3, 4, 5];
            else if ($availableInput == "text") {
                $data['entries']['inputs'][$availableInput]['available'] = [];
                // loop through the values you already have and make it part of the 'available'
                foreach ($inputValues as $inputValue) {
                    if ($inputValue['type'] == "text")array_push($data['entries']['inputs'][$availableInput]['available'],$inputValue['value']);
                }
            }


            // set here available inputs
            foreach ($inputValues as $inputValue) {
                if ($inputValue['type'] == $availableInput) array_push($data['entries']['inputs'][$availableInput], $inputValue);
            }


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
            $case->path() => $case->name
        ];

        return view('entries.index', $data);
    }


    /**
     * @param Project $project
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Project $project)
    {
        $data['breadcrumb'] = [
            url('/') => 'Projects',
            url($project->path()) => $project->name,
            '#' => 'Create Case'
        ];

        if (auth()->user()->isNot($project->created_by()) && !in_array($project->id, auth()->user()->invites()->pluck('project_id')->toArray())) {
            abort(403);
        }

        $data['users'] = User::all();
        $data['project'] = $project;

        return view('cases.create', $data);

    }


    /**
     * @param Project $project
     * @return RedirectResponse|Redirector
     * @throws AuthorizationException
     */
    public function store(Project $project, Request $request)
    {
        $this->authorize('update', $project);
        request()->validate(
            ['name' => 'required'],
            ['email' => 'required']
        );

        if (!$request->filled('duration')) return redirect()->back()->with(['message' => 'Duration is mandatory.']);
        $email = request('email');
        $case = $project->addCase(request('name'), request('duration'));
        $user = User::firstOrNew(['email' => $email]);

        $this->createUserIfDoesNotExists($user, $password);

        $case->addUser($user);

        return redirect($project->path())->with(['message' => $user->email . ' has been invited.']);
    }

    /**
     * @param Project $project
     * @param Cases $case
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
     * @param Project $project
     * @param Cases $case
     * @return RedirectResponse|Redirector
     * @throws \Exception
     */
    public function destroy(Project $project, Cases $case)
    {
        if ($case->isEditable()) {
            $case->delete();
        } else {
            return redirect()->back()->with('message', 'case has entries, you cannot delete it');
        }

        return redirect($project->path())->with('message', 'case deleted');

    }

    /**
     * @param $user
     * @param $password
     */
    protected function createUserIfDoesNotExists($user, &$password): void
    {
        if (!$user->exists) {
            $user->email = request('email');
            $role = Role::where('name', '=', 'user')->first();
            $user->password = bcrypt(Helper::random_str(60));
            $user->password_token = bcrypt(Helper::random_str(60));
            $user->save();
            $user->roles()->sync($role);
            Mail::to($user->email)->send(new VerificationEmail($user, config('utilities.emailDefaultText')));

        }
    }
}
