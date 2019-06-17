@extends('layouts.app')

@section('content')
    <div class="columns">
        <div class="column is-half">
            <nav class="breadcrumb has-succeeds-separator is-small" aria-label="breadcrumbs">
                <ul>
                    <li><a href="#">Metag</a></li>
                    <li ><a href="{{url('/')}}">Projects</a></li>
                    <li class="is-active" aria-current="page"><a href="#">Create</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <h1>Create a Project</h1>
    <div class="columns">
        <div class="level">
            <form method="POST" action="/projects" class="container" style="padding-top: 40px">
                @csrf
                <input type="hidden" value="{{auth()->user()->id}}" name="created_by">
                <div class="field">
                    <label for="name" class="label">
                        Title
                    </label>
                    <div class="control">
                        <input type="text" class="input" name="name">
                    </div>
                </div>

                <div class="field">
                    <label for="description" class="label">
                        Description
                    </label>

                    <div class="control">
                        <textarea name="description" id="textarea" class="textarea"></textarea>
                    </div>
                </div>

                <div class="field">
                    <div class="control">
                        <label class="checkbox">
                            <input type="checkbox" name="is_locked">
                            lock project
                        </label>
                    </div>
                </div>


                <input type="hidden" :value="JSON.stringify(newproject.inputs)" name="inputs">

                <div class="field">
                    <label for="ninputs" class="label">
                        Number of inputs
                    </label>
                    <div class="control">
                        <input type="number" class="input" id="ninputs" min="0" max="10" value="1" v-model.number="newproject.ninputs">
                    </div>
                </div>
                <div class="columns">
                    <div class="column ">
                                <label for="media" class="label" style="display: inline-flex;">
                                    Media
                                </label>
                        <div class="control" v-for="(m,index) in newproject.media">
                            <input type="text" name="media[]" class="input" v-model="newproject.media[index]" @keyup="handleMediaInputs(index,m)" autocomplete="off"  @keydown.enter.prevent @keydown.tab.prevent>
                        </div>

                    </div>
                    <div class="column ">
                                <label for="media" class="label" style="display: inline-flex;">
                                    Places
                                </label>
                        <div class="control" v-for="(m,index) in newproject.places">
                            <input type="text" name="places[]" class="input" v-model="newproject.places[index]" @keyup="handlePlacesInputs(index,m)" autocomplete="off"  @keydown.enter.prevent @keydown.tab.prevent>
                        </div>

                    </div>
                    <div class="column ">
                                <label for="media" class="label" style="display: inline-flex;">
                                    Communication Partners
                                </label>
                        <div class="control" v-for="(m,index) in newproject.cp">
                            <input type="text" name="cp[]" class="input" v-model="newproject.cp[index]" @keyup="handleCommunicationPartnerInputs(index,m)" autocomplete="off"  @keydown.enter.prevent @keydown.tab.prevent>
                        </div>

                    </div>

                </div>


                <div class="columns is-multiline is-mobile">
                    <div class="inputs" v-for="(t,index) in newproject.inputs" :key="index">
                        <div class="column">
                            <div class="field">
                                <label for="name" class="label">
                                    Input Name
                                </label>
                                <div class="control">
                                    <input type="text" class="input" v-model="newproject.inputs[index].name">
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field">
                                <label class="checkbox">
                                    <input type="checkbox" v-model="newproject.inputs[index].mandatory" checked="checked">
                                    Mandatory
                                </label>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field">
                                <label class="label">Type</label>
                                <div class="control">
                                    <div class="select">
                                        <select v-model="newproject.inputs[index].type">
                                            <option v-for="type in newproject.config.available" :value="type">@{{type}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <span v-if="(newproject.inputs[index].type == 'multiple choice' || newproject.inputs[index].type == 'one choice')">
							<div class="field">
								<label class="label">Number of Answers</label>
								<div class="control">
									<input v-model.number="newproject.inputs[index].numberofanswer" class="input" type="number" placeholder="">
								</div>
							</div>
							<div class="field" v-for="na in newproject.inputs[index].numberofanswer">
								<label class="label">Answers</label>
								<div class="control" >
									<input v-model="newproject.inputs[index].answers[na-1]" class="input" type="text" placeholder="">
								</div>
							</div>
						</span>
                        </div>
                        </span>
                    </div>
                    <div class="level">
                        <div class="columns">
                            <div class="column">
                                <div class="notification is-danger" v-if="newproject.response != ''" v-html="newproject.response">
                                    <button class="delete" @click.preventdefault="newproject.response = ''"></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="level">
                    <div class="field">
                        <div class="control">
                            <button class="button is-link">Create Project</button>
                        </div>
                    </div>

                </div>





            </form>
        </div>
        @endsection

        @section('pagespecificscripts')
            <script type="text/javascript">
                function selectAllHandler(id,c) {
                    var items = document.getElementsByName(id);
                    var element = document.getElementById(id+"i");
                    console.log(id);
                    console.log(element.checked);
                    if(!element.checked){
                        for (var i = 0; i < items.length; i++) {
                            if (items[i].type == 'checkbox')
                                items[i].checked = false;
                        }
                    }else{
                        for (var i = 0; i < items.length; i++) {
                            if (items[i].type == 'checkbox'){
                                items[i].checked = true;
                            }
                        }
                    }

                }

                function UnSelectAll() {

                }
            </script>
@endsection
