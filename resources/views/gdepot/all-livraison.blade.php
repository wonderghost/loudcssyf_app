@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
  <div class="uk-container">
    <h3>
			<a href="{{url()->previous()}}" uk-tooltip="Retour" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Commandes</h3>
       <hr class="uk-divider-small">
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

@endsection
