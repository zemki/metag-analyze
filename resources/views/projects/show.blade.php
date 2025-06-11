@extends('layouts.app')

@section('content')
    @include('layouts.breadcrumb')

    <project-cases-view
        :project='@json($project)'
        :project-inputs='@json($project->inputs)'
        :project-media='@json($projectmedia)'
        :invites='@json($invites)'
        :inputs-config='@json(config("inputs"))'
        :is-creator="{{ auth()->user()->is($project->creator()) ? 'true' : 'false' }}"
    ></project-cases-view>

@endsection

@section('pagespecificscripts')
    <!-- Add any page-specific scripts here -->
@endsection
