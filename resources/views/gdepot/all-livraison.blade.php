@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
  <div class="uk-container">
    <h3>
			<a href="{{url()->previous()}}" uk-tooltip="Retour" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Livraisons</h3>
       <hr class="uk-divider-small">
       <table class="uk-table uk-table-hover uk-table-striped uk-table-small">
        <thead>
          <tr>
            <th>Date</th>
            <th>Vendeur</th>
            <th>Article</th>
            <th>Quantite</th>
            <!-- <th>Numero_Recu</th> -->
            <!-- <th>Paraboles a livrer</th> -->
            <th>Status</th>
            <!-- <th>Recu</th> -->
            <th class="uk-text-center" colspan="2">-</th>
          </tr>
        </thead>
        <tbody id="livraison"></tbody>
       </table>
  </div>
</div>
@endsection
@section('script')
<script type="text/javascript">
  $(function () {
    $logistique.ListLivraison($adminPage,"{{csrf_token()}}","{{url('/user/livraison')}}","")
    
  })
</script>
@endsection
