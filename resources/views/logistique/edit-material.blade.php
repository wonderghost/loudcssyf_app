@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin/list-material')}}" uk-tooltip="Tous les materiels" uk-icon="icon:arrow-left;ratio:1.5"></a> Editer Infos Materiel</h3>
		<hr class="uk-divider-small"></hr>
		@if(session('_errors'))
			<div class="uk-alert uk-alert-danger">{{session('_errors')}} <button class="uk-align-right close-button"  uk-icon="icon:close"></button></div>
		@endif
		@if(session('success'))
			<div class="uk-alert uk-alert-success">{{session('success')}} <button class="uk-align-right close-button"  uk-icon="icon:close"></button></div>
		@endif
		<div class="uk-grid-collapse uk-child-width-1-2@m" uk-grid>
			<div>
				<h3>Infos Materiel</h3>
				@if($errors->has('depot') || $errors->has('libelle') || $errors->has('prix_unitaire') || $errors->has('quantite') || $errors->has('prix_initial') || $errors->has('marge'))
				<div class="uk-alert uk-alert-danger">
					<button class="uk-align-right close-button"  uk-icon="icon:close"></button>
					<div>{{$errors->first('depot')}}</div>
					<div>{{$errors->first('libelle')}}</div>
					<div>{{$errors->first('prix_unitaire')}}</div>
					<div>{{$errors->first('prix_initial')}}</div>
					<div>{{$errors->first('marge')}}</div>
					<div>{{$errors->first('quantite')}}</div>
				</div>
				@endif
				@if($material)
				{!!Form::open()!!}
				{!!Form::hidden('reference',$material->reference)!!}
				{!!Form::text('libelle',$material->libelle,['class'=>'uk-input uk-margin-small','placeholder'=>'Materiel*','id'=>'mat-lib'])!!}
				{!!Form::text('prix_initial',$material->prix_initial,['class'=>'uk-input uk-margin-small','placeholder'=>'Prix Initial*','id'=>'mat-pu'])!!}
				{!!Form::text('prix_unitaire',$material->prix_vente,['class'=>'uk-input uk-margin-small','placeholder'=>'Prix de Vente*','id'=>'mat-pu'])!!}
				{!!Form::text('marge',$material->marge,['class'=>'uk-input uk-margin-small','placeholder'=>'Marge*','id'=>'mat-pu'])!!}
				
				<button type="submit" class="uk-button-default uk-border-rounded uk-margin-small">valider<span uk-icon="icon:check"></span></button>
				@endif
				{!!Form::close()!!}	
			</div>
		</div>
	</div>
	
</div>
@endsection

@section('script')
<script type="text/javascript">
	$(function () {
		$(".close-button").on('click',function () {
			$(this).parent().hide(500);
		})
		$("#mat-lib").on('keyup blur',function() {

			$.ajax({
				url : '/admin/add-depot/auto-complete',
				type : 'post',
				data : { wordSearch : $(this).val(),_token : "{{csrf_token()}}"},
				dataType : 'json'
			})
			.done(function(data) {
				console.log(data);
					var price = $("<input/>");
				if(data && data!=='fail') {
					price.attr('name','prix_unitaire');
					price.attr('type','hidden');
					price.val(data.prix_vente);
					price.insertBefore($("#mat-pu"));

					$("#mat-pu").val(data.prix_vente);
					$("#mat-pu").removeAttr('name');
					$("#mat-pu").attr('disabled','');
				} else {
					// $("#mat-pu").val('');
					price.remove();
					$('#mat-pu').attr('name','prix_unitaire');
					$("#mat-pu").removeAttr('disabled');
				}
			})
			.fail(function(data) {
				console.log(data);
			});
		});
	});
</script>
@endsection