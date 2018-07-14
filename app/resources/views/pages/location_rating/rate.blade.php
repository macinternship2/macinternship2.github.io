@extends('layouts.default')
@section('head-content')
	<script type="text/javascript" src="/js/jquery-3.1.1.js"></script>
	<script type="text/javascript" language="JavaScript" src="/js/smart_menu.js">
    </script>
	<script type="text/javascript" language="JavaScript" src="/js/location_rating.js">
    </script>
	<script type="text/javascript" language="JavaScript" src="/js/question_category_mobile_menu.js">
    </script>
    <script type="text/javascript" language="JavaScript" src="/js/utils.js"></script>
    <script type="text/javascript" language="JavaScript" src="/js/question_explanation.js"></script>
@stop
@section('content')
	<div class="location-rating">
	@if ( $location === null )
		<p>The specified location couldn't be found</p>
	@else
		<div class="menu">
			<h3 class="text-center">Rate Location</h3>
			<div class="question-categories">
				@include('includes.question_categories', [
				'base_url' => '/location/rating'
				])
			</div>
			<div class="submit">
				<form method="post" action="/location/rating/{{$location->id}}">
					{!! csrf_field() !!}
					<input type="submit" class="clean" value="Submit">
				</form>
			</div>
		</div>
		<div class="rate">
			<h3 style="margin-left: 20px"><a href="/location/report/{{ $location->id }}">{{ $location->name }}</a></h3>
				@include('includes.rate_report_toggle',
					array(
						'location_id' => $location->id,
						'question_categories' => $categories,
						'question_category' => $category,
						'question_category_id' => $category->id,
						'base_url' => '/location-rating/',
						'is_reporting' => false
					))
			@if ( $category === null )
				@include('pages.location_rating.introduction')
			@else
				@include('pages.location_rating.questions',
					array(
						'question_category' => $category,
						'location' => $location,
						'uses_screen_reader' => $uses_screen_reader,
						'answer_helper' => \App\Helpers\AnswerHelper::build(),
						'comment_helper' => \App\Helpers\CommentHelper::build()
					))
			@endif
		</div>
	@endif
	</div>
@stop