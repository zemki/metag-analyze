<template>

    <div class="content">
        <label class="checkbox">
          <input :disabled="!editable" type="checkbox" v-model="thisproject.edit">
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
            </div>

        </div>
        <div class="level-right">

        </div>
    </div>
        <div class="level">
            <div class="column is-3">
                <label for="media" class="label" style="display: inline-flex;">
                    Media
                </label>
                <div class="control" v-for="(m,index) in thisproject.media">
                    <input :disabled="!editable" type="text" name="media[]" class="input inputcreatecase" v-model="thisproject.media[index]" @keyup="handleMediaInputs(index,m)" autocomplete="off"  @keydown.enter.prevent @keydown.tab.prevent>
                </div>

            </div>
        </div>

    <h2>Inputs</h2>

    <input type="hidden" :value="JSON.stringify(thisproject.inputs)" name="inputs">
        <b-field label="Number of additional inputs" :disabled="!editable">
            <b-numberinput :disabled="!editable" name="inputs" id="ninputs" controls-position="compact" type="is-light" min="0" max="3" :editable="false" steps="1" v-model.number="thisproject.ninputs"></b-numberinput>
        </b-field>

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
        <button class="button is-link" @click.preventdefault="save" :disabled="!editable">Edit Project</button>
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
        props:['project','data','editable'],
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

                    this.thisproject.inputs = this.thisproject.inputs.map(function(item) {
                        item.numberofanswer = item.answers.length;
                        return item;
                    });

                    this.thisproject.name = this.project.name;
                    this.thisproject.description = this.project.description;
                    this.thisproject.media = this.project.media;

                },
                save: function()
                {

                    let submitObject = {};
                    _.merge(submitObject,{id: this.project.id},{name: this.thisproject.name},{description: this.thisproject.description},{inputs: this.formattedinputstring},{media: this.thisproject.media});

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
                handleMediaInputs(index,mediaName)
                {


                    let isLastElement = index+1 == this.thisproject.media.length;
                    if(isLastElement)
                    {
                        if(mediaName != "")this.thisproject.media.push("");

                    }
                    if(index != 0 && mediaName == "")this.thisproject.media.splice(index,1);

                }
            }
        }
    </script>
