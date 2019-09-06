@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Nouveau Rapport</h3>
		@if($errors->has('quantite_recrutement') || $errors->has('quantite_migration') || $errors->has('ttc_reabonnement') || $errors->has('vendeurs'))
		<div class="uk-alert uk-alert-danger">
			<div>{{$errors->first("quantite_recrutement")}}</div>
			<div>{{$errors->first("quantite_migration")}}</div>
			<div>{{$errors->first("ttc_reabonnement")}}</div>
			<div>{{$errors->first("vendeurs")}}</div>
		</div>
		@endif
		@if(session('_errors'))
		<div class="uk-alert-danger" uk-alert>
			<a href="" class="uk-alert-close" uk-close></a>
			<p>{{session('_errors')}}</p>
		</div>
		@endif
		@if(session('success'))
		<div class="uk-alert-success" uk-alert>
			<a href="#" class="uk-alert-close" uk-close></a>
			<p>{{session('success')}}</p>
		</div>
		@endif
		{!!Form::open(['url'=>'/admin/send-rapport'])!!}
		<label for="">Date</label>
		<input type="text" name="date" id="la-date" class="uk-input uk-margin-small" placeholder="Date" value="">
		<select name="vendeurs" id="vendeurs" class="uk-select"></select>
		<ul uk-accordion="multiple: true">
		    <li class="uk-open">
		        <a class="uk-accordion-title" href="#">Recrutement</a>
		        <div class="uk-accordion-content">
		            {!!Form::text('quantite_recrutement','',['class'=>'uk-input uk-margin-small','placeholder'=>'Quantite recrutement'])!!}
		            {!!Form::text('ttc_recrutement','',['class'=>'uk-input uk-margin-small','placeholder'=>'Montant TTC Recrutement'])!!}
		        </div>
		    </li>
		    <li>
		        <a class="uk-accordion-title" href="#">Migration</a>
		        <div class="uk-accordion-content">
		           {!!Form::text('quantite_migration','',['class'=>'uk-input uk-margin-small','placeholder'=>'Quantite Migration'])!!}
		        </div>
		    </li>
		    <li>
		        <a class="uk-accordion-title" href="#">Reabonnement</a>
		        <div class="uk-accordion-content">
		            {!!Form::text('ttc_reabonnement','',['class'=>'uk-input uk-margin-small','placeholder'=>'Montant TTC Reabonnement'])!!}
		        </div>
		    </li>
		</ul>
		<button type="submit" class="uk-button-default uk-border-rounded"> valider<span uk-icon="check"></span></button>
		{!!Form::close()!!}
	</div>
</div>
@endsection
@section("script")
<script type="text/javascript">
	$(function() {

		var form = $adminPage.makeForm("{{csrf_token()}}","{{url()->current()}}","");

		form.on('submit',function (e) {
			e.preventDefault();
			$("#vendeurs").html('');
			$.ajax({
				url : $(this).attr('action'),
				type : 'post',
				data : $(this).serialize(),
				dataType : 'json'
			})
			.done(function(data) {
				//
				var options = [];
				$(data).each(function (index,element) {
					options[index] = $("<option></option>");
					options[index].html(element.username+' ( '+element.agence+' ) ');
					options[index].val(element.username);
					$("#vendeurs").append(options[index]);
				});
			}).
			fail(function (data) {
				console.log(data);
			});


		});
		form.submit();

		$("#la-date").datepicker({
			dateFormat: "dd-mm-yy"
		});
	});
</script>
@endsection
