<template>
  <div class="p-8 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Vue 3 Test Component</h1>
    <p>This is a simple Vue 3 component to test if Vue 3 is working correctly.</p>
    <button 
      @click="count++"
      class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
    >
      Clicked {{ count }} times
    </button>
  </div>
</template>

<script>
import { ref } from 'vue';

export default {
  name: 'TestVue3',
  setup() {
    const count = ref(0);
    
    return {
      count
    };
  }
};
</script>