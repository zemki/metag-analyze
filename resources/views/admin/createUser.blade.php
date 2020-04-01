@extends('layouts.app')

@section('content')

    <div class="columns">
        <div class="column">

            <h1 class="title">Create a User and assign it to a case</h1>
            <p class="subtitle text-sm">The duration start from the moment <strong>when the user log in in the
                    app.</strong> <br>
                The user field define the login username in the app.<br>
                We advice to create the user with a prefix, referring the project. (example: P12_user0)

            </p>

            @if($errors->any())
                <h4>{{$errors->first()}}</h4>
            @endif
            <form method="POST" action="{{route('users')}}" class="" style="padding-top: 40px" id="adduser"
                  autocomplete="off">
                @csrf
                <div class="field">
                    <div class="control">
                        <label for="duration" class="label">
                            User
                        </label>
                        <div class="columns">
                            <div class="column">
                                <input type="text" class="input" name="email" list="email" autocomplete="off"
                                       v-model="newuser.email">
                                <p v-html="newuser.emailexistmessage" class="notification is-warning is-small mt-3"
                                   v-if="newuser.emailexistmessage.length > 0 && newuser.email.length > 3"></p>
                            </div>
                            <div class="column">
                                <div class="select">
                                    <select v-model="newuser.role" name="role">
                                        <option>Select a value</option>
                                        <option value="1">Admin</option>
                                        <option value="2">Researcher</option>
                                        <option value="3">User</option>
                                    </select>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="field">
                    <div class="control">
                        <label class="checkbox">
                            <input type="checkbox" name="assignToCase" v-model="newuser.assignToCase">
                            Assign To a Case
                        </label>
                    </div>
                </div>
                <span v-if="newuser.assignToCase">
					<div class="field">
											<label for="project" class="label">
						Project
					</label>
						<div class="select">
								<select v-model="newuser.case.project" name="project">
									<option>Select a value</option>
									@foreach($projects as $project)
                                        <option value="{{$project->id}}">{{$project->name}}</option>
                                    @endforeach
								</select>
							</div>
					</div>
				<div class="field">
					<label for="name" class="label">
						Case Name
					</label>
					<div class="control">
						<input type="text" class="input" name="caseName" v-model="newuser.case.name">
						<p v-html="newuser.case.caseexistmessage" class="notification is-warning is-small mt-3"
                           v-if="newuser.case.caseexistmessage.length > 0"></p>
						{!! $errors->first('name', '<p class="has-text-danger">:message</p>') !!}
					</div>
				</div>
				<div class="field">
					<label for="duration" class="label">
						Duration
					</label>
					<div class="columns">
						<div class="column">
							<div class="control">
								<input type="text" class="input" v-model="newuser.case.duration.input">
							</div>
						</div>
						<div class="column">
							<div class="select">
								<select v-model="newuser.case.duration.selectedUnit">
									<option>Select a value</option>
									<option value="days">day(s)</option>
									<option value="week">week(s)</option>
								</select>
							</div>
						</div>
						<div class="column" v-show="newuser.case.duration.message != ''"
                             v-html="'Estimated end (if the user log-in today): ' +newuser.case.duration.message">

						</div>
					</div>
				</div>

				<input type="hidden" :value="newuser.case.duration.value" name="duration">
					</span>
                <div class="field">
                    <div class="control">
                        <button class="button is-link" :opacity-75="newuser.email.length < 4 || newuser.case.caseexist">
                            Create User
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection

@section('pagespecificscripts')
@endsection



