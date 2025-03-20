@include('layouts.header')

<body>
<div id="app">
    <div class="w-2/3 pt-2 mx-auto">
        <h1 class="text-2xl font-bold my-4">Vue 3 Test Page</h1>
        <p class="mb-4">This is a test page to verify Vue 3 functionality.</p>
        
        <test-vue3></test-vue3>
        
        <debug-panel></debug-panel>
    </div>
</div>

@vite(['resources/sass/app.scss', 'resources/js/app.js'])
</body>
</html>
