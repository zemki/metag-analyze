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
           <!-- Authentication Links -->


           <div class="navbar-item">
            <p class="control">
              <a class="" href="{{url('projects/new')}}">
                <span class="icon">
                  <plus-icon title="this is an icon!" />
                </span>
                <span>
                 {{ __('New Project') }}
               </span>
             </a>
           </p>
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
