@extends('layouts.default', ['body_class' => 'nav-home-page'])
@section('footer-content')
	<script src="/js/jquery-3.1.1.js"  type="text/javascript"></script>
	<script src="/js/home.js" type="text/javascript"></script>
	<script src="/js/hover_text.js" type="text/javascript"></script>
	<script type="text/javascript" async defer
		src="//maps.googleapis.com/maps/api/js?key={{ $google_map_api_key }}&amp;callback=initMap">
	</script>
@stop
@section('content')
<div class="home-page">
	<div class="row">
		<div class="col-lg-5">
			<div class="intro">
				<div class="title m-b-md">
					<h1>AccessLocator</h1>
				</div>

				<div>
					<p class="slogan">"Your Personalized Access to the World"</p>
					<p>Find accessibility information on locations rated by real users and make your own ratings!</p>
				</div>
			</div>
		</div>
		<div class="col-lg-7">
			<div class="home-inputs">
				@if (!$is_authenticated)
				<div class="signin-signup">
					<a href="/signin">
						<table>
							<tr>
								<td class="user-icon">
									<i class="fa fa-user"></i>
								</td>
								<td class="text-center sign-in-sign-up">
									Sign in / Sign up
								</td>
							</tr>
						</table>
					</a>
				</div>
				@endif
				<div>
					<div id="map"></div>
				</div>
				<div class="search">
					{{ csrf_field() }}
					<form role="search" action="/location-search">
						<datalist id="location_search_options">
							@foreach ($location_search_options as $option)
								<option value="{{ $option->content }}">
							@endforeach
						</datalist>
						<input
							list="location_search_options"
							class="form-control"
							name="address"
							id="address"
							value="{{ $address_value }}"
							placeholder="{{ $address_default }}"
							title="Address">
						<table>
							<tr>
								<td>
									<input name="keywords" type="text" placeholder="Search by keyword(s)" class="form-control">
								</td>
								<td class="search-button">
									<button class="btn btn-default" type="submit">
										<i class="fa fa-search"></i>
									</button>
								</td>
							</tr>
						</table>
					</form>
				</div>
				<div class="location-tags">
					<div class="row">
					@foreach ( $location_tags as $key => $value )

						<div class="location-tag col-sm-4 col-xs-6">
							<a href="/location-search?location_tag_id={{ $value->id }}" title="{{ $value->description }}">
							{{ $value->name }}
							</a>
						</div>

					@endforeach
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@stop
