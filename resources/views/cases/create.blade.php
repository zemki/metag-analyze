@extends('layouts.app')

@section('content')

    <div class="columns">
        <div class="column">

            <h1 class="title">Create a Case</h1>
            <p class="subtitle text-sm mt-1"> {{__('The duration start from the moment when the user log in in the app.')}}<br>
                {{__('The user field define the login email in the app.')}}<br>
            </p>


            <form method="POST" action="{{url($project->path().'/cases')}}" class="" style="padding-top: 40px"
                  id="addcase" autocomplete="off">
                @csrf
                <div class="field">
                    <label for="name" class="label">
                        {{__('Case Name')}}
                    </label>
                    <div class="control">
                        <input type="text" class="input" name="name">
                        {!! $errors->first('name', '<p class="has-text-danger">:message</p>') !!}

                    </div>
                </div>
                <div class="field">
                    <label for="duration" class="label">
                        {{__('Duration')}}
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
                <b-field :label="trans('Or it start this day:')" v-if="!newcase.duration.starts_with_login">
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
                        <label for="duration" class="label">
                            {{__('User')}}
                        </label>
                        <input type="email" class="input" name="email" list="email" autocomplete="off" required>
                        <p class="mt-3 bg-yellow-500 text-black font-bold p-3">
                            {{__('If the user is not registered, he/she will receive an email to set a password.')}}<br>
                            {{__('If the user was already registered, he/she will just need to log-in to see the new case.')}}<br>
                            {{__('Please check beforehand if the user was already assigned to a case with the same email.')}}</p>
                    </div>
                </div>
                <div class="field">
                    <div class="control">
                        <button class="button is-link">{{__('Create Case')}}</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection

@section('pagespecificscripts')
@endsection
