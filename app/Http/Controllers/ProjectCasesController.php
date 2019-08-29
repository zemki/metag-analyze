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
        $data['entriesByMedia'] = $case->entries()
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
            ->select('entries.id', 'entries.inputs', 'projects.inputs as pr_inputs', 'entries.begin', 'entries.end')
            ->get()
            ->toArray();
        $inputsEntries = array();
        $types = array();
        $finalArray = array();

        $k = 0;

        /*
         * FORMAT FOR THE GRAPH IN JS
         * [1] VALUE
         * [2] NAME TO SHOW
         * [3] STYLE
         * [4] BEGIN <- js format
         * [5] END <- js format
         * AVOID TO CREATE SAME NAME INPUTS
         */

        for ($i = 0; $i < count($entries); $i++) {
            $entries[$i]['inputs'] = collect(json_decode($entries[$i]['inputs']));
            $entries[$i]['pr_inputs'] = collect(json_decode($entries[$i]['pr_inputs']));

            foreach ($entries[$i]['inputs'] as $key => $entry) {

                $currentType = "";
                $currentName = "";
                $pr_inputKey = 0;

                for ($j = 0; $j < count($entries[$i]['pr_inputs']); $j++) {
                    if ($entries[$i]['pr_inputs'][$j]->name == $key) {
                        $currentType = $entries[$i]['pr_inputs'][$j]->type;
                        $currentName = $entries[$i]['pr_inputs'][$j]->name;
                        $pr_inputKey = $j;
                    }
                }

                $inputName = $currentName;

                $inputsEntries[$inputName][$i] = array();
                $inputsEntries[$inputName][$i] = $entries[$i]['inputs']->merge($entries[$i]['pr_inputs'][$pr_inputKey]);
                $inputsEntries[$inputName][$i]->put('begin', $entries[$i]['begin']);
                $inputsEntries[$inputName][$i]->put('end', $entries[$i]['end']);


                if (!Helper::in_array_recursive($inputsEntries[$inputName][$i]['type'], $types) ||
                    !Helper::in_array_recursive($inputsEntries[$inputName][$i]['name'], $types)) {
                    $types[$k]['type'] = $inputsEntries[$inputName][$i]['type'];
                    $types[$k]['name'] = $inputsEntries[$inputName][$i]['name'];
                    $k++;
                }

            }
            /*
                        $inputName = (string)$entries[$i]['pr_inputs']->first()->name;

                        $inputsEntries[$inputName][$i] = array();
                        $inputsEntries[$inputName][$i] = $entries[$i]['inputs']->merge($entries[$i]['pr_inputs'][0]);
                        $inputsEntries[$inputName][$i]->put('begin', $entries[$i]['begin']);
                        $inputsEntries[$inputName][$i]->put('end', $entries[$i]['end']);


                        $finalArray[$inputName][$i] = array();
                        array_push($finalArray[$inputName][$i], (string)$entries[$i]['inputs']->first());
                        array_push($finalArray[$inputName][$i], (string)$entries[$i]['pr_inputs']->first()->name);
                        $dateBegin = date("D M d Y H:i:s \G\M\TO (T)", strtotime($entries[$i]['begin']));
                        $dateEnd = date("D M d Y H:i:s \G\M\TO (T)", strtotime($entries[$i]['end']));

                        array_push($finalArray[$inputName][$i], $dateBegin);
                        array_push($finalArray[$inputName][$i], $dateEnd);

                        if (!Helper::in_array_recursive($inputsEntries[$inputName][$i]['type'], $types) ||
                            !Helper::in_array_recursive($inputsEntries[$inputName][$i]['name'], $types)) {
                            $types[$k]['type'] = $inputsEntries[$inputName][$i]['type'];
                            $types[$k]['name'] = $inputsEntries[$inputName][$i]['name'];
                            $k++;
                        }*/
        }


        $data['entriesbyInputs'] = $inputsEntries;
        $data['entriesByMedia'] = array_map('array_values', $data['entriesByMedia']);
        $data['entriesbyInputs'] = array_map('array_values', $data['entriesbyInputs']);
        $data['types'] = $types;
        $data['case'] = $case;


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
            $project->path() => $project->name,
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
    public function store(Project $project)
    {
        $this->authorize('update', $project);
        request()->validate(
            ['name' => 'required'],
            ['email' => 'required']
        );
        $email = request('email');
        $case = $project->addCase(request('name'), request('duration'));
        $user = User::firstOrNew(['email' => $email]);

        $this->createUserIfDoesNotExists($user, $password);

        $case->addUser($user);

        return redirect($project->path())->with(['message' => $user->email . ' will receive an email to set the password.']);
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
            $password = Helper::random_str(60);
            $user->password = bcrypt($password);
            $user->save();
            $user->roles()->sync($role);
            Mail::to($user->email)->send(new VerificationEmail($user, config('utilities.emailDefaultText')));

        }
    }
}
