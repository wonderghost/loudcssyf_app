@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Nouveau Rapport</h3>
		@if($errors->any())
		@foreach($errors->all() as $error)
		<div class="uk-alert-danger uk-border-rounded uk-box-shadow-small uk-width-1-2@m" uk-alert>
			<a href="#" class="uk-alert-close" uk-close></a>
			<p>{{$error}}</p>
		</div>
		@endforeach
		@endif
		@if(session('_errors'))
		<div class="uk-alert-danger uk-border-rounded uk-box-shadow-small uk-width-1-2@m" uk-alert>
			<a href="" class="uk-alert-close" uk-close></a>
			<p>{{session('_errors')}}</p>
		</div>
		@endif
		@if(session('success'))
		<div class="uk-alert-success uk-border-rounded uk-box-shadow-small uk-width-1-2@m" uk-alert>
			<a href="#" class="uk-alert-close" uk-close></a>
			<p>{{session('success')}}</p>
		</div>
		@endif
		<div class="uk-width-1-2@m uk-width-1-1@s">
		<!-- DATE RAPPORT -->
		<label for="">Date Rapport</label>
		<input type="date" class="uk-input uk-border-rounded" name="" value="" id="my-date">

		<!-- // -->
		<!-- CHOIS DU VENDEUR -->
		<label for="">Vendeurs</label>
		<select class="uk-select uk-margin-small uk-border-rounded" id="my-vendeurs" name="">
			<option value="">--- Selectionnez un Vendeur ---</option>
			@if($vendeurs)
			@foreach($vendeurs as $value)
			<option value="{{$value->username}}">{{$value->username}} ({{$value->agence()->societe}}_{{$value->localisation}})</option>
			@endforeach
			@endif
		</select>

		<!-- // -->
	</div>
		<ul uk-tab>
		    <li><a href="#">Recrutement</a></li>
		    <li><a href="#">Reabonnement</a></li>
		    <li><a href="#">Migration</a></li>
		</ul>

		<ul class="uk-switcher uk-margin">
			<li>
				<!-- RECRUTEMENT -->
				{!!Form::open(['url'=>'/admin/send-rapport/recrutement','class'=>'uk-width-1-2@m uk-width-1-1@s','id'=>'recrutement-form'])!!}
				<input type="hidden" name="date" value="" class="la-date">
				<input type="hidden" name="vendeurs" value="" class="vendeurs">
				{!!Form::label('Quantite Materiel')!!}
				{!!Form::number('quantite_materiel','',['class'=>'uk-input uk-margin-small uk-border-rounded quantite-materiel','min'=>'1'])!!}
				<div class="serial-inputs"></div>
				{!!Form::label('Montant TTC')!!}
				{!!Form::number('montant_ttc','',['class'=>'uk-input uk-margin-small uk-border-rounded'])!!}

				{!!Form::submit('validez',['class'=>'uk-button uk-button-primary uk-border-rounded uk-box-shadow-small validation-button','id'=>'validation-recrutement'])!!}
				{!!Form::close()!!}
				<!-- // -->
			</li>
			<li>
				<!-- REABONNEMENT -->
				{!!Form::open(['url'=>'/admin/send-rapport/reabonnement','class'=>'uk-width-1-2@m uk-width-1-1@s'])!!}
				<input type="hidden" name="date" value="" class="la-date">
				<input type="hidden" name="vendeurs" value="" class="vendeurs">
				{!!Form::label('Montant TTC')!!}
				{!!Form::number('montant_ttc','',['class'=>'uk-input uk-margin-small uk-border-rounded'])!!}


				<div class="">
					<div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
            <label>
							{!!Form::radio('type_credit','cga')!!}Cga
						</label>
            <label>
							{!!Form::radio('type_credit','rex')!!}Rex
						</label>
        </div>
			</div>
			{!!Form::submit('validez',['class'=>'uk-button uk-button-primary uk-border-rounded uk-box-shadow-small'])!!}
				{!!Form::close()!!}
				<!-- // -->
			</li>
			<li>
				<!-- MIGRATION -->
				{!!Form::open(['url'=>'','class'=>'uk-width-1-2@m uk-width-1-1@s'])!!}
				<input type="hidden" name="date" value="" class="la-date">
				{!!Form::label('Quantite Materiel')!!}
				{!!Form::number('quantite_materiel','',['class'=>'uk-input uk-margin-small uk-border-rounded quantite-materiel'])!!}
				<div class="serial-inputs"></div>
				{!!Form::submit('validez',['class'=>'uk-button uk-button-primary uk-border-rounded uk-box-shadow-sall validation-button'])!!}
				{!!Form::close()!!}
				<!-- // -->
			</li>
		</ul>
	</div>
</div>
@endsection
@section("script")
<script type="text/javascript">
	$(function() {

		$("#my-date").on('blur',function (e) {
			console.log($(this).val())
			$('.la-date').val($(this).val())
		})

		$("#my-vendeurs").on('focus change',function (e) {
			$(".vendeurs").val($(this).val())

			$(".quantite-materiel").on('keyup change keypress',function(e) {
				$logistique.SerialInputCols($(this).val(),$(".serial-inputs"))
				$logistique.CheckSerial("{{csrf_token()}}","{{url('/admin/rapport/check-serial')}}",$('.validation-button'),$('.vendeurs').val())
			})
		})


	});
</script>
@endsection
