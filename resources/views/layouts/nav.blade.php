<header class="bg-blue-500 sm:flex sm:justify-between sm:items-center sm:px-4 sm:py-3 min-w-screen border-t border-b border-blue-500 text-blue-700 ">
    <div class="flex items-center inline justify-between px-4 sm:p-0">
        <div class="">
            <a href="{{url('/')}}" class="text-3xl text-gray-100 font-bold hover:text-red-600">
                <img class="h-8 inline"
                     src="{{config('utilities.base64logo')}}"
                     alt="Metag Analyze">
                Metag Analyze</a>
        </div>
    </div>

    @component('layouts.breadcrumb', ['breadcrumb'=>$breadcrumb ??''])
    @endcomponent

    <nav class="px-2 sm:flex sm:p-0 align-baseline">

        @if(Auth::user()->hasReachMaxNumberOfProjecs())
            <p class="text-yellow-300 bg-red-600 px-2 py-1 lg:inline-block lg:mt-0 mr-4 ">
                {{__('You have reached the max number of Projects! Contact us for solutions!')}}
            </p>
        @endif

        <p class="flex text-white font-bold px-2 py-1">{{auth()->user()->email}}</p>


        <a href="{{url('projects/new')}}" class="flex px-2 py-1 text-white font-semibold rounded hover:bg-red-600"><i
                    class="px-1">+</i> {{ __('New Project') }}</a>

        <a class="flex px-2 py-1 text-white font-semibold rounded hover:bg-red-600"
           href="{{ route('logout') }}"
           onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">
            {{ __('Logout') }}
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </nav>
</header>
