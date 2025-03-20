<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Standalone Vue 3 Test</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            background: #f5f5f5;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        button {
            background: #4a80ff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #3a70ef;
        }
    </style>
</head>
<body>
    <h1>Standalone Vue 3 Test</h1>
    <p>This page doesn't use any of the app's components or JavaScript - it's a minimal test to see if Vue 3 works.</p>
    
    <div id="app">
        <div class="card">
            <h2>Vue 3 Counter</h2>
            <p>Count: {{ count }}</p>
            <button @click="increment">Increment</button>
        </div>
    </div>

    <script>
        const { createApp, ref } = Vue;
        
        createApp({
            setup() {
                const count = ref(0);
                
                function increment() {
                    count.value++;
                }
                
                return {
                    count,
                    increment
                };
            }
        }).mount('#app');
    </script>
</body>
</html>