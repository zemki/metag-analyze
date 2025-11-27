@extends('layouts.app')

@section('content')

    @include('layouts.breadcrumb')

    <create-project
            :inputs='@json(config("inputs"))'
            :user-id='@json(auth()->user()->id)'
            :mart-enabled='@json($martEnabled)'
    ></create-project>

@endsection
