@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Nouvel Utilisateur</h3>

		@if(session('success'))
		<div class="uk-alert-success" uk-alert>
			<a href="#" class="uk-alert-close" uk-close></a>
			<p>{{session('success')}}</p>
		</div>
		@endif

		@if($errors->has('email') || $errors->has('phone') || $errors->has('type') || $errors->has('societe') || $errors->has('localisation') || $errors->has('num_dist'))
		<div class="uk-alert uk-alert-danger">
			<div>{{$errors->first('email')}}</div>
			<div>{{$errors->first('phone')}}</div>
			<div>{{$errors->first('type')}}</div>
			<div>{{$errors->first('societe')}}</div>
			<div>{{$errors->first('localisation')}}</div>
			<div>{{$errors->first('num_dist')}}</div>
		</div>
		@endif
				{!!Form::open()!!}
		<div class="uk-child-width-1-2@m">
			<div>
				<h4>Infos Utilisateur</h4>
				{!!Form::email('email','',['class'=>'uk-input uk-margin-small','placeholder'=>'E-mail *'])!!}
				{!!Form::number('phone','',['class'=>'uk-input','placeholder'=>'Telephone *'])!!}
				{!!Form::select('type',['v_standart'=>'Vendeur standart','v_da'=>'Distibuteur Agree','commercial'=>'Responsable Commercial','logistique'=>'Responsable Logistique','gcga'=>'Gestionnaire Cga','grex'=>'Gestionnaire Rex','gdepot'=>'Gestionnaire Depot'],null,['class'=>'uk-select uk-margin-small','placeholder'=>"-- Niveau d'acces *--",'id'=>'user-type'])!!}
				{!!Form::text('localisation',null,['class'=>'uk-input uk-margin-small','placeholder'=>'Agence','id'=>'localisation'])!!}
				{!!Form::hidden('password','loudcssyf')!!}
			</div>
			<hr class="uk-divider-small">
			<div id="agency-infos" style="display: none;">
				<h4>Agence</h4>
				{!!Form::text('num_dist','XXX',['class'=>'uk-input uk-margin-small','placeholder'=>'Numero distributeur *'])!!}
				{!!Form::text('societe','Loudcssyf-sarl',['class'=>'uk-input uk-margin-small','placeholder'=>'Societe *','id'=>'societe'])!!}
				{!!Form::text('rccm','XXX',['class'=>'uk-input uk-margin-small','placeholder'=>'RCCM'])!!}
				{!!Form::text('ville','Conakry',['class'=>'uk-input uk-margin-small','placeholder'=>'Ville'])!!}
				{!!Form::text('adresse','XXX',['class'=>'uk-input uk-margin-small','placeholder'=>'Adresse'])!!}
			</div>
		</div>
		<button type="submit" class="uk-button-default uk-border-rounded">valider <span uk-icon="icon:check"></span></button>
				{!!Form::close()!!}


	</div>
</div>

@endsection
@section('script')
<script type="text/javascript">
	$(function() {
		$("#user-type").on('change',function() {
			if($(this).val() == "v_da") {
				$("#agency-infos").show(500);
				$("#societe").val('');
				$("#localisation").show(500);
			} else {
				if($(this).val() !== 'v_standart') {
					$("#localisation").val('');
					$("#localisation").hide(500);
				} else {
					$("#localisation").show(500);
					$("#localisation").val('');
				}
				$("#agency-infos").hide(500);
				$("#societe").val('Loudcssyf-sarl');
			}

		});
	});
</script>
@endsection
