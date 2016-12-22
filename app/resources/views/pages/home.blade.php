@extends('layouts.default')
@section('content')

<div class="home-page">
	<div class="row">
		<div class="col-lg-5">
			<div class="intro">
				<div class="title m-b-md">
					<h1>AccessLocator</h1>
				</div>

				<div>
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
				<div class="search">
					<form role="search" action="/search-by-keywords">
						<input
							class="form-control"
							name="address"
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
							<a href="/search-by-tag/{{ $value->id }}" title="{{ $value->description }}">
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