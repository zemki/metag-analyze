@extends('layouts.app')

@section('content')

<h1 class="mb-4 text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight lg:text-6xl">{{__('Create a Case')}}
</h1>


<form method="POST" class="" action="{{url($project->path().'/cases')}}" id="addcase" autocomplete="off"
    @submit="validateCase">
    @csrf
    <div class="my-2">
        <label for="name" class="block text-sm font-medium text-gray-700">{{__('Case Name')}} *</label>
        <div class="mt-2">
            <input type="text" name="name" id="name" v-model="newcase.name" value="{{ old('name') }}"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            <span
                :class="newcase.inputLength.name <= newcase.name.length ? 'text-red-600 text-xs w-auto inline-flex float-right' : 'text-xs text-gray-500 w-auto inline-flex float-right'">@{{newcase.inputLength.name - newcase.name.length}}
                / @{{newcase.inputLength.name}}</span>
        </div>
    </div>



    {!! $errors->first('name','<div class="p-4 rounded-md bg-red-50">
        <div class="flex w-64 mx-auto ">
            <div class="flex-shrink-0">
                <!-- Heroicon name: solid/times-circle -->
                <svg class="w-5 h-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414-1.414L4 10l3.293 3.293a1 1 0 101.414-1.414L8 10l-1.293-1.293z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="flex-shrink-0 ml-3">
                <p class="text-sm font-medium text-red-800">:message
                </p>
            </div>
        </div>
    </div>') !!}

    <div class="flex full">
        <div class="w-1/3">
            <label for="duration" class="block text-sm font-medium text-gray-700">{{__('Duration')}} *</label>
            <div class="mt-1">
                <input type="text" name="duration" id="duration" required :disabled="newcase.backendcase"
                    value="{{ old('duration') }}" v-model="newcase.duration.input"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    :class="newcase.backendcase ? 'disabled cursor-not-allowed bg-gray-500 opacity-50' : 'bg-white focus:outline-none focus:ring border border-gray-300 rounded-lg py-2 px-4 block w-full appearance-none'">
            </div>
        </div>
        <div class="flex-shrink-0 w-1/3 ml-2 ">
            <div>
                <label for="duration" class="block text-sm font-medium text-gray-700">{{__('Unit')}}</label>
                <select id="duration" name="duration" :disabled="newcase.backendcase"
                    v-model="newcase.duration.selectedUnit"
                    class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option>{{__('Select a value')}}</option>
                    <option value="days">{{__('day(s)')}}</option>
                    <option value="week">{{__('week(s)')}}</option>
                </select>
            </div>

        </div>

        <div class="flex-shrink-0 w-1/4 ml-2">

            <label for="duration" class="block text-sm font-medium text-gray-700">{{__('Estimated end: ')}}</label>

            <div class="w-full py-2 pl-3 pr-10 mt-1" v-show="newcase.duration.message != ''"
                v-html="newcase.duration.message">

            </div>
        </div>

    </div>
    <div class="relative flex items-start my-2">
        <div class="flex items-center h-5">
            <input v-model="newcase.duration.starts_with_login" :disabled="newcase.backendcase" name="loginStart"
                type="checkbox" checked class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500">
        </div>
        <div class="ml-3 text-sm">
            <label for="comments"
                class="font-medium text-gray-700">{{__('The duration start when the user Log in')}}</label>

        </div>
    </div>
    <template v-if="!newcase.duration.starts_with_login">
        <label for="comments" class="font-medium text-gray-700">{{__('Or it start this day:')}} *</label>
        <t-datepicker v-model="newcase.duration.startdate" :disabled-dates="disabledDates" />
    </template>
    {{-- <b-field :customclass="'uppercase tracking-wide text-gray-700 text-xs font-bold'"
        :label="trans('Or it start this day:')+' *'" v-if="!newcase.duration.starts_with_login">
        <b-datepicker :min-date="newcase.minDate" :placeholder="trans('Click to select...')" icon="calendar-today"
            name="startdate" v-model="newcase.duration.startdate">
        </b-datepicker>
    </b-field> --}}
    <input type="hidden" :value="newcase.duration.value" name="duration">
    <div class="relative block my-2">

        <label for="user" class="block text-sm font-medium text-gray-700">{{__('User(s)')}} *</label>
        <div class="mt-1">
            <input type="text" name="email" id="email" required :disabled="newcase.backendcase" autocomplete="off"
                value="{{ old('email') }}"
                class="block w-full px-4 py-2 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring"
                :class="newcase.backendcase ? 'disabled cursor-not-allowed bg-gray-500 opacity-50' : 'bg-white focus:outline-none focus:ring border border-gray-300 rounded-lg py-2 px-4 block w-full appearance-none leading-normal'">
        </div>
    </div>

    <div class="relative flex items-start my-2">
        <div class="flex items-center h-5">
            <input v-model="newcase.backendcase"
                title="{{__('A backend case is filled on Metag Analyze and it\'s not accessible from the mobile app. It doesn\'t have duration because it can be filled and consulted anytime in the backend.')}}"
                name="backendCase" type="checkbox" checked
                class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500">
        </div>
        <div class="ml-3 text-sm">
            <label for="comments" class="font-medium text-gray-700">{{__('This is a backend case')}}</label>

        </div>
    </div>



    <p class="p-3 mt-3 font-bold text-black bg-yellow-500">
        {{__('If the user is not registered, he/she will receive an email to set a password.')}}<br>
        {{__('If the user was already registered, he/she will just need to log-in to see the new case.')}}
        <br>
        {{__('Please check beforehand if the user was already assigned to a case with the same email.')}}
        <br>
        {{__('Data for backend cases can only be entered via MeTag Analyze, they are not accessible from the MeTag mobile app. They don’t have a duration setting because they can be created, filled out and consulted at any time in the backend.')}}
    </p>

    <div class="my-2 text-base">* {{__('required')}}</div>

    <button
        class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">{{__('Create Case')}}</button>

    <div class="block mt-2">
        <div class="relative px-4 py-3 text-red-700 bg-red-100 border border-red-400 rounded"
            v-if="newcase.response != ''" v-html="newcase.response">
            <button class="delete" @click.preventdefault="newcase.response = ''"></button>
        </div>
    </div>
</form>

@endsection

@section('pagespecificscripts')
@endsection