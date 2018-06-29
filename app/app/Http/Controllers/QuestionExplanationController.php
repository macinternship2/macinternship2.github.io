<?php namespace App\Http\Controllers;

use DB;
use Request;
use View;
use App\BaseUser;

class QuestionExplanationController extends Controller
{

    public function getExplanation($question_id)
    {
        $question = DB::table('question')->where('id', '=', $question_id)->first(['question_html', 'explanation']);
        if (!$question) {
            abort(404, 'Specified question not found.');
        }
        $view = View::make('pages.questions.question_explanation_dialog_content', [
                'question_html' => $question->question_html,
                'explanation' => $question->explanation
            ]);
        $question_html = $view->render();
        return response()
            ->json(['html' => $question_html]);
    }

    public function isUsingScreenReader()
    {
        if (BaseUser::isSignedIn()) {
            $result = BaseUser::getDbUser()->uses_screen_reader;
        } else {
            $result = false;
        }

        return response()->json($result ? true : false);
    }

    public function show($question_id)
    {
        $question = DB::table('question')->find($question_id);
        if (!$question) {
            abort(404, 'Specified question not found.');
        }
        $redirect_url = Request::server('HTTP_REFERER');

        return view('pages.questions.question_explanation', [
            'question' => $question,
            'redirect_url' => $redirect_url
        ]);
    }
}
