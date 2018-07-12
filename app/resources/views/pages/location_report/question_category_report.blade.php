@extends('layouts.default')
@section('head-content')
    <script type="text/javascript" src="/js/jquery-3.1.1.js"></script>
    <script type="text/javascript" language="JavaScript" src="/js/question_category_mobile_menu.js">
    </script>
    <script type="text/javascript" language="JavaScript" src="/js/smart_menu.js">
    </script>
    <script type="text/javascript" language="JavaScript" src="/js/utils.js"></script>
    <script type="text/javascript" language="JavaScript" src="/js/question_explanation.js"></script>
    <script>
        $(document).ready(initQuestionExplanationLinks);
    </script>
@stop
@section('content')
    <div class="location-reporting">
        @if ( $location === null )
            <p>The specified location couldn't be found</p>
        @else
            <div class="menu">
                <h3 class="text-center">Location Ratings</h3>
                <div class="question-categories">
                    @include('includes.question_categories')
                </div>
            </div>
            <div class="ratings">
                <h3 style="margin-left: 20px">
                    <a href="/location/report/{{ $location->id }}">{{ $location->name }}</a>
                </h3>
                @include('includes.rate_report_toggle',
                    array(
                        'location_id' => $location->id,
                        'question_categories' => $categories,
                        'question_category' => $category,
                        'question_category_id' => 1,
                        'is_reporting' => true,
                        'base_url' => '/location/report/'
                    ))
                @include('pages.location_report.questions',
                    array(
                        'question_category' => $category,
                        'location_id' => $location->id
                    ))
            </div>
        @endif
    </div>
@stop