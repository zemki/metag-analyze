
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
Vue.component('consult-entries', require('./components/consultentries.vue').default);

 Vue.use(Buefy);
import {GoogleCharts} from 'google-charts';
import VueChartkick from 'vue-chartkick'

Vue.use(VueChartkick)
Vue.use(GoogleCharts)
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

 const app = new Vue({
    el: '#app',
    computed: {
        'newproject.formattedinputstring': function(){
            return JSON.stringify(this.newproject.inputs);
        }
    }, watch: {
        'newcase.duration.selectedUnit': function (newVal,OldVal){
            if(!_.isEmpty(this.newcase.duration.input)){

            if(newVal === 'week') var numberOfDaysToAdd = parseInt(this.newcase.duration.input)*7;
            else var numberOfDaysToAdd = parseInt(this.newcase.duration.input);

            let {cdd, cmm, cy} = this.formatDurationMessage(numberOfDaysToAdd);

            this.newcase.duration.message = cdd + '.'+ cmm + '.'+ cy;
                this.newcase.duration.value = "value:"+numberOfDaysToAdd*24+"|days:"+numberOfDaysToAdd;

            }else{
                this.newcase.duration.message = "";
                this.newcase.duration.value = "";

            }
        },
        'newcase.duration.input': function (newVal,OldVal){

            this.newcase.duration.input = newVal.replace(/\D/g,'');

            if(!_.isEmpty(this.newcase.duration.selectedUnit)){

            if(this.newcase.duration.selectedUnit === 'week') var numberOfDaysToAdd = parseInt(newVal)*7;
            else var numberOfDaysToAdd = parseInt(newVal);

                let {cdd, cmm, cy} = this.formatDurationMessage(numberOfDaysToAdd);

                // duration in days and change to this after first login
                this.newcase.duration.message = cdd + '.'+ cmm + '.'+ cy;
                this.newcase.duration.value = "value:"+numberOfDaysToAdd*24+"|days:"+numberOfDaysToAdd;

            }else{
                this.newcase.duration.message = "";
                this.newcase.duration.value = "";

            }
        },
    	'newproject.ninputs': function (newVal,oldVal) {

            if(newVal < 0 || oldVal < 0) {
                newVal = 0;
                oldVal = 0;
            }

            let direction = (newVal-oldVal);

            if(direction > 0){
                let inputtemplate = {
                    name: "",
                    type: "",
                    mandatory: true,
                    numberofanswer: 0,
                    answers: [""]
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
        mainNotification: true,
        selectedEntriesData: [],
        errormessages:{
            namemissing: "name is required. <br>",
            inputnamemissing: "input name is required. <br>",
            inputtypemissing: "input type is required. <br>",
            multipleinputnoanswer: "provide a valid number of answers. <br>"

        },
        newcase:{
            duration: {
                input: "",
                selectedUnit: "",
                allowedUnits: ["day(s)","week(s)"],
                message: "",
                value:""
            }
        },
        newproject:{
          name: "",
          ninputs: 0,
          inputs:[
          ],
          config: window.inputs,
          response: "",
            media: [""]

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

    },
      formatDurationMessage(numberOfDaysToAdd) {
          var calculatedDate = new Date();
          //get today date
          var dd = calculatedDate.getDate();
          var mm = calculatedDate.getMonth() + 1;
          var y = calculatedDate.getFullYear();

          calculatedDate.setDate(calculatedDate.getDate() + numberOfDaysToAdd);
          var cdd = calculatedDate.getDate();
          var cmm = calculatedDate.getMonth() + 1;
          var cy = calculatedDate.getFullYear();
          return {cdd, cmm, cy};
      },
      handleMediaInputs(index,mediaName)
      {


          let isLastElement = index+1 == this.newproject.media.length;
          if(isLastElement)
          {
              if(mediaName != "")this.newproject.media.push("");

          }
          if(index != 0 && mediaName == "")this.newproject.media.splice(index,1);

      },
      handleAdditionalInputs(questionindex,answerindex,answer)
      {
          let isLastElement = answerindex+1 == this.newproject.inputs[questionindex].answers.length;

          if(isLastElement)
          {
              if(answer != "")this.newproject.inputs[questionindex].answers.push("");

          }


          let middleElementRemoved = answerindex != 0 && answer == "";
          if(middleElementRemoved){
              this.newproject.inputs[questionindex].answers.splice(answerindex,1);
          }

          this.newproject.inputs[questionindex].numberofanswer = this.newproject.inputs[questionindex].answers.length-1;
      },
      validateProject(e)
      {
          var self = this;
          self.newproject.response = "";
          if(this.newproject.name === "") this.newproject.response = "Enter a project name <br>";
          if(this.newproject.description === "") this.newproject.response = "Enter a project description <br>";
          if(this.newproject.media.length === 0 || this.newproject.media[0] === "")this.newproject.response +="Enter the list of media<br>";

          _.forEach(this.newproject.inputs, function(value) {
              console.log(value);
              if(value.numberofanswer == 0 && (value.type !== "text" && value.type !== "scale")) self.newproject.response +="Enter answers for each input<br>";
              if(value.name === "")self.newproject.response +="Enter a name for each input. <br>";

          });

          if(this.newproject.response !== "")e.preventDefault();
      }
}
});
