@extends('layouts.app')

@section('content')
    <h1 class="font-bold text-4xl text-center my-2 font-serif">{{__('Graphs')}}</h1>
    <div class="align-middle justify-center items-center flex">
        <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded inline-flex items-center m-2" @click="switchMediaAndInputsOnGraph">
            <svg class="fill-current w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
            </svg>
            <span>Swap</span>
        </button>
{{--        <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded inline-flex items-center m-2" @click="switchFormatter">
            <span>Day Numbers</span>
        </button>--}}

        <div class="mb-4 flex flex-wrap justify-center items-center">
            <label for="filetype"
                   class="w-32 ml-2 mt-4 leading-normal">{{ __('Select file type') }}</label>
            <select name="filetype" class="w-64 mt-4 bg-white border border-gray-400 hover:border-gray-500 pr-4 py-2 focus:outline-none"
                    v-model="chart.type"
            >
                <option v-for="filetype,key in chart.typeSelect" :value="filetype">@{{key}}</option>
            </select>
        </div>

        <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded inline-flex items-center m-2" @click="downloadChart">
            <svg class="fill-current w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            <span>Download</span>
        </button>


    </div>


    <div id="container">
        <medtaggraph
                :media="{{json_encode($media)}}"
                :inputs="{{json_encode($availableInputs)}}"
                :entries="{{json_encode($entries)}}"
                :yColumn="chart.yColumn"
                ref="graph"
        ></medtaggraph>
    </div>

@endsection
@section('pagespecificcss')
@endsection
