<h2>{{ $question_category->name }}</h2>

<div>
	<div class="questions">
		@foreach ( $question_category->getSortedQuestions() as $question )
			<div class="row" data-question-id="{{ $question->id }}">
				<div class="col-md-6 col-xs-12">
					{!! $question->question_html !!}
				</div>
				<div class="col-md-6 col-xs-12">
					<div class="row answers">
						<div class="col-xs-2 @if ( $answer_repository->getAnswerForQuestion($question->id) === 1 )
							selected
						@endif">
							<div>
							Yes
							</div>
						</div>
						<div class="col-xs-2 @if ( $answer_repository->getAnswerForQuestion($question->id) === 0 )
							selected
						@endif">
							<div>
							No
							</div>
						</div>
						<div class="col-xs-4 @if ( $answer_repository->getAnswerForQuestion($question->id) === 2 )
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
						<div class="col-xs-4 @if ( $answer_repository->getAnswerForQuestion($question->id) === 3 &&
						!($question->id === 3 && !$uses_screen_reader) )
							selected
						@endif
						@if ( $question->id === 3 && !$uses_screen_reader )
							disabled
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
	<textarea class="clean" placeholder="Comment on {{ $question_category->name }} at {{ $location->name }}"
	>{{ $answer_repository->getComment() }}</textarea>
	<div class="pull-right">
		@if ( $next_question_category_id )
		<a class="clean" href="/location-rating/{{ $location->id }}/{{ $next_question_category_id }}">Next</a>
		@else
		@include('pages.location_rating.submit',
					array(
						'location_id' => $location->id
					))
		@endif
	</div>
</div>