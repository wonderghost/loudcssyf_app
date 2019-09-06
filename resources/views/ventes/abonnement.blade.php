@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3>
			<a href="{{url('/user')}}" uk-tooltip="Dashboard" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Activer un abonnement</h3>
		<hr class="uk-divider-small">
		{!!Form::open(['url'=>url()->current(),'id'=>'search-for-abonn'])!!}
		<div class="uk-child-width-1-2@m" uk-grid>
			<div>
				{!!Form::text('search','',['class'=>'uk-input uk-margin-small','placeholder'=>'S/N,Phone,Numero client','id'=>'search-zone'])!!}
			</div>
		</div>
		<button class="uk-button-default uk-border-rounded" type="submit" id="search-button">recherche <span uk-icon="icon:search;ratio:.8"></span></button>
		{!!Form::close()!!}
		<!-- RESULTAT DE LA RECHERCHE -->
		<table class="uk-table">
			<thead>
				<tr>
					<th>MATERIEL</th>
					<th>NUMERO CLIENT</th>
					<th>TELEPHONE</th>
					<th>NOM</th>
					<th>PRENOM</th>
				</tr>
			</thead>
			<tbody id="search-result"></tbody>
		</table>
	</div>
</div>
@endsection

@section('script')
<script type="text/javascript">
	$(function() {
		$("#search-zone").focus();
		if($.trim($("#search-zone").val()) == "") {
			$("#search-button").attr('disabled','disabled');
		}

		$("#search-zone").on('keyup focus blur',function() {
			console.log($(this).val());
			if($.trim($(this).val()) == "") {
				$("#search-button").attr('disabled','disabled');
			} else {
				$("#search-button").removeAttr('disabled');
			}
		});

		$("#search-for-abonn").on('submit',function(e) {
			e.preventDefault();
			$.ajax({
				url : $(this).attr('action'),
				type  : $(this).attr('method'),
				data : $(this).serialize(),
				dataType : 'json',
			})
			.done(function(data) {
				if(data && data!="fail") {
					
				} else {
					UIkit.modal.alert("<div class='uk-alert uk-alert-danger'>AUCUNE CORRESPONDANCE!</div>");
				}
			})
			.fail(function(data) {
				console.log(data);
			});
		});
	});
</script>
@endsection