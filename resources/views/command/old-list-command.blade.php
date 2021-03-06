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

			 <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-fade">
			     <li><a  class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Materiel</a></li>
			     <li><a  class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Cga</a></li>
			     <li><a  class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Rex</a></li>
					 @if(Auth::user()->type == 'v_standart')
			     <li><a  class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Afrocash Semi Grossiste</a></li>
					 @endif
			 </ul>

			 <ul class="uk-switcher uk-margin">
			    <li>
						<!-- COMMANDES MATERIELS -->
						<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
						 <thead>
							 <tr>
								 <th>Date</th>
								 <th>Article</th>
								 <th>Quantite</th>
								 <th>Numero Recu</th>
								 <th>Status</th>
								 <th>id</th>
								 <th>Status de livraison</th>
								 <th>-</th>
							 </tr>
						 </thead>
						 <tbody id="list-command"></tbody>
						</table>
					</li>
					<li>
						<!-- COMMANDES CGA -->
						<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
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
						<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
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
						<table class="uk-table uk-table-small uk-table-divider uk-table-striped uk-table-hover uk-table-responsive">
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

		function listMaterialCommande() {
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

					$logistique.dataList(data,$("#list-command"))
					data.forEach(function (element , index) {
						if(element.status == 'confirmer') {
							console.log($("#list-command .row .col:nth-child(5)"))
							$("#list-command .row:eq("+index+") .col:nth-child(5)").addClass('uk-text-success')
						} else {
							$("#list-command .row:eq("+index+") .col:nth-child(5)").addClass('uk-text-danger')
						}
						// creation du boutton details
						var detail = $("<a></a>") , col = $("<td></td>")
						detail.attr('href','/user/details-command/'+element.id_commande)
						detail.attr('uk-icon','icon : more')
						detail.addClass('uk-button uk-margin-small uk-button-small uk-button-default uk-box-shadow-small uk-border-rounded uk-text-capitalize')
						col.addClass('col')
						detail.text('details')
						col.append(detail)
						$("#list-command .row:eq("+index+")").append(col)
					})

				})
				.fail(function (data) {
					alert(data.responseJSON.message)
					$(location).attr('href','/')
				});
			});

			form.submit();
		}
		setTimeout(function () {
			listMaterialCommande()
		}, 20000);
		listMaterialCommande()
	});
</script>
@endsection
