@extends('layouts.app')

@section('content')
<!-- <div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Login</div>

                <div class="panel-body">
                    <form class="form-horizontal" method="POST" action="{{ route('login') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">E-Mail Address</label>

                            <div class="col-md-6">
                                <input id="email" type="text" class="form-control" name="email" value="{{ old('email') }}" required autofocus>

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="col-md-4 control-label">Password</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Login
                                </button>

                                <a class="btn btn-link" href="{{ route('password.request') }}">
                                    Forgot Your Password?
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> -->

<div class="uk-section uk-section-default">
    <div class="uk-container">
        <div class="uk-child-width-1-3@m" uk-grid>
            <div></div>
            <div>
                @if($errors->has('username') || $errors->has('password'))
                <div class="uk-alert uk-alert-danger">
                    <div>{{$errors->first('username')}}</div>
                    <div>{{$errors->first('password')}}</div>
                </div>
                @endif
                @if(session('_errors'))
                <div class="uk-alert-danger" uk-alert>
                    <a href="" class="uk-alert-close" uk-close></a>
                    <p>{{session('_errors')}}</p>                    
                </div>
                @endif
                <h3>Authentification</h3>
                {!!Form::open(['url'=>'/login'])!!}
                {!!Form::text('username','',['class'=>'uk-input uk-margin-small','placeholder'=>'Username'])!!}
                {!!Form::password('password',['class'=>'uk-input uk-margin-small','placeholder'=>'Password'])!!}
                {!!Form::submit('Login',['class'=>'uk-button uk-button-default'])!!}
                {!!Form::close()!!}
            </div>
            <div></div>
        </div>
    </div>
</div>
@endsection
