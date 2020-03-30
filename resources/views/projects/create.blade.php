@extends('layouts.app')

@section('content')

    <div class="columns">

        <div class="level">
            <div class="columns">
                <div class="column">
                    <div class="container">
                        <h1 class="title">Create a Project</h1>
                        <p class="subtitle text-sm">{{__('The predefined inputs are Begin Date/Time,
                            End Date/time and
                            Media  used.
                            You can enter up to 3 additional inputs giving them name and details,
                            this will be reflected in the mobile app.')}}
                        </p>
                    </div>
                    <form method="POST" action="{{route('projects')}}" class="container" style="padding-top: 40px"
                          @submit="validateProject">
                        @csrf
                        <input type="hidden" value="{{auth()->user()->id}}" name="created_by">
                        <div class="field">
                            <label for="name" class="label">
                                {{__('Title')}}
                            </label>
                            <div class="control">
                                <input type="text" class="input" name="name" v-model="newproject.name">
                            </div>
                        </div>

                        <div class="field">
                            <label for="description" class="label">
                                {{__('Description')}}
                            </label>

                            <div class="control">
								<textarea name="description" id="textarea" class="textarea"
                                          v-model="newproject.description"></textarea>
                            </div>
                        </div>


                        <input type="hidden" :value="JSON.stringify(newproject.inputs)" name="inputs">
                        <div class="field">
                            <div class="control">
                                <b-field label="Number of additional inputs">
                                    <b-numberinput name="ninputs" id="ninputs" controls-position="compact"
                                                   type="is-light" min="0" max="3" :editable="false" steps="1"
                                                   v-model.number="newproject.ninputs"></b-numberinput>
                                </b-field>
                            </div>
                        </div>
                        <div class="columns is-multiline">
                            <div class="column is-3">
                                <label for="media" class="label inline-flex">
                                    Media
                                </label>
                                <p class="subtitle text-sm">
                                    {{__('The user will be able to enter their own media, here you can write e predefined list from which it\'s possible to chose a media.')}}
                                </p>
                                <div class="control" v-for="(m,index) in newproject.media">
                                    <input type="text" name="media[]" class="input inputcreatecase" :tabindex="index+1"
                                           v-model="newproject.media[index]" @keyup.capture="handleMediaInputs(index,m)"
                                           autocomplete="off" @keydown.enter.prevent @keydown.shift.prevent>
                                </div>
                            </div>
                            <div class="inputs" v-for="(t,index) in newproject.inputs" :key="index">
                                <div class="column">
                                    <div class="field">
                                        <label for="name" class="label">
                                            {{__('Input Name')}}
                                        </label>
                                        <div class="control">
                                            <input type="text" class="input" v-model="newproject.inputs[index].name">
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="checkbox">
                                            <input type="checkbox" v-model="newproject.inputs[index].mandatory"
                                                   checked="checked">
                                            {{__('Mandatory')}}
                                        </label>
                                    </div>
                                </div>
                                <div class="column">
                                    <div class="field">
                                        <label class="label">Type</label>
                                        <div class="control">
                                            <div class="select">
                                                <select v-model="newproject.inputs[index].type">
                                                    <option v-for="type in newproject.config.available" :value="type">
                                                        @{{type}}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <span v-if="(newproject.inputs[index].type == 'multiple choice' || newproject.inputs[index].type == 'one choice')">
                                        <div class="field">
                                            <label class="label">{{__('Answers')}}</label>
                                                <div class="control"
                                                     v-for="(m,answerindex) in newproject.inputs[index].answers">
                                                    <input type="text" class="input inputcreatecase"
                                                           v-model="newproject.inputs[index].answers[answerindex]"
                                                           @keyup="handleAdditionalInputs(index,answerindex,m)"
                                                           autocomplete="off"
                                                           @keydown.enter.prevent
                                                           @keydown.shift.prevent>
                                                </div>
                                        </div>
						            </span>
                                </div>
                            </div>
                        </div>

                        <div class="columns is-multiline is-mobile">
                            <div class="level">
                                <div class="columns">
                                    <div class="column">
                                        <div class="notification is-danger" v-if="newproject.response != ''"
                                             v-html="newproject.response">
                                            <button class="delete"
                                                    @click.preventdefault="newproject.response = ''"></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="level">
                            <div class="field">
                                <div class="control">
                                    <button class="button is-link">{{__('Create Project')}}</button>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

        </div>
@endsection
