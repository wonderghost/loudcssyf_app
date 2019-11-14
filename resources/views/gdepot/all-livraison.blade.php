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
            <th>Commande</th>
            <th>Quantite</th>
            <th>Status</th>
            <th class="uk-text-center" colspan="2">-</th>
          </tr>
        </thead>
        <tbody id="livraison"></tbody>
       </table>
       <!-- Confirmer le mot de passe et le code de confirmation -->
       <!-- Selection et saisi des numeros de series -->
       <div id="serials" uk-modal>
         <div class="uk-modal-dialog">
           <div class="uk-modal-header">
             <h3 class="uk-text-center"></h3>
           </div>
           <div class="uk-modal-body">
             {!!Form::open(['url'=>'/user/livraison/confirm','id'=>'confirm-form'])!!}
             <ul uk-accordion="collapsible: false">
              <li>
                  <a class="uk-accordion-title" href="#">Numero de serie</a>
                  <div class="uk-accordion-content">
                    <div class="uk-alert-info uk-border-rounded uk-box-shadow-small" uk-alert>
                      <!-- <a href="#" class="uk-alert-close" uk-close></a> -->
                      <p>Remplissez les champs vides !</p>
                    </div>
                    <div id="all-serials"> <div uk-spinner>Chargement en cours ...</div> </div>
                  </div>
              </li>
              <li>
                <a href="#" class="uk-accordion-title">Suivant</a>
                <div class="uk-accordion-content">
                  {!!Form::label('Entrez le code de confirmation de la livraison')!!}
                  {!!Form::text('confirm_code','',['class'=>'uk-input uk-margin-small','placeholder'=>'Code de Confirmation'])!!}
                  {!!Form::label('Confirmez Votre Mot de passe')!!}
                  {!!Form::password('password',['class'=>'uk-input uk-margin-small','placeholder'=>'Entrez votre mot de passe'])!!}
                  {!!Form::submit('Confirmer',['class'=>'uk-button uk-button-primary uk-border-rounded uk-box-shadow-small'])!!}
                </div>
              </li>
            </ul>
            {!!Form::close()!!}
          </div>
         </div>
       </div>
<!-- // -->
</div>
</div>
@endsection
@section('script')
<script type="text/javascript">
  $(function () {
    $logistique.ListLivraison($adminPage,"{{csrf_token()}}","{{url('/user/livraison')}}","")

    // Envoi du formulaire d'envoi des numeros de serie

  })
</script>
@endsection
