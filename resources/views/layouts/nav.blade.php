       <nav class="navbar" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
          <a class="navbar-item">

            {{ config('app.name', 'Metag') }}
        </a>

        <a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </a>
    </div>

    <div id="navbarBasicExample" class="navbar-menu">
      <div class="navbar-start">
        <a class="navbar-item" href="{{url('/')}}">
          Home
      </a>

  </div>

  <div class="navbar-end">



<div class="navbar-item has-dropdown is-hoverable">
    <a class="navbar-link">
        {{ __('New') }}   <span class="caret"></span>
    </a>
    <div class="navbar-dropdown is-right ">
     <a class="navbar-item" href="{{url('projects/new')}}"
     >
     {{ __('Project') }}
 </a>


 <a class="navbar-item" href="{{url('media_groups/new')}}"
 >
 {{ __('Media Group') }}
</a>

<a class="navbar-item" href="{{url('media/new')}}"
>
{{ __('Media') }}
</a>

</div>
</div>

<div class="navbar-item has-dropdown is-hoverable">
  <a class="navbar-link">
     {{ Auth::user()->email }} <span class="caret"></span>
 </a>

 <div class="navbar-dropdown is-right ">
     <a class="navbar-item" href="{{ route('logout') }}"
     onclick="event.preventDefault();
     document.getElementById('logout-form').submit();">
     {{ __('Logout') }}
 </a>
 <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
  @csrf
</form>
</div>
</div>
</div>
</div>
</nav>
