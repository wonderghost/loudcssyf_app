@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Compte Credit ( CGA / REX / AFROCASH)</h3>
		<hr class="uk-divider-small">
		<account></account>
			<!-- <li>
				<div class="uk-grid-small uk-grid" uk-grid>
          <div class="uk-width-1-2@m">
            <label for=""><span uk-icon= " icon : search"></span> Recherche</label>
            <input type="text" name="" value="" class="uk-input uk-border-rounded">
          </div>
          <div class="uk-width-1-2@m">
            <label for=""><span uk-icon= " icon : users"></span> Vendeurs</label>
            <select class="uk-select uk-border-rounded" name="">
              <option value="">Tous les Vendeurs</option>
            </select>
          </div>
      </div>
				<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
					<thead>
						<tr>
							<th>VENDEURS</th>
							<th>AFROCASH COURANT</th>
							<th>AFROCASH SEMI-GROSSISTE</th>
							<th>CGA</th>
							<th>REX</th>
						</tr>
					</thead>
					<tbody id="solde-vendeur"></tbody>
				</table>
			</li>
		</ul> --> 
	</div>
</div>

@endsection
