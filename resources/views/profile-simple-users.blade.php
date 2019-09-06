@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Profile</h3>
			<ul uk-accordion="multiple:true">
			    <li class="uk-open">
			        <a class="uk-accordion-title" href="#">Mes Informations</a>
			        <div class="uk-accordion-content">
			        	<div class="uk-width-xlarge uk-align-center">
			        		<button class="uk-button-default uk-border-rounded ">edit <span uk-icon="icon:pencil;ratio:.8"></span></button>
				            {!!Form::open()!!}
				            {!!Form::text('email',Auth::user()->email,['class'=>'uk-input uk-margin-small','disabled'])!!}
				            {!!Form::text('username',Auth::user()->username,['class'=>'uk-input uk-margin-small','disabled'])!!}
				            {!!Form::text('telephone',Auth::user()->phone,['class'=>'uk-input uk-margin-small','disabled'])!!}
				            {!!Form::text('agence',Auth::user()->localisation,['class'=>'uk-input uk-margin-small','disabled'])!!}
				            {!!Form::close()!!}
			        	</div>
			        </div>
			    </li>
			    <li class="uk-open">
			        <a class="uk-accordion-title" href="#">Modifier le mot de passe</a>
			        <div class="uk-accordion-content">
			        	<div class="uk-width-xlarge uk-align-center">
			        		@if(session('success'))
			        		<div class="uk-alert uk-alert-success">
			        			<div>{{session('success')}}</div>
			        		</div>
			        		@endif
			        		@if($errors->has('old_password') || $errors->has('new_password') || $errors->has('confirm_password'))
			        		<div class="uk-alert uk-alert-danger">
			        			<div>{{$errors->first('old_password')}}</div>
			        			<div>{{$errors->first('new_password')}}</div>
			        			<div>{{$errors->first('confirm_password')}}</div>
			        		</div>
			        		@endif
			        		@if(session('_errors'))
			        		<div class="uk-alert uk-alert-danger">
			        			<div>{{session('_errors')}}</div>
			        		</div>
			        		@endif
				        	{!!Form::open(['url'=>'/user/change-password'])!!}
				        	{!!Form::label('Ancien Mot de passe')!!}
				        	{!!Form::text('old_password','',['class'=>'uk-input uk-margin-small'])!!}
				        	{!!Form::label('Nouveau Mot de passe')!!}
				        	{!!Form::password('new_password',['class'=>'uk-input uk-margin-small'])!!}
				        	{!!Form::label('Confirmer le Mot de passe')!!}
				        	{!!Form::password('new_password_confirmation',['class'=>'uk-input uk-margin-small'])!!}
				        	<button type="submit" class="uk-button-default uk-border-rounded">valider <span uk-icon="icon:check;ratio:.8"></span></button>
				        	{!!Form::close()!!}
			        	</div>
			            
			        </div>
			        	
			    </li>
			    <!-- <li>
			        <a class="uk-accordion-title" href="#">Item 3</a>
			        <div class="uk-accordion-content">
			            <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat proident.</p>
			        </div>
			    </li> -->
			</ul>
	</div>
</div>
@endsection
@section("script")
<script type="text/javascript">
	$(function() {

		
	});
</script>
@endsection