@if($breadcrumb)
    <div class="sm:hidden md:hidden lg:flex xl:flex">
        <nav class="flex flex-wrap list-reset pt-3 pb-3 py-4 px-4 mb-4 bg-gray-100 rounded has-succeeds-separator is-small" aria-label="breadcrumbs">

                <div class="inline"><a href="#">Metag ></a></div>

                @foreach($breadcrumb as $url => $title)
                    <div @if($url==='#')    class="inline" @endif><a href="{{ $url }}">&nbsp; {{ $title }} ></a></div>
                @endforeach


        </nav>
    </div>
@endif
