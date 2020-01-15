@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Compte Credit ( CGA / AFROCASH / REX)</h3>
		<hr class="uk-divider-small">
		@if(session('success'))
		<div class="uk-alert uk-alert-success">
			<button type="button" class="uk-align-right close-button"  uk-icon="icon:close"></button>
			<div>{{session('success')}}</div>
		</div>
		@endif
		@if(session('_errors'))
		<div class="uk-alert uk-alert-danger">
			<button type="button" class="uk-align-right close-button"  uk-icon="icon:close"></button>
			<div>{{session('_errors')}}</div>
		</div>
		@endif
		<ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-fade">
		    <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Comptes</a></li>
		    <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">SOLDES VENDEURS</a></li>
		</ul>

		<ul class="uk-switcher uk-margin">

			@if(Auth::user()->type == 'gcga')
		    <li>
					<!-- SOLDES DES COMPTES CGA ET AFROCASH -->
					<div class="uk-child-width-1-1@m uk-grid-divider " uk-grid>
						<div>

							<div class="uk-grid-small uk-text-lead" uk-grid>
									<div class="uk-width-expand uk-text-capitalize" uk-leader> CGA (GNF)</div>
									<div>{{number_format($solde)}}</div>
							</div>
							<div class="uk-grid-small uk-text-lead" uk-grid>
									<div class="uk-width-expand uk-text-capitalize" uk-leader> AFROCASH (GNF)</div>
									<div>{{number_format($afrocash)}}</div>
							</div>

						</div>
					</div>
				</li>
				@elseif(Auth::user()->type == 'grex')
				<li>
					<div class="uk-child-width-1-1@m uk-grid-divider " uk-grid>
						<div>

							<div class="uk-grid-small uk-text-lead" uk-grid>
									<div class="uk-width-expand uk-text-capitalize" uk-leader> REX (GNF)</div>
									<div>{{number_format($rex)}}</div>
							</div>
						</div>
					</div>
				</li>
				@endif
				<li>
					<!-- FILTRES -->
					<div class="" uk-grid>
						<div class="uk-width-1-2@m">
							<label for=""> <span uk-icon="icon : users"></span> Vendeurs</label>
							<select class="uk-select uk-border-rounded" name="">
								<option value="">Tous</option>
								<option value="v_da">Distributeur Agree</option>
								<option value="v_standart">Vendeurs Standarts</option>
							</select>
						</div>
						<div class="uk-width-1-2@m">
							<label for=""> <span uk-icon="icon : search"></span> Recherche</label>
							<input type="search" name="" value="" class="uk-input uk-border-rounded" placeholder="..." id="search-input"/>
						</div>
					</div>
					<!-- // -->
					<!-- SOLDE VENDEURS -->
					<table class="uk-table uk-tabl-divider uk-table-hover uk-table-striped uk-table-small uk-table-responsive">
						<thead>
							<tr>
								<th>Vendeurs</th>
								<th>Afrocash Courant</th>
								<th>Afrocash Semi Grossiste</th>
								<th>Cga</th>
								<th>Rex</th>
							</tr>
						</thead>
						<tbody id="solde-vendeur"></tbody>
					</table>
				</li>
		</ul>
	</div>
</div>

@endsection
@section('script')
@if(Auth::user()->type == 'gcga')
<script type="text/javascript">
	$(function() {

// #########
	$logistique.getSoldeVendeurCredit("{{csrf_token()}}","{{url()->current()}}")
	$("#search-input").on('keyup',function (e) {
		$logistique.getSoldeVendeurCredit("{{csrf_token()}}","{{url('/user/cga-credit/vendeurs-solde')}}",$(this).val())
	})
	});
</script>
@elseif(Auth::user()->type == 'grex')
<script type="text/javascript">
	$(function() {

// #########

	});
</script>
@endif
@endsection
