<h2 style="margin-left: 20px">{{ $category->name }}</h2>

<div class="container-fluid questions">
	<div class="questions">
		@foreach ( $category->questions()->orderBy('order')->get() as $question )
			<div class="row" data-question-id="{{ $question->id }}">
				<div class="col-md-6 col-xs-12">
					{!! $question->question_html !!}
					@if ($question->explanation)
						@include('pages.components.question_explanation_link',
						array(
							'location_id' => $location->id,
							'question_id' => $question->id
							)
						)
					@endif
				</div>
				<div class="col-md-6 col-xs-12">

					<div class="row answers">
						<div class="col-xs-2 @if ( $answer_helper->getAnswer($location->id, $question->id) === 1 )
							selected
						@endif">
						<div>
							Yes
							</div>
						</div>
						<div class="col-xs-2 @if ( $answer_helper->getAnswer($location->id, $question->id) === 0 )
							selected
						@endif">
							<div>
							No
							</div>
						</div>
						<div class="col-xs-4 @if ( $answer_helper->getAnswer($location->id, $question->id) === 2 )
							selected
						@endif
						@if ( $question->is_always_required )
							always-required
						@endif
						" title="Location does not require this. For example, one level building does not require an elevator, so check 'N/A to Location' for the Elevator category.">
							<div>
							N/A to Location
							</div>
						</div>
						<div class="col-xs-4 @if ( $answer_helper->getAnswer($location->id, $question->id) === 3 && !$uses_screen_reader)
							selected
						@endif
						" title="I didn't look to see if this criteria is met">
							<div>
							I Didn't Look
							</div>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
	<textarea class="form-control" placeholder="Comment on {{ $question_category->name }} at {{ $location->name }}">
		{{ $comment_helper->getComment($location->id, $category->id) }}
	</textarea>
	<div class="pull-right">
		@if ( $next_category !== null)
			<a class="clean" href="/location/rating/{{ $location->id }}/{{implode('-', explode(" ",strtolower($next_category->name)))}}">Next</a>
		@else
			<form method="post" action="/location/rating/{{$location->id}}">
				{!! csrf_field() !!}
				<input type="submit" class="clean" value="Submit">
			</form>
		@endif
	</div>
</div>