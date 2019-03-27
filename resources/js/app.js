
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

 require('./bootstrap');

 import Buefy from 'buefy'

 window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('edit-case', require('./components/editcase.vue').default);

 Vue.use(Buefy)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

 const app = new Vue({
    el: '#app',
    computed: {
        'newcase.formattedinputstring': function(){
            return JSON.stringify(this.newcase.inputs);
        }
    },
    watch: {
    	'newcase.ninputs': function (newVal,oldVal) {

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
                    answers: []
                }

                for (var i = 0; i < direction; i++) {
                    this.newcase.inputs.push(inputtemplate);
                }
            }else if(newVal == 0){
                // special case
                this.newcase.inputs = [];
            }else{
                // decrease
                for (var i = 0; i < Math.abs(direction); i++) {
                    this.newcase.inputs.pop();

                }
            }
        }
    },
    data: {
        errormessages:{
            namemissing: "name is required. <br>",
            inputnamemissing: "input name is required. <br>",
            inputtypemissing: "input type is required. <br>",
            multipleinputnoanswer: "provide a valid number of answers. <br>"

        },
        newcase:{
          name: "",
          ninputs: 0,
          inputs:[],
          config: window.inputs,
          response: ""
      }
  },
  methods: {
      submitCaseForm(event) {

        if(!this.validateSubmitCaseForm()){
          event.preventDefault();
          return false;
        }
        this.newcase.formattedinputstring = JSON.stringify(this.newcase.inputs);
        this.$forceUpdate();
        this.$nextTick(() => {

            if(this.newcase.formattedinputstring != "") event.target.submit();
            else alert("please contact the sysadmin - problem in newcase.formattedinputstring");
        });
    },
    validateSubmitCaseForm()
    {
        this.newcase.response = "";
        if(this.newcase.name == "") this.newcase.response += this.errormessages.namemissing;

        if(this.newcase.ninputs > 0){
          if(_.find(this.newcase.inputs, {name: ''})) this.newcase.response += this.errormessages.inputnamemissing;
          if(_.find(this.newcase.inputs, {type: ''})) this.newcase.response += this.errormessages.inputtypemissing;

          // if multiple or onechoice and no answers throw error
          if(_.find(this.newcase.inputs, function(o){
            if(o.type == 'multiple choice' || o.type == 'one choice' && (o.numberofanswer != o.answers.length)) return true;
        })) this.newcase.response += this.errormessages.multipleinputnoanswer;
      }


      if(this.newcase.response == "") return true
        else return false

    }
}
});
