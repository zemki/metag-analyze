<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title')</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <style>
        .btn {
            box-sizing: border-box;
            display: inline-block;
            text-align: left;
            white-space: nowrap;
            text-decoration: none;
            vertical-align: middle;
            touch-action: manipulation;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            border: 1px solid #ddd;
            padding: 4px 8px;
            margin: 5px auto;
            border-radius: 4px;
            color: #fff;
            fill: #fff;
            background: #000;
            line-height: 1em;
            min-width: 190px;
            height: 45px;
            transition: 0.2s ease-out;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
            font-family: $btn-font;
            font-weight: 500;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            -moz-font-feature-settings: 'liga', 'kern';
        }

        .btn:hover,
        .btn:focus {
            background: #111;
            color: #fff;
            fill: #fff;
            border-color: #fff;
            transform: scale(1.01) translate3d(0, -1px, 0);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn:active {
            outline: 0;
            background: #353535;
            transition: none;
        }

        .btn__icon,
        .btn__text,
        .btn__storename {
            display: inline-block;
            vertical-align: top;
        }

        .btn__icon {
            width: 30px;
            height: 30px;
            margin-right: 5px;
            margin-top: 2px;
        }

        .btn__icon--amazon {
            transform: scale(0.85);
        }

        .btn__text {
            letter-spacing: 0.08em;
            margin-top: -0.1em;
            font-size: 10px;
        }

        .btn__storename {
            display: block;
            margin-left: 38px;
            margin-top: -17px;
            font-size: 22px;
            letter-spacing: -0.03em;
        }

        .btn--small {
            padding: 2px 8px;
            min-width: 118.75px;
            height: 24px;
            border-radius: 3px;
        }

        .btn--small .btn__icon {
            width: 16px;
            height: 16px;
            margin: 1px 2px 0 0;
        }

        .btn--small .btn__text {
            display: none;
        }

        .btn--small .btn__storename {
            font-size: 12px;
            display: inline-block;
            margin: 0;
            vertical-align: middle;
        }

        .btn--tiny {
            padding: 3px;
            width: 22px;
            height: 22px;
            min-width: 0;
            border-radius: 3px;
        }

        .btn--tiny .btn__icon {
            width: 14px;
            height: 14px;
            margin: 0;
        }

        .btn--tiny .btn__text,
        .btn--tiny .btn__storename {
            display: none;
        }

        html,
        body {
            background: #f4b350;
            width: 100%;
            height: 100%;
        }

        body {
            box-sizing: border-box;
            padding: 0 10%;
            max-width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background-image: radial-gradient(ellipse at center, rgba(255, 255, 255, 0.4) 0%, rgba(255, 255, 255, 0) 80%);
        }

        .stage>h1 {
            margin-top: -5%;
            margin-bottom: 5%;
            font-family: Avenir, Trebuchet, 'Trebuchet MS', sans-serif;
            font-size: 7vw;
            font-weight: 400;
            color: #c67d0c;
        }

        @media (min-width: 50em) {
            .stage>h1 {
                font-size: 5vw;
            }
        }

        html,
        body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 100;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .code {
            border-right: 2px solid;
            font-size: 26px;
            padding: 0 15px 0 15px;
            text-align: center;
        }

        .message {
            font-size: 18px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="flex-center position-ref full-height">
        <div class="code">
            @yield('code')
        </div>

        <div class="message" style="padding: 10px;">
            @yield('message')
            @yield('url')
        </div>
    </div>
</body>

</html>