@extends('layouts.app')

@section('content')
@include('layouts.breadcrumb')
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

            <div class="w-full py-2 pl-3 pr-10 mt-1" v-show="newcase.duration.message != ''">
                <div v-html="newcase.duration.message"></div>
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
    <div class="mt-1 sm:mt-0 sm:col-span-2" v-if="!newcase.duration.starts_with_login">
        <label for="backenddate" class="font-medium text-gray-700">{{__('Or it start this day:')}} *</label>
        <input datepicker type="date" v-model="newcase.duration.startdate"
            class="block w-full max-w-lg border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 active:border-blue-500 sm:text-sm "
            placeholder="{{__('Select date')}}" :min="moment().subtract(1,'day').format('YYYY-MM-DD')">
    </div>
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
            <label for="backendCase" class="font-medium text-gray-700">{{__('This is a backend case')}}</label>

        </div>
    </div>

    <div class="relative flex items-start my-2">
        <div class="flex items-center h-5">
            <input title="{{__('Append sequential numbers at the end of the case name.')}}" name="sequentialNumbers"
                type="checkbox" class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500">
        </div>
        <div class="ml-3 text-sm">
            <label for="sequentialNumbers"
                class="font-medium text-gray-700">{{__('Append sequential numbers at the end of the case')}}</label>

        </div>
    </div>

    <div class="relative flex items-start my-2">
        <div class="flex items-center h-5">
            <input v-model="newcase.sendanywayemail" name="sendanywayemail" type="checkbox" name="backendCase"
                class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500">
        </div>
        <div class="ml-3 text-sm">
            <label for="comments"
                class="font-medium text-gray-700">{{__('If the user is already registered, send anyway an email to notify for a new case')}}</label>

        </div>
    </div>
    <div class="relative block my-2" v-if="newcase.sendanywayemail">

        <label for="user"
            class="block text-sm font-medium text-gray-700">{{__("Subject, otherwise will send 'New Case on Metag'")}}
        </label>
        <div class="mt-1">
            <input type="text" name="sendanywayemailsubject" id="sendanywayemailsubject" autocomplete="off"
                class="block w-full px-4 py-2 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring">
        </div>
    </div>

    <div class="relative block my-2" v-if="newcase.sendanywayemail">

        <label for="sendanywayemailmessage"
            class="block text-sm font-medium text-gray-700">{{__("Message, otherwise will send 'You have been added to a new case, please login in Metag to check it out.'")}}
        </label>
        <div class="mt-1">
            <input type="text" name="sendanywayemailmessage" id="sendanywayemailmessage" autocomplete="off"
                class="block w-full px-4 py-2 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring">
        </div>
    </div>

    <div class="mt-6">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <div class="pt-6">
                <div class="flow-root px-6 pb-8 rounded-lg bg-gray-50">
                    <div class="-mt-6">
                        <div>
                            <span class="inline-flex items-center justify-center p-3 bg-blue-500 rounded-md shadow-lg">
                                <!-- Heroicon name: outline/cloud-upload -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </span>
                        </div>
                        <h3 class="mt-8 text-lg font-medium tracking-tight text-gray-900">Users</h3>
                        <p class="mt-5 text-base text-gray-500">
                            {{__('An email will be sent to not registered users.')}}
                        </p>
                        <p class="mt-5 text-base text-gray-500">
                            {{__('If the user is already registered, there is no communication and the user can just login.')}}
                        </p>
                        <p class="mt-5 text-base text-gray-500">
                            {{__('Please check beforehand if the user was already assigned to a case with the same email.')}}
                        </p>
                    </div>
                </div>
            </div>

            <div class="pt-6">
                <div class="flow-root px-6 pb-8 rounded-lg bg-gray-50">
                    <div class="-mt-6">
                        <div>
                            <span class="inline-flex items-center justify-center p-3 bg-blue-500 rounded-md shadow-lg">
                                <!-- Heroicon name: outline/lock-closed -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </span>
                        </div>
                        <h3 class="mt-8 text-lg font-medium tracking-tight text-gray-900">{{__('Multiple Invites')}}
                        </h3>
                        <p class="mt-5 text-base text-gray-500">
                            {{__('Use , or ; or space (comma, semicolon, empty space) to delimit the email and invite multiple people using the same case name')}}
                        </p>
                    </div>
                </div>
            </div>

            <div class="pt-6">
                <div class="flow-root px-6 pb-8 rounded-lg bg-gray-50">
                    <div class="-mt-6">
                        <div>
                            <span class="inline-flex items-center justify-center p-3 bg-blue-500 rounded-md shadow-lg">
                                <!-- Heroicon name: outline/refresh -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                </svg>
                            </span>
                        </div>
                        <h3 class="mt-8 text-lg font-medium tracking-tight text-gray-900">{{__('Backend Cases')}}</h3>
                        <p class="mt-5 text-base text-gray-500">
                            {{__('Data for backend cases can only be entered via MeTag Analyze, they are not accessible from the MeTag mobile app. They don’t have a duration setting because they can be created, filled out and consulted at any time in the backend.')}}
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>



    <div class="my-2 text-base">* {{__('required')}}</div>

    <button
        class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">{{__('Create Case')}}</button>

    <div class="block mt-2">
        <div class="relative px-4 py-3 text-red-700 bg-red-100 border border-red-400 rounded"
            v-if="newcase.response != ''">
            <div v-html="newcase.response"></div>
            <button class="delete absolute top-2 right-2" @click.prevent="newcase.response = ''"></button>
        </div>
    </div>
</form>

@endsection

@section('pagespecificscripts')
@endsection
