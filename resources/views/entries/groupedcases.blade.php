@extends('layouts.app')

@section('content')
@include('layouts.breadcrumb')
<h1 class="my-2 font-serif text-4xl font-bold text-center">{{__('Graphs')}}</h1>
<div class="flex items-center justify-center align-middle">
    <button class="inline-flex items-center px-4 py-2 m-2 font-bold text-gray-800 bg-gray-300 rounded hover:bg-gray-400"
        @click="switchMediaAndInputsOnGraph">
        <svg class="w-6 h-6 fill-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
        </svg>
        <span>Swap</span>
    </button>
    {{-- <button class="inline-flex items-center px-4 py-2 m-2 font-bold text-gray-800 bg-gray-300 rounded hover:bg-gray-400" @click="switchFormatter">
            <span>Day Numbers</span>
        </button>--}}

    <div class="flex flex-wrap items-center justify-center mb-4">
        <label for="filetype" class="w-32 mt-4 ml-2 leading-normal">{{ __('Select file type') }}</label>
        <select name="filetype"
            class="w-64 py-2 pr-4 mt-4 bg-white border border-gray-400 hover:border-gray-500 focus:outline-hidden"
            v-model="chart.type">
            <option v-for="filetype,key in chart.typeSelect" :value="filetype">@{{key}}</option>
        </select>
    </div>

    <button class="inline-flex items-center px-4 py-2 m-2 font-bold text-gray-800 bg-gray-300 rounded hover:bg-gray-400"
        @click="downloadChart">
        <svg class="w-6 h-6 fill-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
        </svg>
        <span>Download</span>
    </button>


</div>

<div id="container">
    <medtaggraph :media="{{json_encode($media)}}" :inputs="{{json_encode($availableInputs)}}"
        :entries="{{json_encode($entries)}}" :yColumn="chart.yColumn" ref="graph"></medtaggraph>
</div>

@endsection
@section('pagespecificcss')
@endsection