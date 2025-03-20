<template>
  <div class="fixed bottom-0 right-0 bg-white p-4 border shadow-lg z-50 w-96 max-h-96 overflow-auto">
    <h3 class="font-bold text-lg mb-2">Vue 3 Debug Panel</h3>
    <div class="mb-2">
      <p>Vue version: {{ vueVersion }}</p>
      <p>App mounted: {{ isMounted ? 'Yes' : 'No' }}</p>
    </div>
    <div class="mb-2">
      <button @click="logComponentTree" class="bg-blue-500 text-white px-2 py-1 rounded">
        Log Component Tree
      </button>
    </div>
    <div v-if="logs.length">
      <h4 class="font-bold mt-2">Logs:</h4>
      <ul class="text-xs">
        <li v-for="(log, index) in logs" :key="index" class="border-b py-1">
          {{ log }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import { ref, getCurrentInstance, onMounted } from 'vue';

export default {
  name: 'DebugPanel',
  setup() {
    const logs = ref([]);
    const isMounted = ref(false);
    const vueVersion = ref('3.x');
    
    // Log any errors that occur in the application
    const originalConsoleError = console.error;
    console.error = (...args) => {
      logs.value.push(`ERROR: ${args.join(' ')}`);
      originalConsoleError.apply(console, args);
    };
    
    onMounted(() => {
      isMounted.value = true;
      logs.value.push('Debug panel mounted');
      
      // Try to get the Vue version
      try {
        const instance = getCurrentInstance();
        if (instance && instance.appContext.app.version) {
          vueVersion.value = instance.appContext.app.version;
        }
      } catch (e) {
        logs.value.push(`Error getting Vue version: ${e.message}`);
      }
    });
    
    const logComponentTree = () => {
      const instance = getCurrentInstance();
      if (!instance) {
        logs.value.push('No current instance found');
        return;
      }
      
      try {
        // Try to traverse the component tree
        logs.value.push('Component tree logging not implemented in Vue 3');
      } catch (e) {
        logs.value.push(`Error logging component tree: ${e.message}`);
      }
    };
    
    return {
      logs,
      isMounted,
      vueVersion,
      logComponentTree
    };
  }
};
</script>
