@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin/list-users')}}" uk-tooltip="tous les utilisateurs" uk-icon="icon:arrow-left;ratio:1.5"></a> Modifier les informations</h3>

		@if(session('success')) 
		<div class="uk-alert uk-alert-success">
			<div>{{session('success')}}</div>
		</div>
		@endif

		@if($errors->has('email') || $errors->has('phone') || $errors->has('type') || $errors->has('societe') || $errors->has('localisation'))
		<div class="uk-alert uk-alert-danger">
			<div>{{$errors->first('email')}}</div>
			<div>{{$errors->first('phone')}}</div>
			<div>{{$errors->first('type')}}</div>
			<div>{{$errors->first('societe')}}</div>
			<div>{{$errors->first('localisation')}}</div>
		</div>
		@endif
				{!!Form::open()!!}
		<div class="uk-child-width-1-2@m" uk-grid>
			<div>
				<h4>Infos Utilisateur</h4>
				@if($utilisateur && $agence)

				{!!Form::email('email',$utilisateur->email,['class'=>'uk-input uk-margin-small','placeholder'=>'E-mail *'])!!}
				{!!Form::number('phone',$utilisateur->phone,['class'=>'uk-input uk-margin-small','placeholder'=>'Telephone *'])!!}
				@if($utilisateur->type == 'v_standart')
				{!!Form::text('type','Vendeur Standart',['class'=>'uk-input uk-margin-small','placeholder'=>'Access','disabled'])!!}
				@elseif($utilisateur->type == 'v_da')
				{!!Form::text('type','Distributeur Agree',['class'=>'uk-input uk-margin-small','placeholder'=>'Access','disabled'])!!}
				@endif
				{!!Form::text('localisation',$utilisateur->localisation,['class'=>'uk-input uk-margin-small','placeholder'=>'Agence','id'=>'localisation'])!!}
				{!!Form::hidden('password','loudcssyf')!!}
			</div>
			
			<div id="agency-infos">
				<h4>Agence</h4>
				@if($utilisateur->type == 'v_standart')
				{!!Form::text('societe',$agence->societe,['disabled','class'=>'uk-input uk-margin-small','placeholder'=>'Societe *','id'=>'societe'])!!}
				{!!Form::text('rccm',$agence->rccm,['disabled','class'=>'uk-input uk-margin-small','placeholder'=>'RCCM'])!!}
				{!!Form::text('ville',$agence->ville,['disabled','class'=>'uk-input uk-margin-small','placeholder'=>'Ville'])!!}
				{!!Form::text('adresse',$agence->adresse,['disabled','class'=>'uk-input uk-margin-small','placeholder'=>'Adresse'])!!}
				@else
				{!!Form::text('societe',$agence->societe,['class'=>'uk-input uk-margin-small','placeholder'=>'Societe *','id'=>'societe'])!!}
				{!!Form::text('rccm',$agence->rccm,['class'=>'uk-input uk-margin-small','placeholder'=>'RCCM'])!!}
				{!!Form::text('ville',$agence->ville,['class'=>'uk-input uk-margin-small','placeholder'=>'Ville'])!!}
				{!!Form::text('adresse',$agence->adresse,['class'=>'uk-input uk-margin-small','placeholder'=>'Adresse'])!!}
				{!!Form::text('num_dist',$agence->num_dist,['class'=>'uk-input uk-margin-small','placeholder'=>'Numero distributeur'])!!}
				@endif
			</div>
		</div>
		<button type="submit" class="uk-button-default uk-border-rounded">valider <span uk-icon="icon:check"></span></button>
			@endif
				{!!Form::close()!!}

		
	</div>	
</div>

@endsection
@section('script')
<script type="text/javascript">
	$(function() {
		
	});
</script>
@endsection