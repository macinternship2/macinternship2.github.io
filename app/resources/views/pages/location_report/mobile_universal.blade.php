<div>
    @if($location['overall_universal_ratings'] === 0)
        <div class="text-center">
            <h3><b>{{$location->name}}</b></h3>
            <div class="text-center" style="margin-top: 40px"><i class="fa fa-info-circle fa-4x"></i></div>
            <h4 class="text-muted">
                This place is not rated yet. <br/> If you have visited this place then help others by rating this place
            </h4>
            <a href="/location/rating/{{$location->id}}">
                <button class="btn btn-default btn-lg" style="margin-top: 40px">
                    <i class="fa fa-check-square-o"></i> Be the first to rate this location
                </button>
            </a>
            @if (!Auth::check())
                <p class="text-muted" style="margin-top: 24px">
                    For rating place you have to login.
                    If you are not then you can <a href="/signin">Sign in</a>  or <a href="/signup">Sign up</a>.
                </p>
            @endif
        </div>
    @endif
    @if($location['overall_universal_ratings'] > 0)
        <div style="padding-right: 15px">
            <div class="row">
                <div class="col-xs-12">
                    <div class="row" style="margin-top: 10px">
                        <div class="col-xs-2">
                            @include('pages.components.pie_graph', [
                                'percentage' => $rating_system === "personal" ? $location['overall_personal_ratings'] : $location['overall_universal_ratings']
                            ])
                        </div>
                        <div class="col-xs-10">
                            <div class="row">
                                <div class="col-xs-8">
                                    <h4 style="margin-top: 20px"><b>Total Accessibility</b></h4>
                                </div>
                                <div class="col-xs-2">
                                    <h3><b>{{$location['overall_universal_ratings']}}%</b></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr style="margin-top: 4px; margin-bottom: 4px">
                </div>
            </div>
            @foreach($location['detailed_universal_ratings'] as $category => $detailed_universal_rating)
                <div class="row">
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xs-2">
                                @include('pages.components.pie_graph', [
                                    'percentage' => $detailed_universal_rating
                                ])
                            </div>
                            <div class="col-xs-10">
                                <div class="row">
                                    <div class="col-xs-8">
                                        <h4 style="margin-top: 20px"><b>{{$category}}</b></h4>
                                        <a href="/location/report/{{$location->id}}/{{implode("-", explode(" ", strtolower($category)))}}">
                                            <p style="margin-top: -10px">See more details</p>
                                        </a>
                                    </div>
                                    <div class="col-xs-4">
                                        <h3><b>{{$detailed_universal_rating}}%</b></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr style="margin-top: 4px; margin-bottom: 4px">
                    </div>
                </div>
            @endforeach
            @if(!Auth::check())
                <div class="row text-center" style="margin-top: 12px">
                    <div class="col-md-12 col-lg-12">
                        <p>
                            <a href="/signin">Sign In</a>
                            or
                            <a href="/signup">Sign Up</a>
                            to see your personal accessibility ratings.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>