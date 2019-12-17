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
						<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
							<thead>
								<tr>
									<th>Date</th>
									<th>Montant</th>
									<th>Type</th>
									<th>Status</th>
									<th>Numero Recu</th>
									<th>Recu</th>
								</tr>
							</thead>
							<tbody>
								@if($cgacommande)
								@foreach($cgacommande as $value)
								@php
								$date = new Carbon\Carbon($value->created_at);
								$date->locale("fr_FR");
								@endphp
								<tr>
									<td>{{$date->toFormattedDateString()}} ({{$date-> diffForHumans()}})</td>
									<td>{{$value->montant}}</td>
									<td>{{$value->type}}</td>
									<td><span class="{{$value->status == 'validated' ? 'uk-alert-success' : 'uk-alert-danger'}}">{{$value->status}}</span>	</td>
									<td>-</td>
									<td>-</td>
								</tr>
								@endforeach
								@endif

							</tbody>
						</table>
						{{$cgacommande->links()}}
					</li>
					<li>
						<!-- COMMANDES REX -->
						<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
							<thead>
								<tr>
									<th>Date</th>
									<th>Montant</th>
									<th>Type</th>
									<th>Status</th>
									<th>Numero Recu</th>
									<th>Recu</th>
								</tr>
							</thead>
							<tbody>
								@if($rexCommande)
								@foreach($rexCommande as $value)
								@php
								$date = new Carbon\Carbon($value->created_at);
								$date->locale("fr_FR");
								@endphp
								<tr>
									<td>{{$date->toFormattedDateString()}} ({{$date-> diffForHumans()}})</td>
									<td>{{$value->montant}}</td>
									<td>{{$value->type}}</td>
									<td><span class="{{$value->status == 'validated' ? 'uk-alert-success' : 'uk-alert-danger'}}">{{$value->status}}</span>	</td>
									<td>-</td>
									<td>-</td>
								</tr>
								@endforeach
								@endif
							</tbody>
						</table>
						{{$rexCommande->links()}}
					</li>
					<li>
						<!-- AFRO CASH SEMI GROSSISTE -->
						<table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover">
							<thead>
								<tr>
									<th>Date</th>
									<th>Montant</th>
									<th>Type</th>
									<th>Status</th>
									<th>Numero Recu</th>
									<th>Recu</th>
								</tr>
							</thead>
							<tbody>
								@if($commandes)
								@foreach($commandes as $value)
								@php
								$date = new Carbon\Carbon($value->created_at);
								$date->locale("fr_FR");
								@endphp
								<tr>
									<td>{{$date->toFormattedDateString()}} ({{$date-> diffForHumans()}})</td>
									<td>{{number_format($value->montant)}}</td>
									<td>{{$value->type}}</td>
									<td><span class="{{$value->status == 'validated'? 'uk-alert-success' : 'uk-alert-danger'}}">{{$value->status}}</span></td>
									<td>{{$value->numero_recu}}</td>
									<td>
										<div uk-lightbox>
		                    <a class="uk-button-default uk-border-rounded uk-box-shadow-small " href="{{asset('uploads/'.$value->recu)}}" data-caption="{{$value->numero_recu}}">voir le recu</a>
		                </div>
									</td>
								</tr>
								@endforeach
								@endif
							</tbody>
						</table>
						{{$commandes->links()}}
					</li>
			 </ul>
	</div>
</div>
@endsection

@section('script')
<script type="text/javascript">
	$(function () {
		$('.pagination').addClass('uk-pagination uk-flex-center')

		//
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
