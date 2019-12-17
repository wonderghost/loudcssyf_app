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
		<ul uk-tab>
		    <li><a href="#">Comptes</a></li>
		    <li><a href="#">SOLDES VENDEURS</a></li>
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
					<!-- SOLDE VENDEURS -->
					<table class="uk-table uk-tabl-divider uk-table-hover uk-table-striped uk-table-small">
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

	});
</script>
@elseif(Auth::user()->type == 'grex')
<script type="text/javascript">
	$(function() {

// #########
	$logistique.getSoldeVendeurCredit("{{csrf_token()}}","{{url()->current()}}")

	});
</script>
@endif
@endsection
