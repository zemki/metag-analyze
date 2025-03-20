<template><!--Modal-->
    <div class="fixed top-0 left-0 flex items-center justify-center w-full h-auto opacity-0 pointer-events-none modal"
         v-show="entryEdit">
        <div class="absolute w-full h-full bg-gray-900 opacity-50" @click="toggleEntryModal()"></div>

        <div class="z-50 w-full mx-auto overflow-y-auto bg-white rounded shadow-lg modal-container md:max-w-md">

            <div @click="toggleEntryModal()"
                 class="absolute top-0 right-0 z-50 flex flex-col items-center mt-4 mr-4 text-sm text-white cursor-pointer">
                <svg class="text-white fill-current" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                     viewBox="0 0 18 18">
                    <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"></path>
                </svg>
                <span class="text-sm">(Esc)</span>
            </div>

            <!-- Add margin if you want to see some of the overlay behind the modal-->
            <div class="w-auto h-auto px-6 py-4 text-left modal-content">
                <!--Title-->
                <div class="flex items-center justify-between pb-3">
                    <p class="text-2xl font-bold">{{__('Edit Entry')}}</p>
                    <div @click="toggleEntryModal()" class="z-50 cursor-pointer">
                        <svg class="text-black fill-current" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                             viewBox="0 0 18 18">
                            <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"></path>
                        </svg>
                    </div>
                </div>
                <p class="p-3 mt-3 font-bold text-center text-black bg-yellow-500">
                    {{__('Please scroll if you don\'t see all the content.')}}
                </p>

                <!--Body-->
                <input type="hidden" :value="editentry.case_id"/>
                <div class="my-2">
                    <label class="text-base font-bold tracking-wide text-gray-700 uppercase">
                        Start Date/time *
                    </label>
                    <b-datetimepicker
                            :placeholder="trans('Click to select...')"
                            icon="calendar-today"
                            name="begin"
                            v-model="editentry.data.start"
                            @input="newentrydateselected('edit')"
                    >
                    </b-datetimepicker>
                </div>
                <div class="my-2">

                    <label class="text-base font-bold tracking-wide text-gray-700 uppercase">
                        End Date/time *
                    </label>
                    <b-datetimepicker
                            :placeholder="trans('Click to select...')"
                            icon="calendar-today"
                            name="end"
                            v-model="editentry.data.end"
                    >
                    </b-datetimepicker>
                </div>
                <div class="my-2">
                    <label class="text-base font-bold tracking-wide text-gray-700 uppercase">
                        Media *
                    </label>
                    <input type="text" name="media_id"
                           v-model="editentry.data.media_id"
                           class="block w-full px-4 py-2 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring"></input>

                </div>
                <h1 class="my-4 text-2xl font-bold tracking-wide text-center text-gray-700 uppercase">Inputs</h1>
                <div v-for="(value,index) in editentry.inputs" :key="index">
                    <label class="my-2 text-base font-bold tracking-wide text-gray-700 uppercase" v-text="value.mandatory ? value.name +' *' : value.name">

                    </label>
                    <input type="text" v-if="value.type === 'text'" :name="'text'+value.name"
                           v-model="editentry.data.inputs[value.name]"
                           class="block w-full px-4 py-2 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring "></input>
                    <b-taginput
                            :data=value.answers
                            autocomplete
                            size="is-medium"
                            open-on-focus
                            v-if="value.type === 'multiple choice'"
                            v-model="editentry.data.inputs[value.name]"
                    >
                    </b-taginput>
                    <div class="relative"  v-if="value.type === 'one choice'">
                        <select
                                v-model="editentry.data.inputs[value.name]"
                                class="block w-full px-4 py-3 pr-8 leading-tight text-gray-700 bg-gray-200 border border-gray-200 rounded appearance-none focus:outline-none focus:bg-white focus:border-gray-500" >
                            <option v-for="(answer,indexA) in value.answers" :value="answer" :key="indexA">@{{answer}}</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 pointer-events-none">
                            <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>

                    <div class="relative"  v-if="value.type === 'scale'">
                        <select
                                v-model="editentry.data.inputs[value.name]"
                                class="block w-full px-4 py-3 pr-8 leading-tight text-gray-700 bg-gray-200 border border-gray-200 rounded appearance-none focus:outline-none focus:bg-white focus:border-gray-500" >
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 pointer-events-none">
                            <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>

                </div>
                <div class="my-3 text-base">* {{__('required')}}</div>


                <!--Footer-->
                <div class="flex justify-end pt-2">
                    <button class="p-3 px-4 mr-2 text-blue-500 bg-transparent rounded-lg hover:bg-gray-100 hover:text-blue-400"
                            @click="editEntryAndClose()">{{__('Save and Close')}}</button>
                    <button class="p-3 px-4 text-white bg-blue-500 rounded-lg hover:bg-blue-400"
                            @click="toggleEntryModal()">{{__('Close')}}</button>
                </div>

            </div>
        </div>
    </div>
    <!--End Modal--></template>
<script>
export default {
  props: {
    editentry: {
      type: Object,
      required: true
    }
  },
  emits: ['update:editentry'],
  data() {
    return {
      entryEdit: false
    };
  },
  methods: {
    toggleEntryModal() {
      this.entryEdit = !this.entryEdit;
      this.$emit('update:editentry', this.entryEdit);
    },
    editEntryAndClose() {
      // Implement this method based on your requirements
      this.$emit('edit-entry-and-close');
    },
    newentrydateselected(type) {
      // Implement this method based on your requirements
    },
    trans(key) {
      // Translation method
      if (typeof window.trans === 'undefined' || typeof window.trans[key] === 'undefined') {
        return key;
      } else {
        if (window.trans[key] === "") return key;
        return window.trans[key];
      }
    },
    __(key) {
      return this.trans(key);
    }
  }
};
</script>
