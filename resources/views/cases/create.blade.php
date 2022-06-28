@extends('layouts.app')

@section('content')

<h1 class="block font-serif text-4xl font-bold">{{__('Create a Case')}}</h1>


<form method="POST" class="mx-auto" action="{{url($project->path().'/cases')}}" id="addcase" autocomplete="off"
    @submit="validateCase">
    @csrf
    <div class="block">
        <label for="name" class="text-base font-bold tracking-wide text-gray-700 uppercase">
            {{__('Case Name')}} *
        </label>
        <input type="text"
            class="block w-full px-4 py-2 mb-0 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring"
            name="name" v-model="newcase.name">
        <span
            :class="newcase.inputLength.name <= newcase.name.length ? 'text-red-600 text-xs w-auto inline-flex float-right' : 'text-xs text-gray-500 w-auto inline-flex float-right'">@{{newcase.inputLength.name - newcase.name.length}}
            / @{{newcase.inputLength.name}}</span>

    </div>

    {!! $errors->first('name', '<p class="has-text-danger">:message</p>') !!}

    <div class="flex full">
        <div class="w-1/3">
            <label for="duration" class="text-base font-bold tracking-wide text-gray-700 uppercase">
                {{__('Duration')}} *
            </label>
            <input type="text"
                class="block w-full px-4 py-2 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring "
                v-model="newcase.duration.input" required :disabled="newcase.backendcase">
        </div>
        <div class="flex-shrink-0 w-1/3 ml-2">
            <label for="duration" class="text-base font-bold tracking-wide text-gray-700 uppercase">
                {{__('Unit')}}
            </label>
            <div class="relative inline w-64">
                <select
                    class="w-full px-4 py-2 leading-tight bg-white border border-gray-400 appearance-none hover:border-gray-500 focus:outline-none "
                    v-model="newcase.duration.selectedUnit" :disabled="newcase.backendcase">
                    <option>{{__('Select a value')}}</option>
                    <option value="days">{{__('day(s)')}}</option>
                    <option value="week">{{__('week(s)')}}</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 pointer-events-none">
                    <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="flex-shrink-0 w-1/4 ml-2">
            <label for="duration" class="text-base font-bold tracking-wide text-gray-700 uppercase">
                {{__('Estimated end: ')}}
            </label>
            <div v-show="newcase.duration.message != ''" v-html="newcase.duration.message">
            </div>
        </div>

    </div>
    <label class="block text-xs font-bold tracking-wide text-gray-700 uppercase">
        <input type="checkbox" name="loginStart" class="mr-2 leading-tight" checked
            v-model="newcase.duration.starts_with_login" :disabled="newcase.backendcase">
        {{__('The duration start when the user Log in')}}
    </label>
    <b-field :customclass="'uppercase tracking-wide text-gray-700 text-xs font-bold'"
        :label="trans('Or it start this day:')+' *'" v-if="!newcase.duration.starts_with_login">
        <b-datepicker :min-date="newcase.minDate" :placeholder="trans('Click to select...')" icon="calendar-today"
            name="startdate" v-model="newcase.duration.startdate">
        </b-datepicker>
    </b-field>

    <input type="hidden" :value="newcase.duration.value" name="duration">


    <label for="user" class="text-base font-bold tracking-wide text-gray-700 uppercase">
        {{__('User')}} *
    </label>
    <input type="email"
        class="block w-full px-4 py-2 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring"
        :class="newcase.backendcase ? 'disabled cursor-not-allowed bg-gray-700 opacity-50' : 'bg-white focus:outline-none focus:ring border border-gray-300 rounded-lg py-2 px-4 block w-full appearance-none leading-normal'"
        name="email" list="email" autocomplete="off" required :disabled="newcase.backendcase">

    <label class="block text-xs font-bold tracking-wide text-gray-700 uppercase">
        <input type="checkbox" class="mr-2 leading-tight" name="backendCase" v-model="newcase.backendcase"
            title="{{__('A backend case is filled on Metag Analyze and it\'s not accessible from the mobile app. It doesn\'t have duration because it can be filled and consulted anytime in the backend.')}}">
        {{__('This is a backend case')}}
    </label>

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
        class="px-4 py-2 font-semibold text-blue-700 bg-transparent border border-blue-500 rounded hover:bg-blue-500 hover:text-black hover:border-transparent">{{__('Create Case')}}</button>

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