<template>

    <div class="content">
        <label class="checkbox">
            <input :disabled="!editable" type="checkbox" v-model="projectData.edit">
            Edit name and description
        </label>
        <div class="level" v-if="projectData.edit">
            <input type="hidden" :value="project.id" name="id">
            <div class="level-left">
                <h1 v-if="!projectData.edit" v-html="projectData.name"></h1>
                <div class="field has-addons" v-if="projectData.edit"><input name="name" v-model="projectData.name"
                                                                             class="input text is-large">
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
                <p v-if="!projectData.edit" v-html="projectData.description"></p>
                <div class="field has-addons" v-if="projectData.edit">
                    <textarea name="description" v-model="projectData.description" class="input textarea"></textarea>
                </div>

            </div>
            <div class="level-right">

            </div>
        </div>
        <div class="level">
            <div class="column is-3">
                <label for="media" class="label inline-flex">
                    Media
                </label>
                <div class="control" v-for="(m,index) in projectData.media">
                    <input :disabled="!editable" type="text" name="media[]" class="input inputcreatecase"
                           v-model="projectData.media[index]" @keyup="handleMediaInputs(index,m)" autocomplete="off"
                           @keydown.enter.prevent @keydown.tab.prevent>
                </div>

            </div>
        </div>

        <h2>Inputs</h2>

        <input type="hidden" :value="JSON.stringify(projectData.inputs)" name="inputs">
        <b-field label="Number of additional inputs" :disabled="!editable">
            <b-numberinput :disabled="!editable" name="inputs" id="ninputs" controls-position="compact" type="is-light"
                           min="0" max="3" :editable="false" steps="1"
                           v-model.number="projectData.ninputs"></b-numberinput>
        </b-field>

        <div class="columns is-multiline is-mobile">
            <div class="inputs" v-for="(t,index) in projectData.inputs" :key="index">
                <div class="column">
                    <div class="field">
                        <label for="name" class="label">
                            Input Name
                        </label>

                        <div class="control">
                            <input type="text" class="input" v-model="projectData.inputs[index].name">
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="checkbox">
                            <input type="checkbox" v-model="projectData.inputs[index].mandatory" checked="checked">
                            Mandatory
                        </label>
                    </div>
                    <div class="field">
                        <label class="label">Type</label>
                        <div class="control">
                            <div class="select">
                                <select v-model="projectData.inputs[index].type">
                                    <option v-for="type in projectData.config.available" :value="type">{{type}}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <span v-if="(projectData.inputs[index].type == 'multiple choice' || projectData.inputs[index].type == 'one choice')">
                    <div class="field">
                        <label class="label">Answers</label>
                        <div class="control" v-for="(m,answerindex) in projectData.inputs[index].answers">
                            <input type="text" class="input inputcreatecase"
                                   v-model="projectData.inputs[index].answers[answerindex]"
                                   @keyup="handleAdditionalInputs(index,answerindex,m)" autocomplete="off"
                                   @keydown.enter.prevent @keydown.tab.prevent>
                        </div>
                    </div>
                </span>
                </div>
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
        props: ['project', 'data', 'editable'],
        computed: {
            'formattedinputstring': function () {
                console.log("computed formattedstring");
                return JSON.stringify(this.projectData.inputs);
            }
        },
        watch: {
            'projectData.ninputs': function (newVal, oldVal) {
                if (!this.firstLoading) {
                    console.log(this.firstLoading);
                    console.log("token number watcher fired");
                    if (newVal < 0 || oldVal < 0) {
                        newVal = 0;
                        oldVal = 0;
                    }

                    let direction = (newVal - oldVal);

                    if (direction > 0) {
                        let inputtemplate = {
                            name: "",
                            type: "",
                            numberofanswer: 0,
                            mandatory: true,
                            answers: [""]
                        };

                        for (var i = 0; i < direction; i++) {
                            this.projectData.inputs.push(inputtemplate);
                        }
                    } else if (newVal == 0) {
                        // special case
                        this.projectData.inputs = [];
                    } else {
                        // decrease
                        for (var i = 0; i < Math.abs(direction); i++) {
                            this.projectData.inputs.pop();

                        }
                    }
                }else{
                    console.log("not fired.")
                }
            }
        },
        data() {
            return {
                response: '',
                firstLoading: true,
                projectData: {
                    edit: false,
                    name: "",
                    ninputs: 0,
                    inputs: [],
                    config: window.inputs,
                    response: "",
                    media: "",
                    config: window.inputs,
                }
            }
        },
        created() {
            this.fillInputs();

        },
        methods: {
            fillInputs: function () {
                this.projectData.inputs = JSON.parse(this.project.inputs);
                this.projectData.ninputs = JSON.parse(this.project.inputs, true).length;




                this.projectData.name = this.project.name;
                this.projectData.description = this.project.description;
                this.projectData.media = this.project.media;
                this.projectData.media.push("");
                let self = this;
                setTimeout(function () {
                    self.firstLoading = false;
                }, 1000);


            },
            save: function () {

                let submitObject = {};
                _.merge(submitObject, {id: this.project.id}, {name: this.projectData.name}, {description: this.projectData.description}, {inputs: this.formattedinputstring}, {media: this.projectData.media});

                window.axios.patch('../projects/' + submitObject.id, submitObject).then(response => {

                    if (response.message) this.response = response.message;
                    else {
                        this.$buefy.snackbar.open(response.data);
                    }

                    setTimeout(location.reload(true), 2000);


                }).catch(error => {
                    console.log(error);
                    if (error.message) this.$buefy.snackbar.open(error.message);
                    else {
                        this.$buefy.snackbar.open(error.response.data);
                    }

                });

            },
            handleMediaInputs(index, mediaName) {

                let isLastElement = index + 1 == this.projectData.media.length;
                if (isLastElement) {
                    if (mediaName != "") this.projectData.media.push("");
                }
                if (index != 0 && mediaName == "") this.projectData.media.splice(index, 1);

            },
            handleAdditionalInputs(questionindex, answerindex, answer) {
                let isLastElement = answerindex + 1 == this.projectData.inputs[questionindex].answers.length;

                if (isLastElement) {
                    if (answer != "") this.projectData.inputs[questionindex].answers.push("");

                }


                let middleElementRemoved = answerindex != 0 && answer == "";
                if (middleElementRemoved) {
                    this.projectData.inputs[questionindex].answers.splice(answerindex, 1);
                }

                this.projectData.inputs[questionindex].numberofanswer = this.projectData.inputs[questionindex].answers.length - 1;
            }
        }
    }
</script>
