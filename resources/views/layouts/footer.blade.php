<footer style="position:fixed;bottom:0px;z-index:99;background-color:#ddd;height:50px;width:100%;">
    <center>
        <div style="color:gray;margin-top:1px;font-size:14px;border-top:1px solid black;padding:10px;">
        {{ $appName }}
        — Version {{ $appVersion }}
        @if($appBuild)
            / Build {{ $appBuild }}
        @endif
        — &copy; {{ date('Y') }} {{ $appOwner }}
        </div>
    </center>
</footer>
