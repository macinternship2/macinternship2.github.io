@extends('layouts.default', ['body_class' => 'nav-profile'])
@section('head-content')
  <link href="/css/jquery/jquery-ui.css" rel="stylesheet" type="text/css">
  <script src="/css/jquery/external/jquery/jquery.js"></script>
  <script src="/css/jquery/jquery-ui.js"></script>
  <script src="/js/profile.js"></script>
  <script src="/js/profile_save_button.js"></script>
  <script src="/js/profile_save_prompt.js"></script>
@stop
@section('content')
<div class="profile row">
	<div class="col-md-3 col-sm-4 col-xs-12">
		@if ($has_profile_photo)

			<div class="photo-display">

			    <p class="remove-photo"><a href="/profile-photo-delete">Remove Photo</a></p>
				<div id="profile-photo-rotate" onclick="rotateImage()"><i class="fa fa-repeat fa-4x"></i></div>
				<div class="photo-changer" onclick="selectImageFile()">
					<div class="uploaded-photo">
					</div>
					<div class="progress-element"></div>
					<p>Change Photo</p>
				</div>
			</div>
		@else

        <div class="photo-display" onclick="selectImageFile()">
            <div class="user-icon">
                <div><i class="fa fa-user"></i></div>
                <p>Choose File</p>
            </div>
        </div>
        @endif
		<form id="photo-upload" method="post" action="/profile-photo-upload" enctype="multipart/form-data">
			{!! csrf_field() !!}
            <input class="hidden-uploader" type="file" name="profile_photo" onchange="upload()">
		</form>
 	</div>
    <div class="col-md-9 col-sm-8 col-xs-12">
		@if ( $is_internal_user )
			<a class="internal-dashboard-link" href="/dashboard"><em class="fa fa-gears"></em></a>
		@endif
        <h1>{{ $user->first_name.' '.$user->last_name }}</h1>
		<form id="profileForm" method="post" action="/profile">
			{!! csrf_field() !!}
			@include('pages.validation_messages', array('errors'=>$errors))
			<h2>Personal</h2>
			<div class="box">
				<div class="form-group">
					<div class="row">
						<div class="col-sm-4 col-xs-5">
							<label for="first_name">First Name</label>
						</div>
						<div class="col-sm-8 col-xs-7">
							<input class="form-control" id="first_name" name="first_name" value="{{ $user->first_name }}">
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col-sm-4 col-xs-5">
							<label for="last_name">Last Name</label>
						</div>
						<div class="col-sm-8 col-xs-7">
							<input class="form-control" id="last_name" name="last_name" value="{{ $user->last_name }}">
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col-sm-4 col-xs-5">
							<label for="email">Email</label>
						</div>
						<div class="col-sm-8 col-xs-7">
							<input class="form-control" id="email" name="email" type="email" required readonly value="{{ $user->email }}">
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col-sm-4 col-xs-5">
							<label>Password</label>
						</div>
						<div class="col-sm-8 col-xs-7">
							<a class="btn btn-default" href="/change-password">Change password</a>
						</div>
					</div>
				</div>
			</div>

			<h2>Home</h2>
			<div class="box">
				<div class="form-group">
					<div class="row">
						<div class="col-sm-4 col-xs-5">
							<label for="home_country_id">Country</label>
						</div>
						<div class="col-sm-8 col-xs-7">
							<select class="form-control" id="home_country_id" name="home_country_id">
								<option value="">-- Select Country --</option>
								@foreach ($countries as $country)
									<option value="{{ $country->id }}"
									@if ( $user->home_country_id === $country->id )
									selected
									@endif
									@if ( !in_array($country->id, $enabled_country_ids) )
									disabled
									@endif
									>{{ $country->name }}</option>
								@endforeach
							</select>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col-sm-4 col-xs-5">
							<label for="province">State/Province</label>
						</div>
						<div class="col-sm-8 col-xs-7">
							<input list="regions" class="form-control" id="home_region" name="home_region" value="{{ $user->home_region }}">
							<datalist id="regions"></datalist>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col-sm-4 col-xs-5">
							<label for="home_city">City</label>
						</div>
						<div class="col-sm-8 col-xs-7">
							<input class="form-control" id="home_city" name="home_city" value="{{ $user->home_city }}">
						</div>
					</div>
				</div>
			</div>

			<h2>Search Location</h2>
			<div class="box">
				<div class="form-group">
					<div class="row">
						<div class="col-sm-4 col-xs-5">
							<label for="location_search_text">Location</label>
						</div>
						<div class="col-sm-8 col-xs-7">
							<input class="form-control" id="location_search_text" name="location_search_text"
								value="{{ $user->location_search_text }}"
								placeholder="{{ $address_default }}">
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col-sm-4 col-xs-5">
							<label for="distance">Distance (km)</label>
						</div>
						<div class="col-sm-8 col-xs-7">
							<!-- from 10 meters to the full radius of Earth -->
							<input class="form-control" id="distance" name="search_radius_km"
								type="number"
								step="0.01"
								min="0.01"
								max="{{ $max_search_radius_km }}"
								value="{{ $user->search_radius_km }}">
						</div>
					</div>
				</div>
			</div>

			<h2>My Accessibility Requirements </h2>
			<div class="box accessibility-interests">
				<div class="checkbox">
					<label>
					@if ( $user->uses_screen_reader )
					<input type="checkbox" name="uses_screen_reader" checked>
					@else
					<input type="checkbox" name="uses_screen_reader">
					@endif
					Screen Reader</label>
				</div>
				<div id="accordion">

				@foreach ($question_categories as $category)

					<h3>{{ $category->name }}</h3>
					<div class="category">
						<div class="checkbox">
							<button type="button" class="btn btn-lg btn-primary select-all">Select All</button>
						</div>
						<div class="questions">
							@foreach ($category->questions()->get() as $question)
								<div class="checkbox">
									<label>
										@if ($user->isQuestionRequired($required_questions, $question->id))
										<input name="question_{{ $question->id }}" type="checkbox" checked>
										@else
										<input name="question_{{ $question->id }}" type="checkbox">
										@endif
									</label>
									<div>
									{!! $question->question_html !!}
									</div>
								</div>
							@endforeach
						</div>
					</div>

				@endforeach

				</div>

			</div>

			<h2>My Reviews</h2>
			<div class="box rewards">
				<div>
					<a class="btn btn-default" href="/reviewed-locations">My Reviews({{ $num_reviews }})</a>
					<a class="btn btn-default" href="/locations-added-by-me">My Locations({{ $num_locations_added_by_me }})</a>
				</div>
			</div>

			<div class="text-right">
				<button type="submit" id="submitButton" class="btn btn-lg btn-primary save-button" disabled>Save Profile</button>
		   </div>
	 </form>

	</div>
</div>

@stop
