@extends('layouts.app')

@section('content')
    <graph :info="{{json_encode($entries['media'])}}"
           title="Media"
           :availabledata="{{json_encode($entries['availablemedia'])}}"
    ></graph>

    @isset($entries['inputs'])
        @foreach($entries['inputs'] as $input)
            <graph :info="{{json_encode($input)}}"
                   :title="{{json_encode($input['title'])}}"
                   :availabledata="{{json_encode($input['available'])}}"
            ></graph>
        @endforeach
    @endisset

@endsection
@section('pagespecificcss')


@endsection
