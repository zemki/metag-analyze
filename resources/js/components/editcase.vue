<template>

    <div class="content">
        <label class="checkbox">
          <input type="checkbox" v-model="thiscase.edit">
          edit
      </label>
      <div class="level">
        <input type="hidden" :value="cases.id" name="id">
        <div class="level-left">
            <h1 v-if="!thiscase.edit" v-html="cases.name"></h1>

            <div class="field has-addons" v-if="thiscase.edit"><input name="name" v-model="cases.name" class="input text is-large">
                <div class="control">
                    <button type="submit" class="button is-primary is-large">Save</button>
                </div>
            </div>
        </div>
        <div class="level-right">
            <div class="field">
            </div>
        </div>
    </div>
    <h2>Inputs</h2>


    <div class="columns is-multiline is-mobile">

        <div class="inputs column is-4" v-for="(c,index) in cases.inputs" :key="index">
            <h4 v-html="c.name"></h4>
            <p v-html="c.type"></p>
        <div v-if="c.answers" v-for="(answer,index) in c.answers">
            <p v-html="answer"></p>
        </div>
        </div>
    </div>
</div>
</template>

<script>
    export default {
        props:['cases'],
        data() {
            return {
                thiscase: {
                    edit: false
                }
            }
        },
        mounted() {
            console.log('Component mounted.')
        }
    }
</script>
