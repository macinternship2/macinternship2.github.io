@extends('layouts.default')
@section('content')

    <div class="row" style="margin-top: 40px">
        <div class="col-md-offset-3 col-md-6 col-sm-12 col-lg-offset-3 col-lg-6 col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3>Account Verification Recovery</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <form method="post" action="/user/verification-mail/send">
                            {!! csrf_field() !!}
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Email</label>
                                    <input class="form-control" name="email" placeholder="Please enter your email"/>
                                    @if ($errors->has('email'))
                                        <p class="text-danger">{{$errors->get('email')[0]}}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="row" style="margin-top: 20px">
                                <div class="col-md-12">
                                    {!! app('captcha')->display(); !!}
                                    @if ($errors->has('g-recaptcha-response'))
                                        <p class="text-danger">{{$errors->get('g-recaptcha-response')[0]}}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="row" style="margin-top: 20px">
                                <div class="col-md-12">
                                    <button class="btn btn-default" type="submit"><i class="fa fa-send"></i> Send verification Link</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop