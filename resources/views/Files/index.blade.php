@extends('layouts.app')

@section('content')
<h1 class="block text-4xl font-bold capitalize">{{$case->name}}</h1>

<div class="flex flex-wrap content-center w-full overflow-hidden sm:-mx-2">
@foreach($files as $file)
<audio-player caseid="{{$case->id}}" class="w-96 sm:my-2 sm:px-2" :file="{{$file}}" loop="true" autoplay="false" name="{{$file['path']}}" date="{{date("d.m.Y H:i:s", strtotime($file->created_at))}}" ></audio-player>

@endforeach
</div>
@endsection