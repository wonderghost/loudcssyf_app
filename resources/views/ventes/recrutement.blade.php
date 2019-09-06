@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3>
			<a href="{{url('/user')}}" uk-tooltip="Dashboard" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Recrutement</h3>
		<hr class="uk-divider-small">
		<div uk-spinner id="for-loader"></div>
		{!!Form::open(['url'=>'user/make-recrutement'])!!}
		@if($errors->has('serial_number') || $errors->has('debut') || $errors->has('fin') || $errors->has('nom') || $errors->has('phone') || $errors->has('adresse') || $errors->has('prenom') || $errors->has('email'))
		<div class="uk-alert uk-alert-danger">
			<button type="button" class="uk-align-right close-button"  uk-icon="icon:close"></button>
			<div>{{$errors->first('serial_number')}}</div>
			<div>{{$errors->first('debut')}}</div>
			<div>{{$errors->first('fin')}}</div>
			<div>{{$errors->first('nom')}}</div>
			<div>{{$errors->first('prenom')}}</div>
			<div>{{$errors->first('adresse')}}</div>
			<div>{{$errors->first('email')}}</div>
		</div>
		@endif
		@if(session('success'))
		<div class="uk-alert uk-alert-success">
			<button type="button" class="uk-align-right close-button"  uk-icon="icon:close"></button>
			<div>{{session('success')}}</div>
		</div>
		@endif
		@if(session('_errors'))
		<div class="uk-alert uk-alert-danger">
			<div>{{session('_errors')}}</div>
		</div>
		@endif
		<ul uk-accordion="collapsible: false;multiple : true">
		    <li>
		        <a class="uk-accordion-title" href="#">Infos Materiel/Abonnement</a>
		        <div class="uk-accordion-content">
		         	<div class="uk-grid-collapse uk-grid-divider uk-child-width-1-2@m" uk-grid>
		         		<div>
		         			<label>Materiel</label>
		         			{!!Form::text('serial_number','',['class'=>'uk-input uk-margin-small','placeholder'=>'S/N','id'=>'serial_number'])!!}
		         			<label>Date Debut</label>
		         			{!!Form::text('debut','',['class'=>'uk-input uk-margin-small la-date','placeholder'=>'DEBUT'])!!}
		         		</div>
		         		<div>
		         			<div class="uk-grid-collapse uk-margin-small uk-child-width-1-2@m" uk-grid>
		         				<div>
		         					<label>Formule</label>
				         			<select class="uk-select uk-margin-small" name="formule" id="formule">
				         				<option>--Formule--</option>
				         			</select>
				         			<!-- {!!Form::text('fin','',['class'=>'uk-input uk-margin-small la-date','placeholder'=>'FIN'])!!} -->
		         				</div>
		         				<div>
		         					<label>Duree</label>
		         					<select name="duree" class="uk-select uk-margin-small" id="duree">
		         						<option value="1-mois">1 mois</option>
		         						<option value="2-mois">2 mois</option>
		         						<option value="3-mois">3 mois</option>
		         						<option value="6-mois">6 mois</option>
		         						<option value="9-mois">9 mois</option>
		         						<option value="12-mois">12 mois</option>
		         						<option value="24-mois">24 mois</option>
		         					</select>
		         				</div>
		         			</div>
		         			<label>Date Fin</label>
		         			{!!Form::text('fin','',['class'=>'uk-input uk-margin-small la-date','placeholder'=>'FIN'])!!}
		         		</div>
		         	</div>
		        </div>		        	
		    </li>
		    <li>
		        <a class="uk-accordion-title" href="#">Info Clients</a>
		        <div class="uk-accordion-content">
		        	<div class="uk-grid-collapse uk-grid-divider uk-child-width-1-2@m" uk-grid>
		        		<div>
		        			<label>Nom</label>
				            {!!Form::text('nom','',['class'=>'uk-input uk-margin-small','placeholder'=>'Nom'])!!}
				            <label>Telephone</label>
				            {!!Form::text('phone','',['class'=>'uk-input uk-margin-small','placeholder'=>'Telphone'])!!}
				            <label>Adresse</label>
				            {!!Form::text('adresse','',['class'=>'uk-input uk-margin-small','placeholder'=>'Adresse'])!!}
		        		</div>
		        		<div>
		        			<label>Prenom</label>
		        			{!!Form::text('prenom','',['class'=>'uk-input uk-margin-small','placeholder'=>'Prenom'])!!}
		        			<label>Email</label>
		        			{!!Form::text('email','',['class'=>'uk-input uk-margin-small','placeholder'=>'Email'])!!}
		        		</div>
		        	</div>
		        </div>
		    </li>
	  	</ul>
	  	<div class="uk-text-lead"><span>NET A PAYER (GNF)= </span><span id="total-prix">0</span></div>
	  	<hr class="uk-divider-small">
	  	<button type="submit" class="uk-button-default uk-border-rounded" id="sub-button">valider <span uk-icon="icon:check;ratio:.8"></span></button>
	  	{!!Form::close()!!}
	</div>
</div>
@endsection

@section('script')
<script type="text/javascript">
	$(function() {




		// MISE A JOUR DU MONTANT TOTAL NET A PAYER PAR CHANGMENT DE LA DUREE
		$("#duree,#formule").on('change',function () {
			var form 	=	$adminPage.makeForm("{{csrf_token()}}","{{url()->current()}}/net",'');
			var formule = $("<input/>") , duree = $("<input/>");
			formule.val($("#formule").val());
			duree.val($("#duree").val());

			formule.attr('name','formule');
			duree.attr('name','duree');
			if($(this).attr('id') == 'formule') {
				formule.val($(this).val());
				console.log(formule);
			} else {
				duree.val($(this).val());
				console.log(duree);
			}
			form.append(formule);
			form.append(duree);
			form.on('submit',function(e) {
				e.preventDefault();
				$.ajax({
					url : $(this).attr('action'),
					type : $(this).attr('method'),
					dataType : 'json',
					data : $(this).serialize()
				})
				.done(function(data) {
					$("#total-prix").html(lisibilite_nombre(data));
				})
				.fail(function(data) {
					Uikit.alert('Erreur de chargement!');
					$(location).attr('href');
				});
			});
			form.submit();
		});

		
		// =====

		$("#serial_number").focus();
		// datepicker
		$(".la-date").datepicker();
		
		// recuperation des formules
		var form = $adminPage.makeForm("{{csrf_token()}}",'{{url()->current()}}','');
			form.on('submit',function(e) {
				e.preventDefault();
				$.ajax({
					'url' : $(this).attr('action'),
					'type' : $(this).attr('method'),
					'data' : $(this).serialize(),
					'dataType' : 'json'
				})
				.done(function(data) {
					if(data == "fail") {

					} else {
						// ajouter dans la liste des formules
						$("#for-loader").hide('slow');
						var options = [];
						$("#formule").html('');
						for(var i = 0 ; i < data.length ; i++ ) {
							options[i] = $("<option></option>"); 
							options[i].val(data[i].nom);
							options[i].text(data[i].nom+' --> '+lisibilite_nombre(data[i].prix)+' GNF');
							options[i].attr('id',data[i].prix);
							$("#formule").append(options[i]);
						}						
					}
				})
				.fail(function(data) {
					console.log(data);
				});
			});
			form.submit();

		// VERIFICATION DE LA VALIDITE DU NUMERO DE SERIE

		$("#serial_number").on('blur',function() {
			var form = $adminPage.makeForm("{{csrf_token()}}","{{url()->current()}}"+"/is-valid-sn",$(this).val());
			form.on('submit',function(e) {
				e.preventDefault();
				$.ajax({
					'url' : $(this).attr('action'),
					'type' : $(this).attr('method'),
					'data' : $(this).serialize(),
					'dataType' : 'json'
				})
				.done(function(data) {
					if(data == 'fail') {	
						// S/N invalide
						$("#serial_number").removeClass('uk-alert-success');
						$("#serial_number").addClass('uk-alert uk-alert-danger');
						$("#sub-button").attr('disabled','disabled');
						console.log($(this));
					} else {
						// S/N valide
						$("#serial_number").removeClass('uk-alert-danger');
						$("#serial_number").addClass('uk-alert uk-alert-success');
						$("#sub-button").removeAttr('disabled');
						// console.log(data);
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