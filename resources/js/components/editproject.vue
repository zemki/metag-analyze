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


                    this.thisproject.inputs = JSON.parse(this.project.inputs);
                    this.thisproject.ninputs = JSON.parse(this.project.inputs,true).length;
                    console.log(this.thisproject.inputs);
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

                }
            }
        }
    </script>
