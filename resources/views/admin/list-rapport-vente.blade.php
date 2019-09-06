@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-padding-small">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Tous les Rapports</h3>
		<hr class="uk-divider-small">

			{!!Form::open()!!}
		<div class="uk-child-width-1-4@m" uk-grid>
			<div>

				<span uk-icon="icon:search"></span> {!!Form::label('Search')!!}

				{!!Form::text('search','',['class'=>'uk-input uk-margin-small','placeholder'=>'Search...'])!!}
			</div>
			<div>
				<span uk-icon="icon:calendar"></span> {!!Form::label('Date')!!}
				{!!Form::text('date','',['class'=>'uk-input uk-margin-small', 'placeholder'=>'date...'])!!}
			</div>
			<div>
				<span uk-icon="icon:location"></span> {!!Form::label('Vendeurs')!!}
				<select name="vendeurs" class="uk-select uk-margin-small">
				</select>
			</div>
			<div>
				{!!Form::label('Commission du jour (GNF)')!!}
				{!!Form::text('commission_jour','N/A',['class'=>'uk-input uk-margin-small uk-text-center','disabled'])!!}
			</div>
		</div>
		{!!Form::close()!!}

		<table class="uk-table uk-table-divider">
			<thead>
				<tr>
					<th>Date</th>
					<th>Recrutement (GNF/ Qte)</th>
					<th>Migration (Qte)</th>
					<th>Reabonnement (GNF)</th>
					<th>Vendeurs</th>
					<th>agence</th>
					<th>Commission (GNF)</th>
					<th>-</th>
				</tr>
			</thead>
			<tbody id="rapport-list"><div id="loader" uk-spinner></div></tbody>
		</table>
	</div>
</div>
@endsection
@section("script")
<script type="text/javascript">
	$(function() {
		var form = $adminPage.makeForm("{{csrf_token()}}","{{url('/admin/get-rapport')}}","");
		form.on('submit',function (e) {
			e.preventDefault();

			$.ajax({
				url  : $(this).attr('action'),
				type : $(this).attr('method'),
				data : $(this).serialize(),
				dataType : 'json'
			})
			.done(function (data) {
				$("#loader").hide(200);
				$adminPage.createTableRapportVente(data,['date','recrutement','migration','reabonnement','vendeurs','commission','agence','details'],$("#rapport-list"));
			})
			.fail(function (data) {
				console.log(data);
			});
		});

		form.submit();

		
	});
</script>
@endsection