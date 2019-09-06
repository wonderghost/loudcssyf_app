@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3>Complete Registration</h3>
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

		var nbInput = $('#quantite').val();
		var inputs = [];
		$("#all-inputs").html('');
		for(var i=0; i < nbInput ; i++) {
			inputs[i] = $("<input/>");
			var div = $("<div></div>");

			inputs[i].attr('type','text');

			inputs[i].attr('required','');
			inputs[i].attr('name','serial-number-'+(i+1));
			inputs[i].addClass('uk-input uk-margin-small serial-input');
			inputs[i].attr('placeholder','Serial Number-'+(i+1));
			div.append(inputs[i]);
			// div.append(span);
			$("#all-inputs").append(div);
		}
		//
		$(".close-button").on('click',function () {
			$(this).parent().hide(500);
		})

		// Envoi de la request ajax | verification de l'existence du numero de serie

		var tabSerial = [];
		$(".serial-input").on('blur',function () {

			var form = $adminPage.makeForm("{{csrf_token()}}",'/admin/add-material/find-serial-number',$(this).val());
			var serialNow = $(this);

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

						serialNow.attr('uk-tooltip','Ce numero existe dans le systeme');
					} else {

							if($.inArray($.trim(serialNow.val()),tabSerial) > -1) {
								
								serialNow.addClass("uk-alert-danger");
								serialNow.attr('uk-tooltip',"Duplicat de numero!");
							} else {
								serialNow.removeClass('uk-alert-danger');
								$("#validate").removeAttr('disabled');
								serialNow.removeAttr('uk-tooltip');
							}

					}

					if($.trim(serialNow.val()) != "") {

						console.log($.inArray($.trim(serialNow.val()),tabSerial));
						if($.inArray($.trim(serialNow.val()),tabSerial) == -1) {
							tabSerial.push(serialNow.val());
						}
					}
					console.log(tabSerial);
					$(".serial-input").each(function (index,element) {
						if($.trim($(element).val()) == "") {
							$(element).attr('uk-tooltip',"Ce champs ne peut etre vide!");
							$("#validate").attr('disabled','');
						} else {
							$("#validate").removeAttr('disabled');
						}
					});
				})
				.fail(function(data) {
					UIkit.modal.alert(data).then(function () {
						$(location).attr('href',"{{url()->current()}}");
					})
				});
			});
			form.submit();
		});
		// ACTION SUR LE BOUTTON ANNULER
		$("#abort").on('click',function() {
			UIkit.modal.dialog("<div class='uk-padding' uk-spinner> Patientez svp... </div>");
			var form = $adminPage.makeForm("{{csrf_token()}}",'/admin/add-material/abort-registration','');
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
							$(location).attr('href','');
						});
					}
				})
				.fail(function(data) {
					UIkit.modal.alert(data).then(function () {
						$(location).attr('href',"{{url()->current()}}");
					})
				});
			});
			form.submit();
		});


	});
</script>
@endsection
