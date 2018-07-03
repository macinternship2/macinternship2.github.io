<?php namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DB;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $faq_items = DB::table('faq_item')->get();
        return view('pages.faq', [
            'faq_items' => $faq_items
        ]);
    }
}
