@extends('layouts.app_users')

@section('user_content')
<div class="uk-section">
	<div class="uk-container">
		<h3><a href="{{url('/user/add-client')}}" uk-tooltip="Nouveau Client" uk-icon="icon:arrow-left;ratio:1.5"></a> Repertoire de Contact</h3>
		<hr class="uk-divider-small">

		<table class="uk-table uk-table-divider">
			<thead>
				<tr>
					<th>Nom</th>
					<th>Prenom</th>
					<th>Email</th>
					<th>Phone</th>
					<th>Adresse</th>
				</tr>
			</thead>
			<tbody id="list-cli"></tbody>
		</table>
	</div>
</div>
@endsection

@section('script')
<script type="text/javascript">
	$(function() {
		var form = $adminPage.makeForm("{{csrf_token()}}","{{url('/user/get-client')}}","all");
		form.on('submit',function(e) {	
			e.preventDefault();

			$.ajax({
				url : $(this).attr('action'),
				type : $(this).attr('method'),
				data : $(this).serialize(),
				dataType : 'json'
			})
			.done(function(data) {
				$adminPage.createTableClient(data,['nom','prenom','email','phone','adresse'],$('#list-cli'));
			})
			.fail(function(data) {
				console.log(data);
			})
		});
			form.submit();
	});
</script>
@endsection