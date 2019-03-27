@extends('layouts.app')

@section('content')
<div class="columns">
	<div class="column is-half">
		<nav class="breadcrumb has-succeeds-separator is-small" aria-label="breadcrumbs">
			<ul>
				<li><a href="#">Metag</a></li>
				<li ><a href="{{url('/')}}">Projects</a></li>
				<li ><a href="{{url($project->path())}}">{{$project->name}}</a></li>
				<li class="is-active" aria-current="page"><a href="#">Create Case</a></li>
			</ul>
		</nav>
	</div>
</div>

<div class="columns">
	<div class="column">
		<h1>Create a Case</h1>
		<form method="POST" action="{{$project->path().'/cases'}}" class="" style="padding-top: 40px" @submit="submitCaseForm">
			@csrf
			<input type="hidden" :value="newcase.formattedinputstring" name="inputs">
			<div class="field">
				<label for="name" class="label">
					Case Name
				</label>
				<div class="control">
					<input type="text" class="input" name="name" v-model="newcase.name">
					{!! $errors->first('name', '<p class="has-text-danger">:message</p>') !!}

				</div>
			</div>

			<div class="field">
				<label for="ninputs" class="label">
					Number of inputs
				</label>
				<div class="control">
					<input type="number" class="input" id="ninputs" min="0" max="10" value="1" v-model.number="newcase.ninputs">
				</div>
			</div>
			<div class="columns is-multiline is-mobile">
				<div class="inputs" v-for="(t,index) in newcase.inputs" :key="index">
					<div class="column">
						<div class="field">
							<label for="name" class="label">
								Input Name
							</label>
							<div class="control">
								<input type="text" class="input" v-model="newcase.inputs[index].name">
							</div>
						</div>
					</div>
					<div class="column">
						<div class="field">
							<label class="label">Type</label>
							<div class="control">
								<div class="select">
									<select v-model="newcase.inputs[index].type">
										<option v-for="type in newcase.config.available" :value="type">@{{type}}</option>
									</select>
								</div>
							</div>
						</div>
						<span v-if="(newcase.inputs[index].type == 'multiple choice' || newcase.inputs[index].type == 'one choice')">
							<div class="field">
								<label class="label">Number of Answers</label>
								<div class="control">
									<input v-model.number="newcase.inputs[index].numberofanswer" class="input" type="number" placeholder="">
								</div>
							</div>
							<div class="field" v-for="na in newcase.inputs[index].numberofanswer">
								<label class="label">Answers</label>
								<div class="control" >
									<input v-model="newcase.inputs[index].answers[na-1]" class="input" type="text" placeholder="">
								</div>
							</div>
						</span>
					</div>
				</span>
			</div>

		</div>
    <div class="level">
        <div class="columns">
            <div class="column">
                <div class="notification is-danger" v-if="newcase.response != ''" v-html="newcase.response">
                    <button class="delete" @click.preventdefault="newcase.response = ''"></button>
                </div>
            </div>
        </div>
    </div>

		<div class="field">
			<div class="control">
				<button class="button is-link" >Create Case</button>
			</div>
		</div>



	</form>
</div>
</div>
@endsection

@section('pagespecificscripts')
@endsection
