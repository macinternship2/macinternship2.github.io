<?php

namespace App\Helpers;

use App\Question;
use Illuminate\Support\Facades\Session;

class AnswerHelper
{
    /**
     * Builds the instance of this class.
     * @return \Illuminate\Foundation\Application|mixed
     */
    public static function build()
    {
        return app(AnswerHelper::class);
    }

    /**
     * Helper function to get the answers of the user.
     * @param $locationId
     * @param $questionId
     * @return string
     */
    public function getAnswer($locationId, $questionId)
    {
        $key = 'answers_'.$locationId;
        if (Session::has($key)) {
            if (in_array($questionId, array_keys(Session::get($key)))) {
                return (int) array_get(Session::get($key), $questionId);
            }
        }
        return '';
    }

    /**
     * Set the answer and return the status.
     * @param $locationId
     * @param $questionId
     * @param $answer
     * @return bool
     */
    public function setAnswer($locationId, $questionId, $answer)
    {
        if ($answer === 2) {
            $isCompulsoryRequired = Question::query()->find($questionId)->is_always_required;
            if ($isCompulsoryRequired) {
                return false;
            }
        }

        $key = 'answers_'.$locationId;
        if (Session::has($key)) {
            $existing = count(Session::get($key)) > 0 ? Session::get($key) : [];
            $existing = array_add($existing, $questionId, $answer);
            Session::put($key, $existing);
            return true;
        } else {
            Session::put($key, [ $questionId => $answer ]);
            return true;
        }
    }

    public function removeAnswer($locationId, $questionId)
    {
        $key = 'answers_'.$locationId;
        if (Session::has($key)) {
            if (in_array($questionId, array_keys(Session::get($key)))) {
                $remaining = array_except(Session::get($key), $questionId);
                Session::put($key, $remaining);
                return true;
            }
        }
        return false;
    }
}
