var promises_remaining = [];
var comment_saving_promise = undefined;

function blockUnload()
{
	$(window).bind("beforeunload", beforeUnloadCallback);
}

function unblockUnload()
{
	$(window).unbind("beforeunload", beforeUnloadCallback);
}

function processPromise(promise) {
	if ( promises_remaining.length === 0 ) {
		blockUnload();
	}
	promises_remaining.push(promise);
	// Remove the promise when it either succeeds or fails.
	promise.always(function() {
		var index = promises_remaining.indexOf(promise);
		if( index !== -1 ) {
			promises_remaining.splice(index, 1);
		}
		if ( promises_remaining.length === 0 ) {
			unblockUnload();
		}
	});
}

function setComment(comment)
{
	// setComment tries to keep at most 1 comment-related API call active at a time.
	// This prevents overloading the server with wasteful HTTP requests and controls sequence of the requests so they don't get processed out of order.
	if( comment_saving_promise ) {
		return;
	}
	comment_saving_promise = $.ajax({
		'method': 'PUT',
		'url': '/location-rating/comment',
		'data': {
			'location_id': location_id,
			'question_category_id': question_category_id,
			'comment': comment,
			'_token': csrf_token
		}
	});
	processPromise(comment_saving_promise);
	comment_saving_promise.always(function() {
		comment_saving_promise = undefined;
		var new_comment = getCommentElement().val();
		if ( comment !== new_comment ) {
			setComment(new_comment);
		}
	});
}

function removeAnswer(question_id)
{
	processPromise($.ajax({
		'method': 'DELETE',
		'url': '/location-rating/answer',
		'data': {
			'location_id': location_id,
			'question_id': question_id,
			'_token': csrf_token
		}
	}));
}

function saveAnswerChange(question_id, answer_text)
{
	// Turn the answer text into a number 0, 1, 2.
	answer_text = answer_text.toLowerCase().trim();
	var answer_value_map = {"no": 0, "yes": 1, "n/a to location": 2, "i didn't look": 3};
	if ( answer_value_map[answer_text] !== undefined )
	{
		answer_text = answer_value_map[answer_text];
	}
	processPromise($.ajax({
		'method': 'PUT',
		'url': '/location-rating/answer',
		'data': {
			'location_id': location_id,
			'question_id': question_id,
			'answer': answer_text,
			'_token': csrf_token
		}
	}));
}

function answerClicked()
{
	var $this = $(this);
	var $question_element = $this.closest('.questions > div.row');
	var question_id = parseInt($question_element.data('question-id'));
	if ( $this.hasClass('selected') )
	{
		$this.removeClass('selected');
		removeAnswer(question_id);
		return;
	}

	saveAnswerChange(question_id, $this.text());
	$this.addClass('selected');
	var $sibling_answers = $question_element.find('.answers > div');
	$.each($sibling_answers, function(index, sibling_answer) {
		if ( sibling_answer !== $this[0] )
		{
			$(sibling_answer).removeClass('selected');
		}
	});
}

function getCommentElement()
{
	return $('.rate textarea.clean');
}

function saveCommentFromUI()
{
	setComment(getCommentElement().val());
}

function beforeUnloadCallback(event)
{
	return 'Changes are still being saved.  Try again shortly.';
}

function navigateAfterAllPromisesResolve(href)
{
	unblockUnload();
	$.when.apply($, promises_remaining).then(function() {
		window.location.href = href;
	});
}

function processAElements()
{
	var $a_elements = $('a[href]');
	$.each($a_elements, function(index, a_element) {
		var href = $(a_element).attr('href');
		var new_href = 'javascript:navigateAfterAllPromisesResolve("' + href + '")';
		$(a_element).attr('href', new_href);
	});
}

function submitCallback(event)
{
	unblockUnload();
	var form = this;
	$(form).unbind('submit');
	if ( promises_remaining.length > 0 ) {
		event.preventDefault();
		$.when.apply($, promises_remaining).then(
			function() {
				$('.submit input[type="submit"]').click();
			}
		);
	}
}

function delaySubmit()
{
	$('.submit form').submit(submitCallback);
}

function initAnswerBindings()
{
	var $answer_buttons = $('.questions .answers > div:not(.always-required, .disabled)');
	var $comment = getCommentElement();

	$answer_buttons.bind('click', answerClicked);
	$comment[0].setComment = setComment;
	$comment[0].getCommentElement = getCommentElement;
	$comment.bind('keyup blur', saveCommentFromUI);
	processAElements();
	delaySubmit();
}

$(document).ready(initAnswerBindings);