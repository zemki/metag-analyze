@extends('layouts.app')

@section('content')
<div class="mb-5 bg-blue-500 rounded-sm">
  <div class="px-3 py-3 mx-auto max-w-7xl sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between">
      <div class="flex items-center flex-1 w-0">
        <span class="flex p-2 bg-indigo-800 rounded-lg">
          <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
          </svg>
        </span>
        <p class="ml-3 font-medium text-white truncate">
          <span class="hidden md:inline">
            {{__('When you delete a file here, the entry rests untouched.')}}
          </span>
        </p>
      </div>
    </div>
  </div>
</div>
<h1 class="block text-4xl font-bold capitalize">{{$case->name}} <span class="text-base font-normal normal-case">{{$case->user->email}}</span></h1>

<div class="flex flex-wrap content-center w-full overflow-hidden sm:-mx-2">
@foreach($files as $file)
<audio-player caseid="{{$case->id}}" class="w-96 sm:my-2 sm:px-2" :file="{{$file}}" loop="false" autoplay="false" name="{{$file['path']}}" date="{{date("d.m.Y H:i:s", strtotime($file->created_at))}}" ></audio-player>

@endforeach
</div>
@endsection