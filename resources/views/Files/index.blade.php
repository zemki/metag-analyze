@extends('layouts.app')

@section('content')

@foreach($files as $file)
<audio-player file={{$file['audiofile']}} loop="true" autoplay="false" name="{{$file['path']}}"></audio-player>
@endforeach

@endsection