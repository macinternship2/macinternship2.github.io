<div style="overflow-y: scroll;height: 480px; padding-right: 10px">
    @if($location['overall_personal_ratings'] === 0)
        <div class="text-center">
            <h3><b>{{$location->name}}</b></h3>
            <div class="text-center" style="margin-top: 40px"><i class="fa fa-info-circle fa-4x"></i></div>
            <h4 class="text-muted">
                This place is not rated yet. <br/> If you have visited this place then help others by rating this place
            </h4>
            <button class="btn btn-default btn-lg" style="margin-top: 40px">
                <i class="fa fa-check-square-o"></i> Be the first to rate this location
            </button>
            <p class="text-muted" style="margin-top: 24px">
                For rating place you have to login.
                If you are not then you can <a href="/signin">Sign in</a>  or <a href="/signup">Sign up</a>.
            </p>
        </div>
    @endif
    @if($location['overall_personal_ratings'] > 0)
        <div style="padding-right: 15px">
            <div class="row">
                <div class="col-md-12">
                    <div class="row" style="margin-top: 10px">
                        <div class="col-md-2">
                            @include('pages.components.pie_graph', [
                                'percentage' => $location['overall_personal_ratings']
                            ])
                        </div>
                        <div class="col-md-10">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4 style="margin-top: 20px"><b>Total Accessibility</b></h4>
                                </div>
                                <div class="col-md-2">
                                    <h3><b>{{$location['overall_personal_ratings']}}%</b></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr style="margin-top: 4px; margin-bottom: 4px">
                </div>
            </div>
            @foreach($location['detailed_personal_ratings'] as $category => $detailed_personal_rating)
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-2">
                                @include('pages.components.pie_graph', [
                                    'percentage' => $detailed_personal_rating
                                ])
                            </div>
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h4 style="margin-top: 20px"><b>{{$category}}</b></h4>
                                        <a href="/location/report/{{$location->id}}/{{implode("-", explode(" ", strtolower($category)))}}?rating_system=personal">
                                            <p style="margin-top: -10px">See more details</p>
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <h3><b>{{$detailed_personal_rating}}%</b></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr style="margin-top: 4px; margin-bottom: 4px">
                    </div>
                </div>
            @endforeach
            <div class="row" style="margin-top: 40px">
                <div class="col-md-12 text-center">
                    <p>If you have visited {{$location->name}} then you can rate this location by clicking "Rate this location".</p>
                    <button class="btn btn-default btn-lg center-block">
                        <i class="fa fa-check-square-o"></i> Rate this location
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>