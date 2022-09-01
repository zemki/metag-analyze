@extends('layouts.app')

@section('content')
@include('layouts.breadcrumb')
<div class="flex flex-col h-full">
    <div>
        <div class="my-2">
            <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight lg:text-6xl">
                {{__('Create a Project')}}
            </h1>
            <p class="mt-5 text-xl text-gray-500">
                {{__('The predefined inputs are Begin Date/Time,
                                        End Date/time and
                                        Media  used.
                                        You can enter up to 3 additional inputs giving them name and details,
                                        this will be reflected in the mobile app.')}}
            </p>
        </div>
    </div>
</div>


<form method="POST" action="{{route('projects')}}" class="mx-auto" style="padding-top: 40px" @submit="validateProject">
    @csrf
    <input type="hidden" value="{{auth()->user()->id}}" name="created_by">
    <div class="my-2">
        <label for="name" class="block text-sm font-medium text-gray-700">{{__('Name')}} *</label>
        <div class="mt-2">
            <input type="text" name="name" id="name" v-model="newproject.name" value="{{ old('name') }}"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            <span
                :class="newproject.inputLength.name <= newproject.name.length ? 'text-red-600 text-xs w-auto inline-flex float-right' : 'text-xs text-gray-500 w-auto inline-flex float-right'">@{{newproject.inputLength.name - newproject.name.length}}
                / @{{newproject.inputLength.name}}</span>
        </div>
    </div>
    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">{{__('Description')}} *</label>
        <div class="mt-1">
            <textarea rows="4" name="description" id="description" v-model="newproject.description"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
        </div>
    </div>

    <div class="block">
        <label for="name" class="text-base font-bold tracking-wide text-gray-700 uppercase">
            {{__('Title')}} *
        </label>

        <input type="text" class="input" name="name" v-model="newproject.name">

        <span
            :class="newproject.inputLength.name <= newproject.name.length ? 'text-red-600 text-xs w-auto inline-flex float-right' : 'text-xs text-gray-500 w-auto inline-flex float-right'">@{{newproject.inputLength.name - newproject.name.length}}
            / @{{newproject.inputLength.name}}</span>

    </div>

    <label for="description" class="text-base font-bold tracking-wide text-gray-700 uppercase">
        {{__('Description')}} *
    </label>

    <textarea name="description" id="textarea"
        class="w-full h-32 p-2 border rounded resize-y focus:outline-none focus:ring"
        v-model="newproject.description"></textarea>
    <span
        :class="newproject.inputLength.description <= newproject.description.length ? 'text-red-600 text-xs w-auto inline-flex float-right' : 'text-xs text-gray-500 w-auto inline-flex float-right'">@{{newproject.inputLength.description - newproject.description.length}}
        / @{{newproject.inputLength.description}}</span>

    <div class="my-3 text-xs">* {{__('required')}}</div>


    <input type="hidden" :value="JSON.stringify(newproject.inputs)" name="inputs">
    <div class="inline-block w-full">
        <label for="media" class="inline-flex text-base font-bold tracking-wide text-gray-700 uppercase">
            {{__('Media')}}
        </label>
        <p class="text-sm">
            {!! __('The user will be able to enter her/his own media. <br> Here you can write a predefined list from
            which it\'s possible to choose a media. <br> The list will appear as soon as the user select the media
            input and type anything.') !!}
        </p>
        <div class="block mt-2" v-for="(m,index) in newproject.media">
            <input type="text" name="media[]"
                class="block w-64 p-2 border-b-2 border-blue-500 rounded-md shadow-none first:mt-0 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                :tabindex="index+1" v-model="newproject.media[index]" @keyup.capture="handleMediaInputs(index,m)"
                autocomplete="off" @keydown.enter.prevent>
        </div>
    </div>

    <div class="block w-full">
        <label for="inputs" class="text-sm font-medium text-gray-700">
            {{trans('Number of additional inputs')}}</label>
        <div class="relative flex flex-row w-64 h-10 mt-1 bg-transparent rounded-lg">
            <button
                class="w-20 h-full text-gray-600 bg-gray-300 rounded-l outline-none cursor-pointer hover:text-gray-700 hover:bg-gray-400"
                @click.prevent="(newproject.ninputs >= 1) ? newproject.ninputs-- : newproject.ninputs">
                <span class="m-auto text-2xl font-thin">−</span>
            </button>
            <input v-model.number="newproject.ninputs"
                class="flex items-center w-full font-semibold text-center text-gray-700 bg-white outline-none focus:outline-none text-md hover:text-black focus:text-black md:text-basecursor-default"
                max="3" min="0" steps="!" name="inputs" id="ninputs" type="number" value="0"></input>
            <button
                class="w-20 h-full text-gray-600 bg-gray-300 rounded-r cursor-pointer hover:text-gray-700 hover:bg-gray-400"
                @click.prevent="(newproject.ninputs <= 2) ? newproject.ninputs++ : newproject.ninputs">
                <span class="m-auto text-2xl font-thin">+</span>
            </button>
        </div>
    </div>

    <div class="" v-for="(t, index) in newproject.inputs" :key="index">
        <div>
            <label class="block text-sm font-medium text-gray-700">{{trans('Input Name')}}</label>
            <div class="">
                <input v-model="newproject.inputs[index].name" type="text"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    :disabled="!editable">
            </div>
        </div>
        <div class="relative flex items-start my-2">
            <div class="flex items-center h-5">
                <input v-model="newproject.inputs[index].mandatory" :disabled="!editable" checked="checked"
                    type="checkbox" class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500">
            </div>
            <div class="ml-3 text-sm">
                <label for="comments" class="font-medium text-gray-700">{{trans('Mandatory')}}</label>
            </div>
        </div>

        <label id="listbox-label" class="block text-sm font-medium text-gray-700"> {{trans('Type')}} </label>
        <div class="relative mt-1">
            <button @click="showDropdownInputs(index)" type="button"
                class="relative w-full py-2 pl-3 pr-10 text-left bg-white border border-gray-300 rounded-md shadow-sm cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                aria-haspopup="listbox">
                <span class="block truncate"> @{{newproject.inputs[index].type}} </span>
                <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                    <!-- Heroicon name: solid/selector -->
                    <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </span>
            </button>
            <ul :id="'type'+index"
                class="absolute z-10 hidden w-full py-1 mt-1 overflow-auto text-base bg-white rounded-md shadow-lg max-h-60 ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                tabindex="-1" role="listbox" aria-labelledby="listbox-label">
                <li :class="(type == newproject.inputs[index].type) ? 'relative py-2 pl-3 bg-blue-500 text-white cursor-default select-none pr-9' : 'relative py-2 pl-3 text-gray-900 cursor-default select-none pr-9'"
                    id="listbox-option-0" role="option" v-for="(type,indexT) in newproject.config.available"
                    :key="indexT" @click="newproject.inputs[index].type = type">
                    <!-- Selected: "font-semibold", Not Selected: "font-normal" -->
                    <span class="block font-normal truncate">@{{type}} </span>

                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-white"
                        v-if="type == newproject.inputs[index].type">
                        <!-- Heroicon name: solid/check -->
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                            aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>

            </ul>
        </div>


        <div v-if="
                      newproject.inputs[index].type == 'multiple choice' ||
                      newproject.inputs[index].type == 'one choice'
                    " class="mt-1">
            <label class="">Answers</label>
            <div class="mt-2" v-for="(m, answerindex) in newproject.inputs[index]
                          .answers" :key="answerindex">
                <input type="text" v-model="newproject.inputs[index].answers[answerindex]"
                    @keyup="handleAdditionalInputs(index, answerindex, m)" autocomplete="off" @keydown.enter.prevent
                    @keydown.tab.prevent :disabled="!editable"
                    class="block w-64 p-2 border-b-2 border-blue-500 rounded-md shadow-none first:mt-0 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
            </div>


        </div>
        <div class="relative mt-4 mb-2">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-gray-500 border-solid"></div>
            </div>
            <div class="relative flex justify-center">
                <span class="px-2 text-gray-500 bg-white">
                    <svg class="w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 20 20">
                        <path fill="#6B7280" fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                </span>
            </div>
        </div>
    </div>


    <div class="flex w-full">

        <div class="block w-full " v-for="(t,index) in newproject.inputs" :key="index">


            <label for="name" class="text-base font-bold tracking-wide text-gray-700 uppercase">
                {{__('Input Name')}}
            </label>

            <input type="text"
                class="block w-full px-4 py-2 mb-0 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring"
                v-model="newproject.inputs[index].name">

            <label class="block font-bold text-gray-500 md:w-2/3">
                <input class="mr-2 leading-tight" type="checkbox" v-model="newproject.inputs[index].mandatory">
                <span class="text-base">
                    {{__('Mandatory')}}
                </span>
            </label>


            <div class="w-full">
                <label class="text-base font-bold tracking-wide text-gray-700 uppercase">Type</label>
                <div class="relative">
                    <select
                        class="block w-full px-4 py-3 pr-8 leading-tight bg-white border border-gray-300 rounded appearance-none focus:outline-none focus:bg-gray focus:border-gray-500"
                        v-model="newproject.inputs[index].type">
                        <option v-for="type in newproject.config.available" :value="type">
                            @{{type}}
                        </option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 pointer-events-none">
                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                        </svg>
                    </div>
                </div>
            </div>
            <span
                v-if="(newproject.inputs[index].type == 'multiple choice' || newproject.inputs[index].type == 'one choice')">
                <label class="text-base font-bold tracking-wide text-gray-700 uppercase">{{__('Answers')}}</label>
                <div class="block" v-for="(m,answerindex) in newproject.inputs[index].answers">
                    <input type="text"
                        class="block w-full px-4 py-2 mb-0 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring inputcreatecase"
                        v-model="newproject.inputs[index].answers[answerindex]"
                        @keyup="handleAdditionalInputs(index,answerindex,m)" autocomplete="off" @keydown.enter.prevent>
                </div>
            </span>

        </div>
    </div>

    <div class="block mt-2">
        <div class="relative px-4 py-3 text-red-700 bg-red-100 border border-red-400 rounded"
            v-if="newproject.response != ''" v-html="newproject.response">
            <button class="delete" @click.preventdefault="newproject.response = ''"></button>
        </div>
    </div>


    <button
        class="px-4 py-2 font-semibold text-blue-700 bg-transparent border border-blue-500 rounded hover:bg-blue-500 hover:text-black hover:border-transparent">{{__('Create Project')}}</button>

</form>

@endsection