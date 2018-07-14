@extends('layouts.default')
@section('head-content')
    <script language="JavaScript" src="/js/jquery-3.1.1.js"></script>
    <script src="/js/location_report.js"></script>
    <script language="JavaScript" src="/js/pie_graph.js"></script>
@stop
@section('footer-content')
    <script async defer
            src="//maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY')  }}&callback=initMap">
    </script>
@stop
@section('content')
    <!-- Description Section -->
    <div class="location-report">
        <div class="row visible-md visible-lg">
            <div class="col-md-8 col-lg-8">
                <div style="margin-left: 22px">
                    <h2><b>{{$location->name}}</b></h2>
                    <h4>{{$location->address}}</h4>
                    <h4>{{$location->phone_number}}</h4>
                    <h5>
                        <a href="{{$location->external_web_url}}">{{$location->external_web_url}}</a>
                    </h5>
                </div>
            </div>
            <div class="col-md-4 col-lg-4 visible-md visible-lg">
                <div style="margin-top: 24px" class="pull-right">
                    @if (Auth::check())
                        <a href="{{request()->fullUrlWithQuery(['rating_system' => 'personal'])}}">
                            <button class="btn {{$rating_system === 'personal' ? 'btn-ac-selected' : 'btn-ac'}}">
                                <i class="fa fa-user"></i> Personal
                            </button>
                        </a>
                    @endif
                    <a href="{{request()->fullUrlWithQuery(['rating_system' => 'universal'])}}">
                        <button class="btn {{$rating_system === 'universal' ? 'btn-ac-selected' : 'btn-ac'}}">
                            <i class="fa fa-globe"></i> Universal
                        </button>
                    </a>
                    <a href="/location/rating/{{$location->id}}">
                        <button class="btn btn-ac">
                            <i class="fa fa-location-arrow"></i> Rate the location
                        </button>
                    </a>
                    <div style="margin-top: 16px;margin-bottom: 10px">
                        @foreach($location->tags as $tag)
                            <a href="/location/search?location_tag_id={{$tag->id}}">
                                <button class="btn btn-ac-selected">
                                    <i class="{{$tag->icon_selector}} fa-2x"></i>
                                </button>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="row visible-xs">
            <div class="col-xs-12 text-center">
                <h3><b>{{$location->name}}</b></h3>
                <h4>{{$location->address}}</h4>
                <h4>{{$location->phone_number}}</h4>
                <h4>
                    <a href="{{$location->external_web_url}}"><i class="fa fa-link"></i> Website</a>
                </h4>
            </div>
            <div class="col-xs-12 text-center">
                @if(Auth::check())
                    <a href="{{request()->fullUrlWithQuery(['rating_system' => 'personal'])}}">
                        <button class="btn btn-xs {{$rating_system === 'personal' ? 'btn-ac-selected' : 'btn-ac'}}">
                            <i class="fa fa-user"></i> Personal
                        </button>
                    </a>
                @endif
                <a href="{{request()->fullUrlWithQuery(['rating_system' => 'universal'])}}">
                    <button class="btn btn-xs {{$rating_system === 'universal' ? 'btn-ac-selected' : 'btn-ac'}}">
                        <i class="fa fa-globe"></i> Universal
                    </button>
                </a>
                <a href="/location/rating/{{$location->id}}">
                    <button class="btn btn-ac btn-xs">
                        <i class="fa fa-location-arrow"></i> Rate the location
                    </button>
                </a>
                <div style="margin-top: 16px;margin-bottom: 10px">
                    @foreach($location->tags as $tag)
                        <button class="btn btn-ac-selected">
                            <i class="{{$tag->icon_selector}}"></i>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <hr style="margin: 0">
        </div>
    </div>

    <!-- Map Section -->
    <div class="container-fluid">
        <div class="row" style="background-color: #F8F6FF;">
            <div class="col-md-8 col-xs-12" style="padding-left: 0; padding-right: 0">
                <div id="map"></div>
            </div>
            <div class="col-md-4 col-xs-12" style="padding-right: 0">
                @if ($rating_system === 'universal')
                    <div class="visible-lg visible-md">
                        @include('pages.location_report.universal')
                    </div>
                    <div class="visible-xs">
                        @include('pages.location_report.mobile_universal')
                    </div>
                @endif
                @if ($rating_system === 'personal')
                    <div class="visible-lg visible-md">
                        @include('pages.location_report.personal')
                    </div>
                    <div class="visible-xs">
                        @include('pages.location_report.mobile_personal')
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection