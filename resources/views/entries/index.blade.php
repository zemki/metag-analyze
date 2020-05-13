@extends('layouts.app')

@section('content')

    <div class="block text-center">
        <h1 class="font-bold text-4xl text-center my-2 font-serif inline mr-4">Entries</h1>
        <button class="text-center bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
                @click="showentriestable = !showentriestable">{{__('Toggle Entries')}}</button>
    </div>
    <table class="table-fixed w-full" v-show="showentriestable">
        <thead>
        <tr>
            <th class="px-4 py-2">{{__('Begin')}}</th>
            <th class="px-4 py-2">{{__('End')}}</th>
            <th class="px-6 py-2">{{__('Inputs')}}</th>
            <th class="px-3 py-2">{{__('Media')}}</th>
            @if($case->isBackend())
                <th class="px-3 py-2">{{__('Actions')}}</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach($entries['list'] as $entry)
            <tr>
                <td class="border px-4 py-2">{{date('d.m.Y H:i:s', strtotime($entry->begin))}}</td>
                <td class="border px-4 py-2">{{date('d.m.Y H:i:s', strtotime($entry->end))}}</td>
                <td class="border px-6 py-2">
                    @foreach($entry->inputs as $key => $input)
                        <div class="inline-block">
                            <div class="inline font-bold">
                                {{$key}}
                            </div>
                            <div class="inline">
                                @if(is_array($input))
                                    @foreach($input as $answer)
                                        {{$answer}}
                                    @endforeach
                                @else
                                    {{$input}}
                                @endif

                            </div>
                        </div>

                    @endforeach

                </td>
                <td class="border px-3 py-2">{{$entry->media_id}}</td>
                @if($case->isBackend())
                    <td class="border px-3 py-2">

                        <a href="#" @click="confirmdelete({{$entry->case_id}},{{$entry->id}},{{$entries['list']->count() == 1 ? 'true' : 'false'}})"
                           class="bg-red-600 hover:bg-red-700 text-black font-bold py-2 px-4 rounded inline-flex items-center">
                            <b-icon
                                    class="fill-current w-4 h-4 mr-2"
                                    icon="delete"
                            >
                            </b-icon>
                            Delete</a>

                    </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
    <h1 class="font-bold text-4xl text-center my-2 font-serif">{{__('Graphs')}}</h1>

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
