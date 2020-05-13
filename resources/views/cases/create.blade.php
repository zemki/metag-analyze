@extends('layouts.app')

@section('content')

    <h1 class="text-4xl font-bold font-serif block">Create a Case</h1>


    <form method="POST" action="{{url($project->path().'/cases')}}"
          id="addcase" autocomplete="off">
        @csrf

        <label for="name" class="uppercase tracking-wide text-gray-700 text-base font-bold">
            {{__('Case Name')}} *
        </label>
        <input type="text"
               class="bg-white focus:outline-none focus:shadow-outline border border-gray-300 rounded-lg py-2 px-4 block w-full appearance-none leading-normal w-full"
               name="name">
        {!! $errors->first('name', '<p class="has-text-danger">:message</p>') !!}

        <div class="full flex">
            <div class="w-1/3">
                <label for="duration" class="uppercase tracking-wide text-gray-700 text-base font-bold">
                    {{__('Duration')}} *
                </label>
                <input type="text"
                       class="bg-white focus:outline-none focus:shadow-outline border border-gray-300 rounded-lg py-2 px-4 block w-full appearance-none leading-normal "
                       v-model="newcase.duration.input"
                       :disabled="newcase.backendcase">
            </div>
            <div class="w-1/3 flex-shrink-0 ml-2">
                <label for="duration" class="uppercase tracking-wide text-gray-700 text-base font-bold">
                    {{__('Unit')}}
                </label>
                <div class="inline relative w-64">
                    <select class="appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 leading-tight focus:outline-none "
                            v-model="newcase.duration.selectedUnit"
                            :disabled="newcase.backendcase">
                        <option>{{__('Select a value')}}</option>
                        <option value="days">{{__('day(s)')}}</option>
                        <option value="week">{{__('week(s)')}}</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="w-1/4 flex-shrink-0 ml-2">
                <label for="duration" class="uppercase tracking-wide text-gray-700 text-base font-bold">
                    {{__('Estimated end: ')}}
                </label>
                <div v-show="newcase.duration.message != ''"
                     v-html="newcase.duration.message">
                </div>
            </div>

        </div>
        <label class="uppercase tracking-wide text-gray-700 text-xs font-bold block">
            <input type="checkbox" name="loginStart" class="mr-2 leading-tight" checked v-model="newcase.duration.starts_with_login" :disabled="newcase.backendcase">
            {{__('The duration start when the user Log in')}}
        </label>
        <b-field :customclass="'uppercase tracking-wide text-gray-700 text-xs font-bold'" :label="trans('Or it start this day:')+' *'" v-if="!newcase.duration.starts_with_login">
            <b-datepicker
                    :min-date="newcase.minDate"
                    :placeholder="trans('Click to select...')"
                    icon="calendar-today"
                    name="startdate"
                    v-model="newcase.duration.startdate"
            >
            </b-datepicker>
        </b-field>

        <input type="hidden" :value="newcase.duration.value" name="duration">


        <label for="user" class="uppercase tracking-wide text-gray-700 text-base font-bold">
            {{__('User')}} *
        </label>
        <input type="email"
               class="bg-white focus:outline-none focus:shadow-outline border border-gray-300 rounded-lg py-2 px-4 block w-full appearance-none leading-normal"
               :class="newcase.backendcase ? 'disabled cursor-not-allowed bg-gray-700 opacity-50' : 'bg-white focus:outline-none focus:shadow-outline border border-gray-300 rounded-lg py-2 px-4 block w-full appearance-none leading-normal'"
               name="email" list="email" autocomplete="off" required
               :disabled="newcase.backendcase">

        <label class="uppercase tracking-wide text-gray-700 text-xs font-bold block">
            <input type="checkbox" class="mr-2 leading-tight" name="backendCase" v-model="newcase.backendcase" title="{{__('A backend case is filled on Metag Analyze and it\'s not accessible from the mobile app. It doesn\'t have duration because it can be filled and consulted anytime in the backend.')}}">
            {{__('This is a backend case')}}
        </label>

        <p class="mt-3 bg-yellow-500 text-black font-bold p-3">
            {{__('If the user is not registered, he/she will receive an email to set a password.')}}<br>
            {{__('If the user was already registered, he/she will just need to log-in to see the new case.')}}
            <br>
            {{__('Please check beforehand if the user was already assigned to a case with the same email.')}}
            <br>
            {{__('Data for backend cases can only be entered via MeTag Analyze, they are not accessible from the MeTag mobile app. They don’t have a duration setting because they can be created, filled out and consulted at any time in the backend.')}}
        </p>

        <div class="text-base my-3">* {{__('required')}}</div>


        <button class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-black py-2 px-4 border border-blue-500 hover:border-transparent rounded">{{__('Create Case')}}</button>


    </form>

@endsection

@section('pagespecificscripts')
@endsection
