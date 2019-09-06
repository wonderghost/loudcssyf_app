@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		@if(session('_errors'))
		<div class="uk-alert uk-alert-danger">{{session('_errors')}}</div>
		@endif
		<h3>Complete Transfert</h3>
		<div class="uk-alert uk-alert-info">Completez les champs vides! <button class="uk-align-right close-button"  uk-icon="icon:close"></button></div>
		<input type="hidden" id="quantite" value="{{session('quantite')}}">
			{!!Form::open(['url'=>url()->current(),'method'=>'post','id'=>'complete-form'])!!}
			<div id="all-inputs" class="uk-child-width-1-4@m" uk-grid><div uk-spinner>Patientez svp...</div></div>
		<div class="uk-margin-small">
			<button type="button" class="uk-button-danger uk-border-rounded" id="abort">Annuler <span uk-icon="icon:close"></span></button>
			<button type="submit" disabled class="uk-button-default uk-border-rounded" id="validate">Valider <span uk-icon="icon:check"></span></button>
		</div>
			{!!Form::close()!!}
	</div>
</div>
@endsection
@section('script')
<script type="text/javascript">
	$(function() {
		// setTimeout(function () {

		// },1000);
		var nbInput = $('#quantite').val();
		var inputs = [];
		$("#all-inputs").html('');
		for(var i=0; i < nbInput ; i++) {
			inputs[i] = $("<input/>");
			var div = $("<div></div>");
			// var span = $("<span></span>");
			// span.attr('uk-tooltip','Ce numero existe dans le systeme');
			inputs[i].attr('type','text');
			// inputs[i].attr('uk-tooltip','Ce numero existe dans le systeme');
			inputs[i].attr('required','');
			inputs[i].attr('name','serial-number-'+(i+1));
			inputs[i].addClass('uk-input uk-margin-small serial-input');
			inputs[i].attr('placeholder','Serial Number-'+(i+1));
			div.append(inputs[i]);
			// div.append(span);
			$("#all-inputs").append(div);
		}

		//
		$('.serial-input').each(function(index) {
			if($(this).val() == '') {

				return 0;
			}
		});
		//
		$(".close-button").on('click',function () {
			$(this).parent().hide(500);
		})

		// Envoi de la request ajax | verification de l'existence du numero de serie
		var serialTab = [];
		$(".serial-input").on('keyup focus blur',function () {
			var form = $adminPage.makeForm("{{csrf_token()}}",'/user/complete-transfert/find-serial-number',$(this).val());
			var serialNow = $(this);
			if($.trim($(this).val()) == "") {
				$("#validate").attr('disabled');
				serialNow.removeAttr('uk-tooltip');
				return 0;
			}
			// serialTab.push(serialNow.val());
			form.on('submit',function(e) {
				e.preventDefault();
				$.ajax({
					url : $(this).attr('action'),
					type : $(this).attr('method'),
					data : $(this).serialize(),
					dataType : 'json'
				})
				.done(function (data) {
					if(data && data =='done') {
						$("#validate").removeAttr('disabled');
						serialNow.removeAttr('uk-tooltip');
						serialNow.addClass('uk-alert-success');
						serialNow.removeClass('uk-alert-danger');
					} else if(data == 'not-in-system') {
						$("#validate").attr('disabled','');
						// $("#validate").removeAttr('disabled');
						serialNow.attr('uk-tooltip',"Ce numero n'existe pas dans le systeme");
						serialNow.addClass('uk-alert-danger');
						// serialNow.removeAttr('uk-tooltip');
					} else if(data == 'not-in-depot') {
						$("#validate").attr('disabled','');
						serialNow.attr('uk-tooltip',"Ce numero n'existe pas dans le depot choisi");
						serialNow.addClass('uk-alert-danger');
					}  else if(data == 'is-attribuer') {
						$("#validate").attr('disabled','');
						serialNow.attr('uk-tooltip',"Ce numero est deja attribuer");
						serialNow.addClass('uk-alert-danger');
					}
				})
				.fail(function(data) {
					console.log(data);
				});
			});
			form.submit();
		});
		// ACTION SUR LE BOUTTON ANNULER
		$("#abort").on('click',function() {
			UIkit.modal.dialog("<div class='uk-padding' uk-spinner> Patientez svp... </div>");
			var form = $adminPage.makeForm("{{csrf_token()}}","/user/ravitailler/{commande}/abort-transfert",'');
			form.on('submit',function(e) {
				e.preventDefault();
				$.ajax({
					url : $(this).attr('action'),
					type : $(this).attr('method'),
					data : $(this).serialize(),
					dataType : 'json'
				})
				.done(function(data) {
					if(data == 'done') {
						UIkit.modal.alert('Enregistrement annulé!').then(function() {
							$(location).attr('href',"{{url()->previous()}}");
						});
					}
				})
				.fail(function(data) {
					console.log(data);
				});
			});
			form.submit();
		});


	});
</script>
@endsection
