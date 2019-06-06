<template>

    <div class="content">
        <label class="checkbox">
          <input type="checkbox" v-model="thisproject.edit">
          Edit name and description
      </label>
      <div class="level">
        <input type="hidden" :value="project.id" name="id">
        <div class="level-left">
            <h1 v-if="!thisproject.edit" v-html="thisproject.name"></h1>
            <div class="field has-addons" v-if="thisproject.edit"><input name="name" v-model="thisproject.name" class="input text is-large">
                <div class="control">
                </div>
            </div>
        </div>
        <div class="level-right">
            <div class="field">
            </div>
        </div>
    </div>
    <div class="level">
        <input type="hidden" :value="project.id" name="id">
        <div class="level-left">
            <p v-if="!thisproject.edit" v-html="thisproject.description"></p>
            <div class="field has-addons" v-if="thisproject.edit">
                <textarea name="description" v-model="thisproject.description" class="input textarea"></textarea>
                <div class="control">
                </div>
            </div>
        </div>
        <div class="level-right">

        </div>
    </div>

    <h2>Inputs</h2>


    <div class="columns is-multiline is-mobile">

        <div class="inputs column is-4" v-for="(c,index) in project.inputs" :key="index">
            <h4 v-html="c.name"></h4>


            <p v-html="c.type"></p>

            <div v-if="c.answers" v-for="(answer,index) in c.answers">
                <p v-html="answer"></p>
            </div>
        </div>
    </div>

    <input type="hidden" :value="JSON.stringify(thisproject.inputs)" name="inputs">

    <div class="field">
        <label for="ninputs" class="label">
            Number of inputs
        </label>
        <div class="control">
            <input type="number" class="input" id="ninputs" min="0" max="10" value="1" v-model.number="thisproject.ninputs">
        </div>
    </div>
    <div class="columns is-multiline is-mobile">
        <div class="inputs" v-for="(t,index) in thisproject.inputs" :key="index">
            <div class="column">
                <div class="field">
                    <label for="name" class="label">
                        Input Name
                    </label>

                    <div class="control">
                        <input type="text" class="input" v-model="thisproject.inputs[index].name">
                    </div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="checkbox">
                        <input type="checkbox" v-model="thisproject.inputs[index].mandatory" checked="checked">
                        Mandatory
                    </label>
                </div>

                <span v-if="(thisproject.inputs[index].type == 'multiple choice' || thisproject.inputs[index].type == 'one choice')">
                    <div class="field">
                        <label class="label">Number of Answers</label>
                        <div class="control">
                            <input v-model.number="thisproject.inputs[index].numberofanswer" class="input" type="number" placeholder="">
                        </div>
                    </div>
                    <div class="field" v-for="na in thisproject.inputs[index].numberofanswer">
                        <label class="label">Answers</label>
                        <div class="control" >
                            <input v-model="thisproject.inputs[index].answers[na-1]" class="input" type="text" placeholder="">
                        </div>
                    </div>
                </span>
            </div>
        </span>
    </div>
</div>
<div class="columns">
    <div class="column is-4">
        <details>
            <summary>
                <label for="media" class="label" style="display: inline-flex;">
                    Media
                    <label id="media[]" class="checkbox" style="display: inline-block;margin-left: 5px;" @click="selectAllHandler('media')">
                        <input type="checkbox" id="mediai">
                        Select all
                    </label>
                </label>
            </summary>
            <div class="field" v-for="m in data.media">
                <label class="checkbox">

                    <input type="checkbox" :id="m.id" name="media" :value="m.id" v-model="thisproject.checkedmedia">
                    {{m.name}}
                </label><br>


            </div>
        </details>
    </div>
    <div class="column is-4">
        <details>
            <summary>
                <label for="places" class="label" style="display: inline-flex;">
                    Places
                    <label id="places" class="checkbox" style="display: inline-block;margin-left: 5px;" @click="selectAllHandler('places')">
                        <input type="checkbox" id="placesi">
                        Select all
                    </label>
                </label>
            </summary>
            <div class="field" v-for="p in data.places">
                <label class="checkbox" >
                    <input type="checkbox" :id="p.id" name="places" :value="p.id" v-model="thisproject.checkedplaces">
                    {{p.name}}
                </label><br>

            </div>
        </details>
    </div>
    <div class="column is-4">
        <details>
            <summary>
                <label for="cp" class="label" style="display: inline-flex;">
                    Communication Partner
                    <label id="cp" class="checkbox" style="display: inline-block;margin-left: 5px;" @click="selectAllHandler('cp')">
                        <input type="checkbox" id="cpi">
                        Select all
                    </label>
                </label>
            </summary>
            <div class="field" v-for="c in data.cp">

                <label class="checkbox">
                    <input type="checkbox" :id="c.id" name="cp" :value="c.id" v-model="thisproject.checkedcp">
                    {{c.name}}
                </label><br>


            </div>
        </details>
    </div>
</div>
<div class="field">
    <div class="control">
        <button class="button is-link" @click.preventdefault="save">Edit Project</button>
    </div>
</div>
<div class="level">
    <div class="columns">
        <div class="column">
            <div class="notification is-danger" v-if="response != ''" v-html="response">
                <button class="delete" @click.preventdefault="response = ''"></button>
            </div>
        </div>
    </div>
</div>
</div>
</template>

<script>
    export default {
        props:['project','data'],
        computed: {
            'formattedinputstring': function(){
              console.log("computed formattedstring");
              return JSON.stringify(this.thisproject.inputs);
          }
      },
      watch: {
        'thisproject.ninputs': function (newVal,oldVal) {
            if(!this.firstLoading){
                console.log(this.firstLoading);
                console.log("token number watcher fired");
                if(newVal < 0 || oldVal < 0) {
                    newVal = 0;
                    oldVal = 0;
                }

                let direction = (newVal-oldVal);

                if(direction > 0){
                    let inputtemplate = {
                        name: "",
                        type: "",
                        numberofanswer: 0,
                        mandatory: true,
                        answers: []
                    };

                    for (var i = 0; i < direction; i++) {
                        this.thisproject.inputs.push(inputtemplate);
                    }
                }else if(newVal == 0){
                            // special case
                            this.thisproject.inputs = [];
                        }else{
                            // decrease
                            for (var i = 0; i < Math.abs(direction); i++) {
                                this.thisproject.inputs.pop();

                            }
                        }
                    }else{
                        this.firstLoading = false;
                    }
                }
            },
            data() {
                return {
                    response: '',
                    firstLoading: true,
                    thisproject: {
                        edit: false,
                        name: "",
                        ninputs: 0,
                        inputs:[],
                        config: window.inputs,
                        response: "",
                        media: "",
                        places: "",
                        cp: "",
                        checkedmedia: [],
                        checkedplaces: [],
                        checkedcp: []
                    }
                }
            },
            created() {
                this.fillInputs();

            },
            methods: {
                fillInputs: function()
                {

                    console.log(this.project);
                    this.thisproject.inputs = this.project.inputs;
                    this.thisproject.ninputs = this.project.inputs.length;
                    this.thisproject.name = this.project.name;
                    this.thisproject.description = this.project.description;

                    this.thisproject.checkedmedia = this.project.media;
                    this.thisproject.checkedplaces = this.project.places;
                    this.thisproject.checkedcp = this.project.communication_partners;
                },
                save: function()
                {


                    let submitObject = {};
                    _.merge(submitObject,{id: this.project.id},{name: this.thisproject.name},{description: this.thisproject.description},{inputs: this.formattedinputstring},{media: this.thisproject.checkedmedia},{places: this.thisproject.checkedplaces},{cp: this.thisproject.checkedcp});

                    window.axios.patch('../projects/'+submitObject.id, submitObject).then(response => {

                        if(response.message) this.response = response.message;
                        else{
                            this.$snackbar.open(response.data);
                        }


                    }).catch(error => {
                        console.log(error);
                        if(error.message)this.$snackbar.open(error.message);
                        else {
                            this.$snackbar.open(error.response.data);
                        }

                    });

                },
                selectAllHandler: function(which)
                {

                    var items = document.getElementsByName(which);
                    var element = document.getElementById(which+"i");
                    console.log(which);
                    console.log(element.checked);
                    var varname = 'checked'+which;
                    if(!element.checked){
                        for (var i = 0; i < items.length; i++) {
                            if (items[i].type == 'checkbox')
                                items[i].checked = false;
                        }
                        console.log(varname);
                        this.thisproject[varname] = [];
                    }else{
                        for (var i = 0; i < items.length; i++) {
                            if (items[i].type == 'checkbox'){
                                items[i].checked = true;
                            }
                             this.thisproject[varname] = this.data[which];
                        }
                    }


                }
            }
        }
    </script>
