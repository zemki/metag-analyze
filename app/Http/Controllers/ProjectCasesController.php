<?php

namespace App\Http\Controllers;

use App\Cases;
use App\Mail\VerificationEmail;
use App\Project;
use App\Role;
use App\User;

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
        if (auth()->user()->notOwnerNorInvited($project)) {
            abort(403);
        }

        list($mediaValues, $availableMedia) = Cases::getMediaValues($case);
        list($availableInputs, $inputValues, $data) = Cases::getInputValues($case,$data);

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
        $data['breadcrumb'] = [
            url('/') => 'Projects',
            url($project->path()) => $project->name,
            '#' => 'Create Case'
        ];

        if (auth()->user()->notOwnerNorInvited($project)) {
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
            ['email' => 'required'],
            ['duration' => 'required']
        );

        $email = request('email');
        $case = $project->addCase(request('name'), request('duration'));

        $user = User::createIfDoesNotExists(User::firstOrNew(['email' => $email]));

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



}
