@extends('layouts.app')

@section('content')



    <h1 class="text-4xl font-bold font-serif">{{__('Create a Project')}}</h1>
    <p class="text-sm">{{__('The predefined inputs are Begin Date/Time,
                            End Date/time and
                            Media  used.
                            You can enter up to 3 additional inputs giving them name and details,
                            this will be reflected in the mobile app.')}}
    </p>

    <form method="POST" action="{{route('projects')}}" class="mx-auto" style="padding-top: 40px"
          @submit="validateProject">
        @csrf
        <input type="hidden" value="{{auth()->user()->id}}" name="created_by">
        <div class="block">
            <label for="name" class="uppercase tracking-wide text-gray-700 text-base font-bold">
                {{__('Title')}} *
            </label>

            <input type="text" class="input" name="name" v-model="newproject.name">

            <span
                    :class="newproject.inputLength.name <= newproject.name.length ? 'text-red-600 text-xs w-auto inline-flex float-right' : 'text-xs text-gray-500 w-auto inline-flex float-right'">@{{newproject.inputLength.name - newproject.name.length}} / @{{newproject.inputLength.name}}</span>

        </div>

        <label for="description" class="uppercase tracking-wide text-gray-700 text-base font-bold">
            {{__('Description')}} *
        </label>

        <textarea name="description" id="textarea" class="p-2 resize-y border rounded focus:outline-none focus:ring w-full h-32"
                  v-model="newproject.description"></textarea>
        <span
                :class="newproject.inputLength.description <= newproject.description.length ? 'text-red-600 text-xs w-auto inline-flex float-right' : 'text-xs text-gray-500 w-auto inline-flex float-right'">@{{newproject.inputLength.description - newproject.description.length}} / @{{newproject.inputLength.description}}</span>

        <div class="text-xs my-3">* {{__('required')}}</div>


        <input type="hidden" :value="JSON.stringify(newproject.inputs)" name="inputs">

        <b-field :label="trans('Number of additional inputs')">
            <b-numberinput name="ninputs" id="ninputs" controls-position="compact"
                           type="is-light" min="0" max="3" :editable="false" steps="1"
                           v-model.number="newproject.ninputs"></b-numberinput>
        </b-field>

        <div class="flex w-full">
            <div class="w-1/4 inline-block">
                <label for="media" class="uppercase tracking-wide text-gray-700 text-base font-bold inline-flex">
                    {{__('Media')}}
                </label>
                <p class="text-sm">
                    {!! __('The user will be able to enter her/his own media. <br> Here you can write a predefined list from which it\'s possible to choose a media. <br> The list will appear as soon as the user select the media input and type anything.') !!}
                </p>
                <div class="block mt-2" v-for="(m,index) in newproject.media">
                    <input type="text" name="media[]" class="inputcreatecase" :tabindex="index+1"
                           v-model="newproject.media[index]" @keyup.capture="handleMediaInputs(index,m)"
                           autocomplete="off" @keydown.enter.prevent>
                </div>
            </div>
            <div class="w-1/5 mx-1 inline-block " v-for="(t,index) in newproject.inputs" :key="index">


                    <label for="name" class="uppercase tracking-wide text-gray-700 text-base font-bold">
                        {{__('Input Name')}}
                    </label>

                    <input type="text" class="mb-0 bg-white focus:outline-none focus:ring border border-gray-300 rounded-lg py-2 px-4 block w-full appearance-none leading-normal w-full" v-model="newproject.inputs[index].name">

                    <label class="md:w-2/3 block text-gray-500 font-bold">
                        <input class="mr-2 leading-tight" type="checkbox" v-model="newproject.inputs[index].mandatory">
                        <span class="text-base">
                            {{__('Mandatory')}}
                        </span>
                    </label>


                <div class="w-full">
                    <label class="uppercase tracking-wide text-gray-700 text-base font-bold">Type</label>
                    <div class="relative">
                        <select class="block appearance-none w-full bg-white border border-gray-300 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-gray focus:border-gray-500" v-model="newproject.inputs[index].type">
                            <option v-for="type in newproject.config.available" :value="type">
                                @{{type}}
                            </option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>
                </div>
                    <span v-if="(newproject.inputs[index].type == 'multiple choice' || newproject.inputs[index].type == 'one choice')">
                                            <label class="uppercase tracking-wide text-gray-700 text-base font-bold">{{__('Answers')}}</label>
                                                <div class="block"
                                                     v-for="(m,answerindex) in newproject.inputs[index].answers">
                                                    <input type="text" class="mb-0 bg-white focus:outline-none focus:ring border border-gray-300 rounded-lg py-2 px-4 block w-full appearance-none leading-normal w-full inputcreatecase"
                                                           v-model="newproject.inputs[index].answers[answerindex]"
                                                           @keyup="handleAdditionalInputs(index,answerindex,m)"
                                                           autocomplete="off"
                                                           @keydown.enter.prevent
                                                           >
                                                </div>
						            </span>

            </div>
        </div>

        <div class="block mt-2">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" v-if="newproject.response != ''"
                 v-html="newproject.response">
                <button class="delete"
                        @click.preventdefault="newproject.response = ''"></button>
            </div>
        </div>


        <button class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-black py-2 px-4 border border-blue-500 hover:border-transparent rounded">{{__('Create Project')}}</button>

    </form>

@endsection
