@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/user/commandes')}}" uk-tooltip="Toutes les commandes" uk-icon="icon:arrow-left;ratio:1.5"></a> Ravitailler un vendeur</h3>
		<hr class="uk-divider-small"></hr>
		@if(session('_errors'))
			<div class="uk-alert uk-alert-danger">{{session('_errors')}} <button class="uk-align-right close-button"  uk-icon="icon:close"></button></div>
		@endif
		@if(session('success'))
			<div class="uk-alert uk-alert-success">{{session('success')}} <button class="uk-align-right close-button"  uk-icon="icon:close"></button></div>
		@endif



		<!-- FORMULAIRE DE RAVITAILLEMENT -->
		{!!Form::open(['url'=> url()->current()])!!}
		@if($errors->has('vendeur') || $errors->has('produit') || $errors->has('depot') || $errors->has('quantite'))
		<div class="uk-alert uk-alert-danger">
			<button type="button" class="uk-align-right close-button"  uk-icon="icon:close"></button>
			<div>{{$errors->first('vendeur')}}</div>
			<div>{{$errors->first('produit')}}</div>
			<div>{{$errors->first('depot')}}</div>
			<div>{{$errors->first('quantite')}}</div>
		</div>
		@endif
		<!-- SELECT VENDEURS -->
		<div class="" uk-grid>
			<div class="uk-width-3-5@m">
				<label>Vendeur</label>
				<select name="vendeur" class="uk-select" id="vendeur-id">
					<option value="">--Vendeur--</option>
					@if($users && $agences)
					<option value="{{$users->username}}">
						<span>{{$users->username}}</span> -
						<span>{{$users->localisation}}</span> -
						<span>{{$agences->societe}}</span>
					</option>
					@endif
				</select>
			</div>
			<div class="uk-width-1-5@m">
				<label>Parabole du</label>
				{!!Form::text('parabole_du',0,['class'=>'uk-input','disabled','id'=>'parabole_du'])!!}
			</div>
			<div class="uk-width-1-5@m">
					<label>Restant pour ravitaillement</label>
					{!!Form::text('reste_a_ravitailler',0,['class'=>'uk-input','disabled','id'=>'reste_a_ravitailler'])!!}
			</div>
		</div>
		<!-- SELECT MATERIAL -->
		{!!Form::label('Materiel*')!!}
		<select name="produit" id="produit" class="uk-select uk-margin-small">
			<option value="">--Materiel--</option>
			@if($materiel)
			@foreach($materiel as $values)
			<option value="{{$values->reference}}">{{$values->libelle}}</option>
			@endforeach
			@endif
		</select>
		<!-- SELECT DEPOT -->
		{!!Form::label('Depot*')!!}
		<select name="depot" id="depot" class="uk-select">

			<!-- @if($depots)
			@foreach($depots as $values)
			<option value="{{$values->localisation}}">{{$values->localisation}}</option>
			@endforeach
			@endif -->

		</select>
		{!!Form::label('Quantite*')!!}
		{!!Form::number('quantite','',['class'=>'uk-input uk-margin-small','placeholder'=>'Quantite'])!!}
		<div class="uk-alert-info" uk-alert>
			<a href="#" class="uk-alert-close" uk-close></a>
			<p>Ce champs n'est pas obligatoire!</p>
		</div>
		{!!Form::label('Compense*')!!}
		{!!Form::number('compense',0,['class'=>'uk-input uk-margin-small','placeholder'=>'Compense'])!!}
		<button type="submit" class="uk-button-default uk-border-rounded">valider <span uk-icon="icon:check;ratio:.8"></span></button>
		{!!Form::close()!!}
		<!-- // -->



	</div>

</div>
@endsection

@section('script')
<script type="text/javascript">
	$(function () {
		$(".close-button").on('click',function () {
			$(this).parent().hide(500);
		})

		// recuration de la quantite restant pour ravitaillement



		$("#vendeur-id").on('change',function () {

			var form = $adminPage.makeForm("{{csrf_token()}}","{{url('/user/parabole-du')}}",$(this).val());
			form.on('submit',function (e) {
				e.preventDefault();
				$.ajax({
					url : $(this).attr('action'),
					type : $(this).attr('method'),
					data : $(this).serialize(),
					dataType : 'json'
				})
				.done(function (data) {
					// recuperation de la quantite de parabole du et de celle restant pour ravitaillement
						$("#parabole_du").val(data);
				})
				.fail(function (data) {
					UIkit.modal.alert('Erreur!');
				});
			});

			form.submit();

		});

		$('#produit').on('change',function() {

			var form = $adminPage.makeForm("{{csrf_token()}}","{{url('user/get-by-depot')}}",$(this).val());
			var mat = $(this).val();
			form.on('submit',function(e) {
				e.preventDefault();
				$.ajax({
					url : $(this).attr('action'),
					type : $(this).attr('method'),
					data : $(this).serialize(),
					dataType : 'json'
				})
				.done(function(data) {
					$("#depot").html('');
					var options = [];
					data.forEach(function(element) {
						var opt = $("<option></option>");
						opt.val(element.depot);
						opt.text(element.depot+' ('+element.quantite+') ');
						options.push(opt);
						$("#depot").append(options);
					});
					console.log(options);
				})
				.fail(function(data) {
					UIkit.modal.alert("Erreur de chargement!").then(function () {
						$(location).attr('href',"{{url()->current()}}");
					});
				})
			});
			form.submit();

			// recuration de la quantite restant pour ravitaillement

			var myForm = $adminPage.makeForm("","{{url('/user/reste-pour-ravitaillement')}}","");



			myForm.on('submit',function (e) {
				e.preventDefault();
				$.ajax({
					url : $(this).attr('action'),
					type : 'post',
					dataType : 'json',
					data : {
						'vendeurs' : "{{$users->username}}",
						'command'	 : "{{$commande}}",
						'material' : mat,
						'_token'	:	"{{csrf_token()}}"
					}
				})
				.done(function (data) {
					$("#reste_a_ravitailler").val(data);
				})
				.fail(function (data) {
					UIkit.modal.alert(data).then(function () {
						$(location).attr('href',"{{url()->current()}}");
					});
				})
			});
		myForm.submit();
		});

	});
</script>
@endsection
