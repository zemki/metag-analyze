@if($breadcrumb)
<div class="sm:hidden md:hidden lg:flex xl:flex">
		<nav class="breadcrumb has-succeeds-separator is-small" aria-label="breadcrumbs">
			<ul>
				<li><a href="#">Metag</a></li>

				@foreach($breadcrumb as $url => $title)
					<li @if($url==='#')	class="is-active" @endif><a href="{{ $url }}">{{ $title }}</a></li>
				@endforeach

			</ul>
		</nav>
</div>
@endif
