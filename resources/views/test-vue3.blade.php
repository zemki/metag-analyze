<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Vue 3 Test</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script type="module" src="{{ asset('js/test-vue3-app.js') }}"></script>
    <script>
        window.trans = window.trans || {};

        // Display any errors on the page itself
        window.addEventListener('error', function(event) {
            const errorDiv = document.getElementById('error-output');
            if (errorDiv) {
                errorDiv.innerHTML += `<p>ERROR: ${event.message} at ${event.filename}:${event.lineno}</p>`;
                errorDiv.style.display = 'block';
            }
        });
    </script>
</head>
<body>
    <h1 class="text-3xl font-bold p-4">Vue 3 Standalone Test Page</h1>
    
    <!-- Error display area -->
    <div id="error-output" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded m-4" style="display: none;"></div>
    
    <!-- Vue mount point -->
    <div id="app" class="p-4">
        <test-vue3></test-vue3>
    </div>
</body>
</html>