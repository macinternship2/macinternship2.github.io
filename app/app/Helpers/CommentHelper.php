<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Session;

class CommentHelper
{
    /**
     * Builds the instance of this class.
     * @return \Illuminate\Foundation\Application|mixed
     */
    public static function build()
    {
        return app(CommentHelper::class);
    }

    /**
     * Helper function to get the comment that is stored in the session.
     * @param $locationId
     * @param $categoryId
     * @return string
     */
    public function getComment($locationId, $categoryId)
    {
        $key = 'comments_'.$locationId;
        if (Session::has($key)) {
            if (in_array($categoryId, array_keys(Session::get($key)))) {
                return array_get(Session::get($key), $categoryId);
            }
        }
        return '';
    }

    /**
     * Sets the comment in session and returns the status.
     * @param $locationId
     * @param $categoryId
     * @param $comment
     * @return bool
     */
    public function setComment($locationId, $categoryId, $comment)
    {
        $key = 'comments_'.$locationId;
        if (Session::has($key)) {
            $existing = count(Session::get($key)) > 0 ? Session::get($key) : [];
            $existing = array_add($existing, $categoryId, $comment);
            Session::put($key, $existing);
            return true;
        }
        return false;
    }
}
