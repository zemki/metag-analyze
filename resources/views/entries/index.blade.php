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
    @media print{
    @page {size: landscape}
    .no-print, .no-print *
    {
    display: none !important;
    }
    body {
    margin: 0;
    background-color: #fff;
    padding: 0;
    }
    .chart-left{
    width:100%;
    height:100%;
    page-break-after:always;
    margin-left: -150px;
    }
    .title-chart{
    margin-left: -150px;
    width:100%;
    }
    }
@endsection
