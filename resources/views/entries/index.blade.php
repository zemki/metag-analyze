@extends('layouts.app')

@section('content')
    <graph :info="{{json_encode($entries['media'])}}"
           :availabledata="{{json_encode($entries['availablemedia'])}}"
			></graph>

    @foreach($entries['inputs'] as $input)
        <graph :info="{{json_encode($input)}}"
               :title="{{json_encode($input['title'])}}"
               :availabledata="{{json_encode($input['available'])}}"
        ></graph>
    @endforeach

@endsection
@section('pagespecificcss')


@endsection
