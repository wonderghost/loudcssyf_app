@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3>
			<a href="{{url('/user')}}" uk-tooltip="Dashboard" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Toute les commandes</h3>
			 <hr class="uk-divider-small">
			 @if(session("_errors"))
			 <div class="uk-alert-danger" uk-alert>
				 <a href="#" class="uk-alert-close" uk-close></a>
				 <p>{{session('_errors')}}</p>
			 </div>
			 @endif
			 <table class="uk-table uk-table-divider">
			 	<thead>
			 		<tr>
			 			<th>Date</th>
			 			<th>Article</th>
			 			<th>Quantite</th>
			 			<th>Numero Recu</th>
			 			<th>Status</th>
			 			<th>-</th>
			 		</tr>
			 	</thead>
			 	<tbody id="list-command"></tbody>
			 </table>
	</div>
</div>
@endsection

@section('script')
<script type="text/javascript">
	$(function () {
		var form = $adminPage.makeForm("{{csrf_token()}}","{{url()->current()}}","");
		form.on('submit',function(e) {
			e.preventDefault();
			$.ajax({
				url : $(this).attr('action'),
				type : $(this).attr('method'),
				dataType : 'json',
				data : $(this).serialize()
			})
			.done(function (data) {
				$adminPage.createTableCommandRow(data,['date','item','quantite','numero_recu','status','details'],$("#list-command"));
			})
			.fail(function (data) {
				Uikit.modal.alert(data).then(function () {
					$(location).attr('href',"{{url()->current()}}");
				})
			});
		});

		form.submit();
		setTimeOut(function () {
			form.submit();
		});
	});
</script>
@endsection
