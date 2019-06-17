       <nav class="navbar" role="navigation" aria-label="main navigation">
<div class="mx-auto text-3xl font-bold">
    <a href="{{url('/')}}">
    <img src="{{config('utilities.base64logo')}}" class="w-12 inline">
    Metag Analyze
    </a>
</div>
 <div class="navbar-item is-right ">
     <a class="navbar-item" href="{{ route('logout') }}"
     onclick="event.preventDefault();
     document.getElementById('logout-form').submit();">
     {{ __('Logout') }}
 </a>
 <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
  @csrf
</form>
 </div>
</nav>
