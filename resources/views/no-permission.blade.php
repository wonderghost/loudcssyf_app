@extends('layouts.app')

@section('content')
<div class="uk-section uk-section-default">
    <div class="uk-container">
        <div class="uk-child-width-1-3@m" uk-grid>
            <div></div>
            <div class="uk-alert uk-alert-warning">
                <p>
                	<span uk-icon="icon:warning"></span> Access suspendu , Contactez l'Administrateur
                </p>
                <a href="{{url('/logout')}}" class="uk-button-default"><span uk-icon="icon:sign-out"></span> Deconnexion</a>
            </div>
            <div></div>
        </div>
    </div>
</div>
@endsection