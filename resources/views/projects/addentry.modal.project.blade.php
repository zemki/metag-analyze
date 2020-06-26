@extends('projects.show')

@section('content')
@parent


    <!--Modal-->
    <div class="modal opacity-0 pointer-events-none fixed w-full h-auto top-0 left-0 flex items-center justify-center"
         v-show="newentry.modal">
        <div class="absolute w-full h-full bg-gray-900 opacity-50" @click="toggleModal()"></div>

        <div class="modal-container bg-white w-full md:max-w-md mx-auto rounded shadow-lg z-50 overflow-y-auto">

            <div @click="toggleModal()"
                 class="absolute top-0 right-0 cursor-pointer flex flex-col items-center mt-4 mr-4 text-white text-sm z-50">
                <svg class="fill-current text-white" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                     viewBox="0 0 18 18">
                    <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"></path>
                </svg>
                <span class="text-sm">(Esc)</span>
            </div>

            <!-- Add margin if you want to see some of the overlay behind the modal-->
            <div class="modal-content py-4 text-left px-6 h-auto w-auto">
                <!--Title-->
                <div class="flex justify-between items-center pb-3">
                    <p class="text-2xl font-bold">{{__('Add Entry')}}</p>
                    <div @click="toggleModal()" class="cursor-pointer z-50">
                        <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                             viewBox="0 0 18 18">
                            <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"></path>
                        </svg>
                    </div>
                </div>
                <p class="mt-3 bg-yellow-500 text-black font-bold p-3 text-center">
                    {{__('Please scroll if you don\'t see all the content.')}}
                    {{__('To see the changes remember to reload the page.')}}
                </p>

                <!--Body-->
                <input type="hidden" :value="newentry.case_id"/>
                <div class="my-2">
                    <label class="uppercase tracking-wide text-gray-700 text-base font-bold">
                        Start Date/time *
                    </label>
                    <b-datetimepicker
                            :placeholder="trans('Click to select...')"
                            icon="calendar-today"
                            name="begin"
                            v-model="newentry.data.start"
                    >
                    </b-datetimepicker>
                </div>
                <div class="my-2">

                    <label class="uppercase tracking-wide text-gray-700 text-base font-bold">
                        End Date/time *
                    </label>
                    <b-datetimepicker
                            :placeholder="trans('Click to select...')"
                            icon="calendar-today"
                            name="end"
                            v-model="newentry.data.end"
                    >
                    </b-datetimepicker>
                </div>
                <div class="my-2">
                    <label class="uppercase tracking-wide text-gray-700 text-base font-bold">
                        Media *
                    </label>
                    <input type="text" name="media_id"
                           v-model="newentry.data.media_id"
                           class="bg-white focus:outline-none focus:shadow-outline border border-gray-300 rounded-lg py-2 px-4 block w-full appearance-none leading-normal"></input>

                </div>
                <h1 class="text-2xl font-bold text-center my-4 uppercase tracking-wide text-gray-700">Inputs</h1>
                <div v-for="(value) in newentry.inputs">
                    <label class="uppercase tracking-wide text-gray-700 text-base font-bold my-2" v-text="value.mandatory ? value.name +' *' : value.name">

                    </label>
                    <input type="text" v-if="value.type === 'text'" :name="'text'+value.name"
                           v-model="newentry.data.inputs[value.name]"
                           class="bg-white focus:outline-none focus:shadow-outline border border-gray-300 rounded-lg py-2 px-4 block w-full appearance-none leading-normal "></input>
                    <b-taginput
                            :data=value.answers
                            autocomplete
                            size="is-medium"
                            open-on-focus
                            v-if="value.type === 'multiple choice'"
                            v-model="newentry.data.inputs[value.name]"
                    >
                    </b-taginput>
                    <div class="relative"  v-if="value.type === 'one choice'">
                        <select
                                v-model="newentry.data.inputs[value.name]"
                                class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500" >
                            <option v-for="answer in value.answers" :value="answer">@{{answer}}</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>

                    <div class="relative"  v-if="value.type === 'scale'">
                        <select
                                v-model="newentry.data.inputs[value.name]"
                                class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500" >
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>

                </div>
                <div class="text-base my-3">* {{__('required')}}</div>


                <!--Footer-->
                <div class="flex justify-end pt-2">
                    <button class="px-4 bg-transparent p-3 rounded-lg text-blue-500 hover:bg-gray-100 hover:text-blue-400 mr-2"
                            @click="entrySaveAndClose()">{{__('Save and Close')}}</button>
                    <button class="px-4 bg-transparent p-3 rounded-lg text-blue-500 hover:bg-gray-100 hover:text-blue-400 mr-2"
                            @click="entrySaveAndNewEntry()">{{__('Save and add new Entry')}}</button>
                    <button class="px-4 bg-blue-500 p-3 rounded-lg text-white hover:bg-blue-400"
                            @click="toggleModal()">{{__('Close')}}</button>
                </div>

            </div>
        </div>
    </div>
    <!--End Modal-->

@endsection
