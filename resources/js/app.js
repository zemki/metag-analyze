
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

Vue.component('edit-project', require('./components/editproject.vue').default);


// passport components
Vue.component(
    'passport-clients',
    require('./components/passport/Clients.vue').default
);

Vue.component(
    'passport-authorized-clients',
    require('./components/passport/AuthorizedClients.vue').default
);

Vue.component(
    'passport-personal-access-tokens',
    require('./components/passport/PersonalAccessTokens.vue').default
);


 Vue.use(Buefy)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

 const app = new Vue({
    el: '#app',
    computed: {
        'newproject.formattedinputstring': function(){
          console.log("computed formattedstring");
            return JSON.stringify(this.newproject.inputs);
        }
    },
    watch: {
    	'newproject.ninputs': function (newVal,oldVal) {

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
                    this.newproject.inputs.push(inputtemplate);
                }
            }else if(newVal == 0){
                // special case
                this.newproject.inputs = [];
            }else{
                // decrease
                for (var i = 0; i < Math.abs(direction); i++) {
                    this.newproject.inputs.pop();

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
        newproject:{
          name: "",
          ninputs: 0,
          inputs:[],
          config: window.inputs,
          response: ""
      }
  },
  methods: {
    validateSubmitCaseForm()
    {
        this.newproject.response = "";
        if(this.newproject.name == "") this.newproject.response += this.errormessages.namemissing;

        if(this.newproject.ninputs > 0){
          if(_.find(this.newproject.inputs, {name: ''})) this.newproject.response += this.errormessages.inputnamemissing;
          if(_.find(this.newproject.inputs, {type: ''})) this.newproject.response += this.errormessages.inputtypemissing;

          // if multiple or onechoice and no answers throw error
          if(_.find(this.newproject.inputs, function(o){
            if(o.type == 'multiple choice' || o.type == 'one choice' && (o.numberofanswer != o.answers.length)) return true;
        })) this.newproject.response += this.errormessages.multipleinputnoanswer;
      }


      if(this.newproject.response == "") return true
        else return false

    }
}
});
