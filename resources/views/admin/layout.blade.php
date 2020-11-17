@include('layouts.header')

<body class="bg-black-alt font-sans leading-normal tracking-normal">
<div id="app">
    <nav id="header" class="bg-gray-900 block w-full z-10 top-0 shadow">

        <?php
        $grav_url = "https://www.gravatar.com/avatar/" . md5( strtolower( trim( Auth::user()->email ) ) ) . "&s=40";
        ?>
        <div class="w-full container mx-auto mx-auto mx-auto mx-auto flex flex-wrap items-center mt-0 pt-3 md:pb-0">

            <div class="w-1/2 pl-2 md:pl-0">
                <a class="text-gray-100 text-base xl:text-xl no-underline hover:no-underline font-bold" href="#">
                    <img class="w-8 h-8 rounded-full mr-4 inline" src="{{config('utilities.base64logo')}}" alt="Logo">
                     Metag Admin
                </a>
            </div>
            <div class="w-1/2 pr-0">
                <div class="flex relative inline-block float-right">
                    <div class="relative text-sm text-gray-100 logoutdropbtn">
                        <button id="userButton"
                                @click="showdropdown('dropdownLogout')"
                                @mouseover="showdropdown('dropdownLogout')"
                                class="logoutdropbtn flex items-center focus:outline-none mr-3">
                            <img class="w-8 h-8 rounded-full mr-4" src="{{$grav_url}}" alt="Avatar of User">
                            <span class="md:inline-block text-gray-100">{{ Auth::user()->email }}</span>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        </button>
                        <div id="dropdownLogout"
                             class="bg-gray-900 rounded shadow-md absolute mt-8 top-0 right-0 min-w-full overflow-auto z-30 logoutdropdown-content hidden"
                             @mouseleave="showdropdown('dropdownLogout')"
                        >
                            <ul class="list-reset w-full">
                                <li>
                                    <hr class="border-t mx-2 border-gray-400">
                                </li>
                                <li><a href="#"
                                       class="px-4 py-2 block text-gray-100 hover:bg-gray-800 no-underline hover:no-underline"
                                       href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                   document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>


                    <div class="block lg:hidden pr-4">
                        <button id="nav-toggle"
                                class="flex items-center px-3 py-2 border rounded text-gray-500 border-gray-600 hover:text-gray-100 hover:border-teal-500 appearance-none focus:outline-none">
                            <svg class="fill-current mb-2 font-medium leading-tight text-2xl w-3 h4- w-4" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <title>
                                    Menu</title>
                                <path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z"/>
                            </svg>
                        </button>
                    </div>
                </div>

            </div>


            <div
                class="w-full flex-grow lg:flex lg:items-center lg:w-auto hidden lg:block mt-2 lg:mt-0 bg-gray-900 z-20"
                id="nav-content">
                <ul class="list-reset lg:flex flex-1 items-center px-4 md:px-0">
                    <li class="mr-6 my-2 md:my-0">
                        <a :class="url == 'admin' ? 'text-blue-400' :'text-gray-500'" href="{{url('admin/')}}"
                           class="block py-1 md:py-3 pl-1 align-middle no-underline hover:text-gray-100 border-b-2 border-blue-400 hover:border-blue-400">
                            <i class="fas fa-home fa-fw mr-3 "></i><span
                                class="pb-1 md:pb-0 text-sm">Dashboard</span>

                        </a>
                    </li>

                    <li class="mr-6 my-2 md:my-0">
                        <a href="{{url('admin/users')}}" :class="url == 'users' ? 'text-blue-400' :'text-gray-500'"
                           class="block py-1 md:py-3 pl-1 align-middle no-underline hover:text-gray-100 border-b-2 border-gray-900  hover:border-green-400">
                            <i class="fas fa-chart-area fa-fw mr-3"></i><span
                                class="pb-1 md:pb-0 text-sm">Users</span>

                        </a>
                    </li>
                    <li class="mr-6 my-2 md:my-0">
                        <a href="{{url('admin/cases')}}" :class="url == 'cases' ? 'text-blue-400' :'text-gray-500'"
                           class="block py-1 md:py-3 pl-1 align-middle no-underline hover:text-gray-100 border-b-2 border-gray-900  hover:border-green-400">
                            <i class="fas fa-chart-area fa-fw mr-3"></i><span
                                class="pb-1 md:pb-0 text-sm">Cases</span>

                        </a>
                    </li>
                    <li class="mr-6 my-2 md:my-0">
                        <a href="{{url('admin/newsletter')}}" :class="url == 'newsletter' ? 'text-blue-400' :'text-gray-500'"
                           class="block py-1 md:py-3 pl-1 align-middle no-underline hover:text-gray-100 border-b-2 border-gray-900  hover:border-green-400">
                            <i class="fas fa-chart-area fa-fw mr-3"></i><span
                                class="pb-1 md:pb-0 text-sm">Newsletter</span>

                        </a>
                    </li>
                    <li class="mr-6 my-2 md:my-0">
                        <a href="{{url('')}}"
                           class="block py-1 text-gray-500 md:py-3 pl-1 align-middle no-underline hover:text-gray-100 border-b-2 border-gray-900  hover:border-red-400">
                            <i class="fa fa-wallet fa-fw mr-3"></i><span
                                class="pb-1 md:pb-0 text-sm">Metag-Analyze Home</span>
                        </a>
                    </li>
                </ul>


            </div>

        </div>
    </nav>

    <div class="container mx-auto mx-auto mx-auto mx-auto mt-2">

        @if(session()->has('message'))
            <b-notification
                aria-close-label="Close notification"
                type="is-danger"
                role="alert"
            >
                {{session()->get('message')}}
            </b-notification>

        @endif


        <div class="w-full">
                @yield('content')
        </div>
    </div>
</div>
@yield('pagespecificscripts')

</body>

