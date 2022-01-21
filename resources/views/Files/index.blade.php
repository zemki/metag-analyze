@extends('layouts.app')

@section('content')
<div class="flex flex-wrap content-center w-full overflow-hidden sm:-mx-2">

@foreach($files as $file)
<audio-player class="w-96 sm:my-2 sm:px-2" file={{$file['audiofile']}} loop="true" autoplay="false" name="{{$file['path']}}"></audio-player>

@endforeach
</div>
@endsection