<h2 style="margin-left: 20px">{{ $question_category->name }}</h2>

<div>
	<div class="questions" style="margin-top: 20px">
		@foreach ( $question_category->questions()->get() as $question )
			<div>
				<div class="question-html">
				{!! $question->question_html !!}
				@if ($question->explanation)
						@include('pages.components.question_explanation_link',
						array(
							'question_id' => $question->id
							)
						)
				@endif
				</div>
				<div class="stats">
					<div class="percentage">
						@if (!isset($question->ratings[$location->id]))
							No ratings
						@else
							{{ $question->ratings[$location->id]->avg('answer_value')}}%
						@endif
					</div>
					<div class="user-count">
						@if (!isset($question['all_ratings'][$location->id]))
							<p>0 rating</p>
						@else
							@if(collect($question->all_ratings[$location->id]->flatten())->whereIn('question_id', $question->id)->isEmpty())
								<p> 0 ratings</p>
							@else
								<p> {{collect($question->all_ratings[$location->id]->flatten())->whereIn('question_id', $question->id)->count()}} rating(s)</p>
							@endif
						@endif
					</div>
				</div>
			</div>
		@endforeach
	</div>
	<div class="comments" style="margin-top: 20px">
		<h2>Comments</h2>
		@if ( count($comments) === 0 )
			<p>There are no comments for this category.</p>
		@else
			@foreach ( $comments as $comment )
				<div class="comment" style="margin-top: 20px">
					<div class="comment-header">
						{{ $comment->user->first_name }}
						{{ $comment->user->last_name }}
						said,

						<span class="pull-right">
						{{ \Carbon\Carbon::parse($comment->when_submitted)->toDayDateTimeString() }}
						</span>
					</div>
					<div class="comment-content">
						{{ $comment->content }}
					</div>
				</div>
			@endforeach
		@endif
	</div>
</div>