@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3>
			<a href="{{url('/user')}}" uk-tooltip="Dashboard" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Toute les commandes</h3>
			 <hr class="uk-divider-small">
			 @if(session("_errors"))
			 <div class="uk-alert-danger uk-border-rounded uk-box-shadow-small" uk-alert>
				 <a href="#" class="uk-alert-close" uk-close></a>
				 <p>{{session('_errors')}}</p>
			 </div>
			 @endif

			 <ul uk-tab>
			     <li><a href="#">Materiel</a></li>
			     <li><a href="#">Cga</a></li>
			     <li><a href="#">Rex</a></li>
					 @if(Auth::user()->type == 'v_standart')
			     <li><a href="#">Afrocash Semi Grossiste</a></li>
					 @endif
			 </ul>

			 <ul class="uk-switcher uk-margin">
			    <li>
						<!-- COMMANDES MATERIELS -->
						<table class="uk-table uk-table-divider">
						 <thead>
							 <tr>
								 <th>Date</th>
								 <th>Article</th>
								 <th>Quantite</th>
								 <th>Numero Recu</th>
								 <th>Status</th>
								 <th>id</th>
								 <th>-</th>
							 </tr>
						 </thead>
						 <tbody id="list-command"></tbody>
						</table>
					</li>
					<li>
						<!-- COMMANDES CGA -->
					</li>
					<li>
						<!-- COMMANDES REX -->
					</li>
					<li>
						<!-- AFRO CASH SEMI GROSSISTE -->
					</li>
			 </ul>
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
				$adminPage.createTableCommandRow(data,['date','item','quantite','numero_recu','status','id','details'],$("#list-command"));
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
