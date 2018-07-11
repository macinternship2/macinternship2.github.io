@extends('layouts.default')
@section('head-content')
    <script src="/js/jquery-3.1.1.js"></script>
    <script src="/js/utils.js" type="text/javascript"></script>
    <script src="/js/location_search_map.js"></script>
    @if ( $view === 'table' )
        <link href="/css/jquery/jquery-ui.css" rel="stylesheet" type="text/css">
        <script src="/css/jquery/jquery-ui.js"></script>
        <script src="/js/location_search_table.js"></script>
    @endif
@stop
@section('footer-content')
    @if ( $view === 'map')
        <script async defer
                src="//maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&amp;callback=initMap">
        </script>
    @endif
@stop
@section('content')
    <div class="location-search">
        <div class="row" style="padding-top: 20px">
            <div class="col-md-12">
                <div class="title-map-table-bar">
                    <div class="text-right">
                        <a class="{{$view === "table" ? "selected" : ""}}" href="{{ request()->fullUrlWithQuery(['view' => 'table']) }}">Table</a>
                        <a class="{{ $view === "map" ? "selected" : ""}}" href="{{ request()->fullUrlWithQuery(['view' => 'map']) }}">Map</a>
                    </div>
                </div>
            </div>
        </div>
        @if ( $view === 'table' )
            <div class="row" style="margin-top: 20px">
                <form method="post" action="/location/search-radius">
                    <div class="col-md-4 col-xs-8">
                            {!! csrf_field() !!}
                            <div class="form-group">
                                <label class="sr-only" for="distance">Radius Distance</label>
                                <div class="input-group">
                                    <div class="input-group-addon">Radius</div>
                                    <input type="number" step="0.1"
                                           min="0.1"
                                           name="distance"
                                           class="form-control" id="distance"
                                           placeholder="distance" value="{{ $search_radius }}">
                                    <span class="input-group-addon">KM</span>
                                </div>
                            </div>
                    </div>
                    <div class="col-md-2 col-xs-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-refresh"></i> Update</button>
                    </div>
                </form>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-responsive table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{request()->fullUrlWithQuery(['order_by' => 'name'])}}">Location Name <i class="fa fa-sort"></i></a>
                                </th>
                                <th class="text-center">
                                    <a href="{{request()->fullUrlWithQuery(['order_by' => 'ratings'])}}">Accessibility Ratings <i class="fa fa-sort"></i> </a>
                                </th>
                                <th class="text-center">
                                    <a href="{{request()->fullUrlWithQuery(['order_by' => 'distance'])}}">Distance <i class="fa fa-sort"></i></a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($locations as $location)
                                <tr>
                                    <td style="width: 30%" class="text-left">{{$location['name']}}</td>
                                    <td style="width: 20%" class="text-center">{{Auth::check() ?
                                      $location['overall_personal_ratings']."%" :
                                      $location['overall_universal_ratings']."%"}}
                                    </td>
                                    <td  style="width: 30%" class="text-center">{{$location['distance']." km"}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($view === 'map')
            <div class="row" style="margin-top: 30px">
                <div class="col-md-12">
                    <div id="map"></div>
                </div>
            </div>
        @endif
    </div>
@stop
