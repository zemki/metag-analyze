<template>
  <div class="p-2 space-y-8 bg-top divide-y-0">
    <div class="space-y-8 divide-y-0">
      <div>
        <h3 class="text-lg font-medium leading-6 text-gray-900">{{trans('Edit Project')}}</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ trans("You can edit your project here.") }}
        </p>
      </div>

        
        <div>
  <label for="name" class="block text-sm font-medium text-gray-700">{{trans('Name')}}</label>
  <div class="mt-1">
    <input type="name" :disabled="!editable" name="name" id="name" class="block w-full p-2 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" v-model="projectData.name">
  </div>
</div>
<div>
  <label for="description" class="block text-sm font-medium text-gray-700">{{trans('Description')}}</label>
  <div class="mt-1" >
    <textarea :disabled="!editable"
              name="description"
              v-model="projectData.description" 
              rows="3" 
              id="description" 
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
              </textarea>
              </div>
  </div>
      </div>
      <div>
        <label for="media" class="block text-sm font-medium text-gray-700"
          >{{trans('Media')}}</label
        >
        <div
          class="mt-1"
          v-for="(singleMedia, index) in projectData.media"
          :key="index"
        >
          <input
            type="text"
            name="media[]"
            id="media"
            :disabled="!editable"
            v-model="projectData.media[index]"
            @keyup="handleMediaInputs(index, singleMedia.name)"
            autocomplete="off"
            @keydown.enter.prevent
            @keydown.tab.prevent
            class="block w-64 p-2 border-b-2 border-blue-500 rounded-md shadow-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          />
        </div>
      </div>


          <label for="inputs" class="block text-sm font-medium text-gray-700">{{trans('Inputs')}} - {{trans('Number of additional inputs')}}</label>


        <input
          type="hidden"
          :value="JSON.stringify(projectData.inputs)"
          name="inputs"
        />

              <div
                      class="relative flex flex-row w-64 h-10 mt-1 bg-transparent rounded-lg">
                  <button
                  :disabled="!editable"
                          class="w-20 h-full text-gray-600 bg-gray-300 rounded-l outline-none cursor-pointer hover:text-gray-700 hover:bg-gray-400"
                          @click.prevent="(projectData.ninputs >= 1) ? projectData.ninputs-- : projectData.ninputs">
                      <span
                              class="m-auto text-2xl font-thin">−</span>
                  </button>
                  <input
                          v-model.number="projectData.ninputs"
                          class="flex items-center w-full font-semibold text-center text-gray-700 bg-white outline-none focus:outline-none text-md hover:text-black focus:text-black md:text-basecursor-default"
                          max="3"
                          min="0"
                          steps="!"
                          name="inputs"
                          id="ninputs"
                          type="number"
                          :disabled="!editable"
                          
                          value="0"></input>
                  <button
                  :disabled="!editable"
                          class="w-20 h-full text-gray-600 bg-gray-300 rounded-r cursor-pointer hover:text-gray-700 hover:bg-gray-400"
                          @click.prevent="(projectData.ninputs <= 2) ? projectData.ninputs++ : projectData.ninputs">
                      <span
                              class="m-auto text-2xl font-thin">+</span>
                  </button>
              </div>

        

          <div
            class=""
            v-for="(t, index) in projectData.inputs"
            :key="index"
          >
          <div>
  <label class="block text-sm font-medium text-gray-700">{{trans('Input Name')}}</label>
  <div class="">
    <input v-model="projectData.inputs[index].name" type="text" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" :disabled="!editable">
  </div>
</div>
  <div class="relative flex items-start my-2">
    <div class="flex items-center h-5">
      <input v-model="projectData.inputs[index].mandatory" :disabled="!editable" checked="checked" type="checkbox" class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500">
    </div>
    <div class="ml-3 text-sm">
      <label for="comments" class="font-medium text-gray-700">{{trans('Mandatory')}}</label>
    </div>
  </div>

  <label id="listbox-label" class="block text-sm font-medium text-gray-700"> {{trans('Type')}} </label>
  <div class="relative mt-1">
    <button @click="showDropdownInputs(index)" type="button" :class="(projectData.inputs[index].type !== '') ? 'relative w-full py-2 pl-3 pr-10 text-left bg-white border border-gray-300 rounded-md shadow-sm cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm':'relative w-full py-4 pl-3 pr-10 text-left bg-white border border-gray-300 rounded-md shadow-sm cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm'" aria-haspopup="listbox" >
      <span class="block truncate" > {{projectData.inputs[index].type}}  </span>
      <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
        <!-- Heroicon name: solid/selector -->
        <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </span>
    </button>
    <ul :id="'type'+index" class="absolute z-10 hidden w-full py-1 mt-1 overflow-auto text-base bg-white rounded-md shadow-lg max-h-60 ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm" tabindex="-1" role="listbox" aria-labelledby="listbox-label">
      <li :class="(type == projectData.inputs[index].type) ? 'relative py-2 pl-3 bg-blue-500 text-white cursor-default select-none pr-9' : 'relative py-2 pl-3 text-gray-900 cursor-default select-none pr-9'" id="listbox-option-0" role="option"  v-for="(type,indexT) in projectData.config.available" :key="indexT" @click="projectData.inputs[index].type = type;showDropdownInputs(index)" >
        <!-- Selected: "font-semibold", Not Selected: "font-normal" -->
        <span class="block font-normal truncate" >{{type}} </span>

        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-white" v-if="type == projectData.inputs[index].type">
          <!-- Heroicon name: solid/check -->
          <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
        </span>
      </li>

    </ul>
  </div>


<div
   v-if="
                  projectData.inputs[index].type == 'multiple choice' ||
                  projectData.inputs[index].type == 'one choice'
                "
          class="mt-1"
        >
                  <label class="">Answers</label>
                  <div
                    class="mt-2"
                    v-for="(m, answerindex) in projectData.inputs[index]
                      .answers"
                      :key="answerindex"
                  >
          <input
            type="text"
                      v-model="projectData.inputs[index].answers[answerindex]"
                      @keyup="handleAdditionalInputs(index, answerindex, m)"
                      autocomplete="off"
                      @keydown.enter.prevent
                      @keydown.tab.prevent
                      :disabled="!editable"
           
            class="block w-64 p-2 border-b-2 border-blue-500 rounded-md shadow-none first:mt-0 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          />
      </div>


            </div>
            <div class="relative mt-4 mb-2">
  <div class="absolute inset-0 flex items-center" aria-hidden="true">
    <div class="w-full border-t border-gray-500 border-solid"></div>
  </div>
  <div class="relative flex justify-center">
    <span class="px-2 text-gray-500 bg-white">
      <svg class="w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
        <path fill="#6B7280" fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
      </svg>
    </span>
  </div>
</div>
          </div>

          
          <button type="button" @click.preventdefault="save" :disabled="!editable" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-500 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">{{trans('Edit Project')}}</button>
     <div                 v-if="response != ''"  class="p-4 mb-4 rounded-md bg-red-50">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <!-- Heroicon name: solid/x-circle -->
                        <svg class="w-5 h-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800"  v-html="response">
                            
                        </h3>
                    </div>
                </div>
            </div>

      </div>
    
</template>

<script>
export default {
  props: ["project", "data", "editable", "projectmedia"],
  computed: {
    formattedinputstring: function () {
      return JSON.stringify(this.projectData.inputs);
    },
  },
  watch: {
    "projectData.ninputs": function (newVal, oldVal) {
      if (!this.firstLoading) {
        if (newVal < 0 || oldVal < 0) {
          newVal = 0;
          oldVal = 0;
        }

        let direction = newVal - oldVal;

        if (direction > 0) {
          let inputtemplate = {
            name: "",
            type: "",
            numberofanswer: 0,
            mandatory: true,
            answers: [""],
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
      } else {
      }
    },
  },
  data() {
    return {
      showCustomSelect: [false,false,false],
      response: "",
      firstLoading: true,
      projectData: {
        
        name: "",
        ninputs: 0,
        inputs: [],
        config: window.inputs,
        response: "",
        media: "",
      },
    };
  },
  created() {
    this.fillInputs();
  },
  methods: {
    showDropdownInputs: function (index) {
      if(this.editable){
       var element = document.getElementById("type"+index);
        
        element.classList.toggle("hidden");
      this.$forceUpdate();
      }
       
    },
    fillInputs: function () {
      this.projectData.inputs = JSON.parse(this.project.inputs);
      this.projectData.ninputs = JSON.parse(this.project.inputs, true).length;

      this.projectData.name = this.project.name;
      this.projectData.description = this.project.description;
      this.projectData.media = this.projectmedia;
      this.projectData.media.push("");
      let self = this;

      setTimeout(function () {
        self.firstLoading = false;
      }, 1000);
    },
    save: function () {
      let submitObject = {};
      _.merge(
        submitObject,
        { id: this.project.id },
        { name: this.projectData.name },
        { description: this.projectData.description },
        { inputs: this.formattedinputstring },
        { media: this.projectData.media }
      );

      window.axios
        .patch("../projects/" + submitObject.id, submitObject)
        .then((response) => {
          if (response.message) this.response = response.message;
          else {
            this.$buefy.snackbar.open(response.data);
          }

          setTimeout(location.reload(true), 2000);
        })
        .catch((error) => {
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
      if (index != 0 && mediaName == "")
        this.projectData.media.splice(index, 1);
    },
    handleAdditionalInputs(questionindex, answerindex, answer) {
      let isLastElement =
        answerindex + 1 ==
        this.projectData.inputs[questionindex].answers.length;

      if (isLastElement) {
        if (answer != "")
          this.projectData.inputs[questionindex].answers.push("");
      }

      let middleElementRemoved = answerindex != 0 && answer == "";
      if (middleElementRemoved) {
        this.projectData.inputs[questionindex].answers.splice(answerindex, 1);
      }

      this.projectData.inputs[questionindex].numberofanswer =
        this.projectData.inputs[questionindex].answers.length - 1;
    },
  },
};
</script>
