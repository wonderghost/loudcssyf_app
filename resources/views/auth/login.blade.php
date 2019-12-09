@extends('layouts.app')

@section('content')

<div class="uk-section uk-section-default">
    <div class="uk-container">
        <div class="uk-child-width-1-3@m" uk-grid>
            <div></div>
            <div>
                @if($errors->any())
                @foreach($errors->all() as $error)
                <div class="uk-alert-danger uk-border-rounded uk-box-shadow-small" uk-alert>
                  <a href="#" class="uk-alert-close" uk-close></a>
                  <p>{{$error}}</p>
                </div>
                @endforeach
                @endif
                @if(session('_errors'))
                <div class="uk-alert-danger uk-border-rounded uk-box-shadow-small" uk-alert>
                    <a href="" class="uk-alert-close" uk-close></a>
                    <p>{{session('_errors')}}</p>
                </div>
                @endif
                <h3 class="uk-text-center">Authentification</h3>
                {!!Form::open(['url'=>'/login'])!!}
                {!!Form::text('username','',['class'=>'uk-input uk-margin-small uk-border-rounded','placeholder'=>'Username'])!!}
                {!!Form::password('password',['class'=>'uk-input uk-margin-small uk-border-rounded','placeholder'=>'Password'])!!}
                {!!Form::submit('Login',['class'=>'uk-button uk-button-primary uk-border-rounded uk-box-shadow-small uk-width-1-3@m'])!!}
                {!!Form::close()!!}
            </div>
            <div></div>
        </div>
    </div>
</div>
@endsection
