@extends('layouts.app')

@section('content')

    <h1 class="text-4xl font-bold font-serif">Create a Case</h1>
    <p class="subtitle text-sm mt-1">
        {{__('The user field define the login email in the app.')}}<br>
    </p>


    <form method="POST" action="{{url($project->path().'/cases')}}"
          id="addcase" autocomplete="off">
        @csrf
        <div class="field">
            <label for="name" class="label">
                {{__('Case Name')}} *
            </label>
            <div class="control">
                <input type="text" class="input" name="name">
                {!! $errors->first('name', '<p class="has-text-danger">:message</p>') !!}

            </div>
        </div>
        <div class="field">
            <label for="duration" class="label">
                {{__('Duration')}} *
            </label>
            <div class="columns">
                <div class="column">
                    <div class="control">
                        <input type="text" class="input" v-model="newcase.duration.input">
                    </div>
                </div>
                <div class="column">
                    <div class="select">
                        <select v-model="newcase.duration.selectedUnit">
                            <option>{{__('Select a value')}}</option>
                            <option value="days">{{__('day(s)')}}</option>
                            <option value="week">{{__('week(s)')}}</option>
                        </select>
                    </div>
                </div>
                <div class="column" v-show="newcase.duration.message != ''"
                     v-html="'Estimated end: ' +newcase.duration.message">

                </div>
            </div>
        </div>

        <label class="checkbox">
            <input type="checkbox" name="loginStart" checked v-model="newcase.duration.starts_with_login">
            {{__('The duration start when the user Log in')}}
        </label>
        <b-field :label="trans('Or it start this day:')+' *'" v-if="!newcase.duration.starts_with_login">
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

        <div class="field">
            <div class="control">
                <label for="user" class="label">
                    {{__('User')}} *
                </label>
                <input type="email" class="input" name="email" list="email" autocomplete="off" required>
                <p class="mt-3 bg-yellow-500 text-black font-bold p-3">
                    {{__('If the user is not registered, he/she will receive an email to set a password.')}}<br>
                    {{__('If the user was already registered, he/she will just need to log-in to see the new case.')}}
                    <br>
                    {{__('Please check beforehand if the user was already assigned to a case with the same email.')}}
                </p>
            </div>
        </div>
        <div class="text-xs my-3">* {{__('required')}}</div>

        <button class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-black py-2 px-4 border border-blue-500 hover:border-transparent rounded">{{__('Create Case')}}</button>


    </form>

@endsection

@section('pagespecificscripts')
@endsection
