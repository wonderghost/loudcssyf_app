@extends('layouts.app_users')

@section('content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3>Complete Registration</h3>
		<div class="uk-alert-info" uk-alert>
			<a href="#" class="uk-alert-close" uk-close></a>
			<p>Completez les champs vides!</p>
		 </div>
		<input type="hidden" id="quantite" value="{{session('quantite')}}">
			{!!Form::open(['url'=>url()->current(),'method'=>'post','id'=>'complete-form'])!!}
			<div id="all-inputs" class="uk-child-width-1-4@m" uk-grid><div uk-spinner>Patientez svp...</div></div>
		<div id="submit-btn" class="uk-margin-small">
			<button type="button" class="uk-button-danger uk-border-rounded" id="abort">Annuler <span uk-icon="icon:close"></span></button>
			<button type="submit" class="uk-button-default uk-border-rounded" disabled id="validate">Valider <span uk-icon="icon:check"></span></button>
		</div>
		<div id="loader" style="display : none;" uk-spinner>Patientez un instant ...</div>
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
		// creation des champs de saisi pour S/N
		for(var i=0; i < nbInput ; i++) {
			inputs[i] = $("<input/>");
			var div = $("<div></div>");

			inputs[i].attr('type','text');

			inputs[i].attr('required','');
			inputs[i].attr('name','serial-number-'+(i+1));
			inputs[i].addClass('uk-input uk-margin-small serial-input');
			inputs[i].attr('placeholder','Serial Number-'+(i+1));
			div.append(inputs[i]);
			$("#all-inputs").append(div);
		}

		var tabSerial = [];
		// pendant le focus
		$(".serial-input").on('focus',function () {

			var _serialNow = $(this);
			if($.trim(_serialNow.val()) !== "" && $.inArray($.trim(_serialNow.val()),tabSerial) > -1) {
				// existe dans le tableau
				tabSerial.splice(tabSerial.indexOf($.trim(_serialNow.val())),1);
			}
		});

		// apres le focus (verifier le duplicat et l'existence en base de donnees )
		$(".serial-input").on('blur',function () {

			var form = $adminPage.makeForm("{{csrf_token()}}",'/user/add-material/find-serial-number',$(this).val());
			var serialNow = $(this);

			// verification de l'exitence dans la base de donnees | envoi de la requete ajax
			form.on('submit',function(e) {
				e.preventDefault();
				$.ajax({
					url : $(this).attr('action'),
					type : $(this).attr('method'),
					data : $(this).serialize(),
					dataType : 'json'
				})
				.done(function (data) {
					if(data && data !== 'success') {
						// Erreur genere
						UIkit.notification({
							message : data,
							status : 'danger',
							timeout : 800
						});
						$("#validate").attr('disabled','')

					} else {
						$("#validate").removeAttr('disabled')
						// le numero n'existe pas dans la base de donnees
						// verifier s'il n'existe pas de duplicat
						if($.inArray($.trim(serialNow.val()),tabSerial) == -1) {
							// la valeur n'existe pas dans le tableau , il faut l'ajouter
							tabSerial.push($.trim(serialNow.val()));
						} else {
							// la valeur existe dans le tableau
							if($.trim(serialNow.val()) == "")  {
								UIkit.modal.alert("Ce champs ne peut etre vide!");
								$("#validate").attr('disabled','');
								return 0;
							}
							UIkit.modal.alert("Duplicat de numero!").then(function () {
								$("#validate").attr('disabled','')
								tabSerial.splice(tabSerial.indexOf($.trim(serialNow.val())),1);
								$('.serial-input').each(function (index, element) {
										if($.trim($(element).val()) == $.trim(serialNow.val())) {
											$(element).val('');
										}
								});
							});
						}

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

		// ACTION SUR LE BOUTTON ANNULER
		$("#abort").on('click',function() {
			UIkit.modal.dialog("<div class='uk-padding' uk-spinner> Patientez svp... </div>");
			var form = $adminPage.makeForm("{{csrf_token()}}",'/user/add-material/abort-registration','');
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
					UIkit.modal.alert(data).then(function () {
						$(location).attr('href',"{{url()->current()}}");
					})
				});
			});
			form.submit();
		});

// envoi du formulaire de finalisation
$("#complete-form").on('submit',function (e) {
	e.preventDefault();
$(".serial-input").each(function (index,element) {
	if($(element).val() == "") {
		// console.log('ok');
		$("#validate").attr('disabled','');
	}
})
	// envoi de la requete post ajax
	$.ajax({
		url : $(this).attr('action'),
		type : $(this).attr('method'),
		data : $(this).serialize(),
		dataType : 'json',
		beforeSend : function () {
			$("#submit-btn").hide(300)
			$("#loader").show(300)
		}
	}).done(function (data) {
		if(data && data == "success") {
			$("#loader").hide(300)
			// enregistrement reussis
			UIkit.modal.alert("Enregistrement reussi!").then(function () {
				$(location).attr('href',"{{url('user/list-material')}}");
			})
		}
	})
	.fail(function (data) {
		UIkit.modal.alert(data).then(function () {
			$(location).attr('href',"{{url()->current()}}");
		})
	})
})
	});
</script>
@endsection
