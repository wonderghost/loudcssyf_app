@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-padding-small">
		<h3>
			<a href="{{url('/user')}}" uk-tooltip="Dashboard" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Toute les commandes</h3>
			 <hr class="uk-divider-small">
			 <div class="uk-container">
			 @if(session("_errors"))
			 <div class="uk-alert-danger" uk-alert>
				 <a href="#" class="uk-alert-close" uk-close></a>
				 <p>{{session("_errors")}}</p>
			 </div>
			 @endif
			 @if(session("success"))
			 <div class="uk-alert-success" uk-alert>
				 <a href="#" class="uk-alert-close" uk-close></a>
				 <p>{{session("success")}}</p>
			 </div>
			 @endif
		 </div>
			 <table class="uk-table uk-table-divider">
			 	<thead>
			 		<tr>
			 			<th>Date</th>
			 			<th>Vendeur</th>
			 			<th>Item</th>
			 			<th>Quantite</th>
			 			<th>Numero_Recu</th>
			 			<th>Paraboles a livrer</th>
			 			<th>Status</th>
			 			<th>Recu</th>
			 			<th class="uk-text-center" colspan="2">-</th>
			 		</tr>
			 	</thead>
			 	<tbody id="list-commands"></tbody>
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
				$adminPage.createTableCommandRowLogistique(data,['date','vendeur','item','quantite','numero_recu','parabole','status','recu','confirm'],$("#list-commands"),"","{{url('/')}}");
			})
			.fail(function (data) {
				Uikit.modal.alert(data).then(function () {
					$(location).attr('href',"{{url()->current()}}");
				})
			});
		});
		form.submit();
		setInterval(function() {
			form.submit();
		},20000);

	});
</script>
@endsection
