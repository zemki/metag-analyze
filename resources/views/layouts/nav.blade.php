<div class="relative flex flex-col min-h-full print:hidden">
    <!-- Navbar -->
    <nav class="flex-shrink-0 bg-blue-500">
        <div class="px-2 mx-auto max-w-7xl sm:px-4 lg:px-8">
            <div class="relative flex items-center justify-between h-16">
                <!-- Logo section -->
                <div class="flex items-center px-2 lg:px-0 xl:w-64">
                    <div class="flex-shrink-0">
                        <a class="flex items-center justify-center text-white align-middle hover:text-gray-200"
                            href="{{url('/')}}" title="Home button">
                            <img class="w-auto h-10" src="{{config('utilities.base64logo')}}" alt="MeSort Logo">
                            <p class="px-3 py-2 text-sm font-medium text-gray-200 rounded-md hover:text-white">Home</p>
                        </a>
                    </div>
                </div>
                <div class="hidden lg:block lg:w-80">
                    <div class="flex items-center justify-end">

                        @if(Request::is('/'))

                        {{-- <notification-bell></notification-bell> --}}

                        @endif
                        @if(Auth::user()->hasRole('admin'))
                        <a target="_blank"
                            class="px-3 py-2 text-sm font-medium text-gray-200 rounded-md hover:text-white"
                            href="{{url('translations')}}">
                            {{ __('Translations') }}

                        </a>
                        @endif
                        <div class="flex">
                            <a title="{{__('MeSort User Manuals')}}" href="https://mesoftware.org/index.php/mesort/"
                                class="px-3 py-2 text-sm font-medium text-gray-200 rounded-md hover:text-white">Manuals</a>
                        </div>

                        <div class="flex shrink-0">
                            <span class="sr-only">{{__('Your Email')}}</span>
                            <span
                                class="px-3 py-2 text-sm font-medium text-gray-200 rounded-md cursor-pointer pointer-events-none hover:text-gray-200">{{
                Auth::user()->email }}</span>
                        </div>
                        <div class="relative z-50 flex-shrink-0 ml-4">
                            <div>

                                <button ref="usermenu" @click="showdropdown('dropdownLogout')"
                                    @mouseover="showdropdown('dropdownLogout')" type="button"
                                    class="flex text-sm rounded-full bg-sky-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-sky-500 focus:ring-white"
                                    id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                    <span class="sr-only">Open user menu</span>
                                    <img class="w-8 h-8 rounded-full" src="{{\Gravatar::get(Auth::user()->email)}}"
                                        alt="">
                                </button>
                            </div>

                            <div id="dropdownLogout"
                                class="absolute right-0 hidden w-48 py-1 mt-2 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button"
                                tabindex="-1">
                                @if(auth()->user()->isAdmin())
                                <a title="User Profile"
                                    class="flex w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    role="menuitem" tabindex="-1" id="user-menu-item-0"
                                    href="{{ route('userprofile') }}">{{__('User
                  Profile')}}</a>
                                @endif
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                </form>
                                <a title="Logout" class="flex w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    role="menuitem" tabindex="-1" id="user-menu-item-1" href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">{{__('Log out')}}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
</nav>

</header>