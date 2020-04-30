@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/user/list-client')}}" uk-tooltip="Repertoire" uk-icon="icon:arrow-left;ratio:1.5"></a>Ajouter au Carnet d'adresse</h3>
		<hr class="uk-divider-small">
		@if(session('_errors'))
		<div class="uk-alert uk-alert-danger">
			<button class="uk-align-right close-button"  uk-icon="icon:close"></button>
			<div>{{session('_errors')}}</div>
		</div>
		@endif
		@if(session('success'))
		<div class="uk-alert uk-alert-success">
			<button class="uk-align-right close-button"  uk-icon="icon:close"></button>
			<div>{{session('success')}}</div>
		</div>
		@endif
		@if($errors->has('nom') || $errors->has('prenom') || $errors->has('email') || $errors->has('phone'))
		<div class="uk-alert uk-alert-danger">
			<button class="uk-align-right close-button"  uk-icon="icon:close"></button>
			<div>{{$errors->first('nom')}}</div>
			<div>{{$errors->first('prenom')}}</div>
			<div>{{$errors->first('email')}}</div>
			<div>{{$errors->first('phone')}}</div>
		</div>
		@endif
		<div uk-grid>
			<div class="uk-width-1-2@m">
				{!!Form::open()!!}
				<!-- {!!Form::text('numero_abonne','',['class'=>'uk-input uk-margin-small','placeholder'=>'Nom * '])!!} -->
				{!!Form::text('nom','',['class'=>'uk-input uk-margin-small','placeholder'=>'Nom * '])!!}
				{!!Form::text('prenom','',['class'=>'uk-input uk-margin-small','placeholder'=>'Prenom * '])!!}
				{!!Form::email('email','',['class'=>'uk-input uk-margin-small','placeholder'=>'Email  '])!!}
				{!!Form::text('phone','',['class'=>'uk-input uk-margin-small','placeholder'=>'Telephone * '])!!}
				{!!Form::text('adresse','undefined',['class'=>'uk-input uk-margin-small','placeholder'=>'Adresse  '])!!}
				<button type="submit" class="uk-button-default uk-border-rounded">valider <span uk-icon="icon:check;ratio:.8"></span></button>
				{!!Form::close()!!}
			</div>
		</div>
	</div>
</div>
@endsection
