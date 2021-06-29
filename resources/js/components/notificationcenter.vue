<template>
<div>

    <div class="my-4" v-for="oneCase in cases">
        <h2 class="break-words text-2xl font-bold font-serif">{{oneCase.name}}</h2>
        <p class="break-words font-bold font-serif">{{oneCase.user.email}}</p>
        <div class="block">
            <label for="name" class="uppercase tracking-wide text-gray-700 text-base font-bold">
                {{trans('Notification Title')}} *
            </label>
            <input type="text"
                   class="mb-0 bg-white focus:outline-none focus:ring border border-gray-300 rounded-lg py-2 px-4 block w-full appearance-none leading-normal w-full"
                   name="name">
            <label for="name" class="uppercase tracking-wide text-gray-700 text-base font-bold">
                {{trans('Notification Text')}} *
            </label>
            <textarea class="block resize border w-full rounded-lg"></textarea>

        </div>

        <p class="break-words font-bold " v-if="oneCase.user.deviceID.length !== 0">{{trans('Send notification to')}} {{oneCase.user.deviceID.length}} {{trans('device(s)')}}.</p>

        <p class="break-words font-bold bg-red-500 p-1 text-white" v-if="oneCase.user.deviceID.length === 0">{{trans('No device registered')}}</p>
        <button @click="sendNotification(oneCase)" class="mt-2 bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-black py-2 px-4 border border-blue-500 hover:border-transparent rounded">{{trans('Send Notification')}}</button>

    </div>

</div>
</template>

<script>

    export default {
        props: ['cases'],
        data() {
            return {
            user: {}
            }
        },
        mounted() {


        },
        methods: {
          sendNotification(oneCase){


          }
        }
    }
</script>

