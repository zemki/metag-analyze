<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opening MeTag App...</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .container {
            text-align: center;
            padding: 40px;
            max-width: 500px;
        }
        .loader {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 30px auto;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .fallback {
            display: none;
            margin-top: 30px;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            margin: 10px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Opening MeTag App...</h1>
        <div class="loader"></div>
        <p>You will be redirected to the MeTag mobile app.</p>

        <div id="fallback" class="fallback">
            <h2>App Not Installed?</h2>
            <p>Download MeTag to continue:</p>
            <a href="{{ $app_store_url }}" class="btn">📱 Download for iPhone</a>
            <a href="{{ $play_store_url }}" class="btn">🤖 Download for Android</a>
            <p style="margin-top: 30px; font-size: 14px;">
                Already have the app? <a href="{{ $deep_link }}" style="color: white; text-decoration: underline;">Open MeTag</a>
            </p>
        </div>
    </div>

    <script>
        // Attempt to open app
        window.location = "{{ $deep_link }}";

        // Show fallback after 2 seconds if app didn't open
        setTimeout(function() {
            document.getElementById('fallback').style.display = 'block';
            document.querySelector('.loader').style.display = 'none';
        }, 2000);
    </script>
</body>
</html>
