@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Nouveau Depot/Materiel</h3>
		<hr class="uk-divider-small"></hr>
		@if(session('_errors'))
			<div class="uk-alert uk-alert-danger">{{session('_errors')}} <button class="uk-align-right close-button"  uk-icon="icon:close"></button></div>
		@endif
		@if(session('success'))
			<div class="uk-alert uk-alert-success">{{session('success')}} <button class="uk-align-right close-button"  uk-icon="icon:close"></button></div>
		@endif
		<div class="uk-grid-collapse uk-grid-divider" uk-grid>
			<div class="uk-width-1-2@m">
				<h3>Nouveau Depot</h3>

				@if($errors->has('localisation'))
				<div class="uk-alert uk-alert-danger">{{$errors->first('localisation')}} <button class="uk-align-right close-button"  uk-icon="icon:close"></button></div>
				@endif
				{!!Form::open()!!}
				{!!Form::text('localisation','',['class'=>'uk-input uk-margin-small','placeholder'=>'Localisation'])!!}
				<select class="uk-select" name="vendeurs">
					@if($userdepot)
					@foreach($userdepot as $key=>$values)
					<option value="{{$values->username}}">{{$values->username}}</option>
					@endforeach
					@endif
				</select>
				<button type="submit" class="uk-button-default uk-border-rounded">valider<span uk-icon="icon:check"></span></button>
				{!!Form::close()!!}
			</div>
			<div class="uk-width-1-2@m">
				<h3>Nouveau Materiel</h3>
				@if($errors->has('libelle') || $errors->has('prix_unitaire') || $errors->has('quantite') || $errors->has('prix_initial') || $errors->has('marge') || $errors->has('with_serial'))
				<div class="uk-alert uk-alert-danger">
					<button class="uk-align-right close-button"  uk-icon="icon:close"></button>
					<div>{{$errors->first('libelle')}}</div>
					<div>{{$errors->first('prix_unitaire')}}</div>
					<div>{{$errors->first('prix_initial')}}</div>
					<div>{{$errors->first('marge')}}</div>
					<div>{{$errors->first('quantite')}}</div>
					<div>{{$errors->first('with_serial')}}</div>
				</div>
				@endif
				{!!Form::open(['url'=>'/admin/add-material'])!!}
				{!!Form::text('libelle','',['class'=>'uk-input uk-margin-small','placeholder'=>'Materiel*','id'=>'mat-lib'])!!}
				{!!Form::text('prix_initial',null,['class'=>'uk-input uk-margin-small','placeholder'=>'Prix Initial*','id'=>'mat-pi'])!!}
				{!!Form::text('prix_unitaire',null,['class'=>'uk-input uk-margin-small','placeholder'=>'Prix de Vente*','id'=>'mat-pu'])!!}
				{!!Form::text('marge',null,['class'=>'uk-input uk-margin-small','placeholder'=>'Marge*','id'=>'mat-marg'])!!}
				{!!Form::number('quantite','',['class'=>'uk-input uk-margin-small','placeholder'=>'Quantite*'])!!}
				<div>
				<label>
					Avec S/N {!!Form::checkbox('with_serial',true,['class'=>'uk-checkbox'])!!}
				</label>
				</div>
				<button type="submit" class="uk-button-default uk-border-rounded uk-margin-small">valider<span uk-icon="icon:check"></span></button>
				{!!Form::close()!!}
			</div>
		</div>
	</div>

</div>
@endsection

@section('script')
<script type="text/javascript">
	$(function () {

		$("#mat-lib").on('keyup blur',function() {

			$.ajax({
				url : '/admin/add-depot/auto-complete',
				type : 'post',
				data : { wordSearch : $(this).val(),_token : "{{csrf_token()}}"},
				dataType : 'json'
			})
			.done(function(data) {
				console.log(data);
					var price = $("<input/>"),
						priceInitial = price.clone();
						priceMarge = price.clone();
				if(data && data!=='fail') {
					price.attr('name','prix_unitaire');
					price.attr('type','hidden');
					price.val(data.prix_vente);
					price.insertBefore($("#mat-pu"));

					priceInitial.attr('name','prix_initial');
					priceInitial.attr('type','hidden');
					priceInitial.val(data.prix_vente);
					priceInitial.insertBefore($("#mat-pu"));

					priceMarge.attr('name','marge');
					priceMarge.attr('type','hidden');
					priceMarge.val(data.marge);
					priceMarge.insertBefore($("#mat-pu"));

					$("#mat-pu").val(data.prix_vente);
					$("#mat-pu").removeAttr('name');
					$("#mat-pu").attr('disabled','');

					$("#mat-pi").val(data.prix_initial);
					$("#mat-pi").removeAttr('name');
					$("#mat-pi").attr('disabled','');

					$("#mat-marg").val(data.marge);
					$("#mat-marg").removeAttr('name');
					$("#mat-marg").attr('disabled','');
				} else {
					// $("#mat-pu").val('');
					price.remove();
					priceInitial.remove();
					priceMarge.remove();

					$('#mat-pu').attr('name','prix_unitaire');
					$("#mat-pu").removeAttr('disabled');

					$('#mat-pi').attr('name','prix_initial');
					$("#mat-pi").removeAttr('disabled');

					$('#mat-marg').attr('name','marge');
					$("#mat-marg").removeAttr('disabled');
				}
			})
			.fail(function(data) {
				console.log(data);
			});
		});
	});
</script>
@endsection
