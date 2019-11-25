@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
  <div class="uk-container">
    <h3>
			<a href="{{url()->previous()}}" uk-tooltip="Retour" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Livraisons</h3>
       <hr class="uk-divider-small">
       @if(session('success'))
       <div class="uk-alert-success uk-box-shadow-small uk-border-rounded" uk-alert>
         <a href="#" class="uk-alert-close" uk-close></a>
         <p>{{session('success')}}</p>
       </div>
       @endif
       @if(session('_errors'))
       <div class="uk-alert-danger uk-box-shadow-small uk-border-rounded" uk-alert>
         <a href="#" class="uk-alert-close" uk-close></a>
         <p>{{session('_errors')}}</p>
       </div>
       @endif
       @if($errors->any())
       @foreach($errors->all() as $error)
       <div class="uk-alert-danger uk-box-shadow-small uk-border-rounded" uk-alert>
         <a href="#" class="uk-alert-close" uk-close></a>
         <p>{{$error}}</p>
       </div>
       @endforeach
       @endif
       <ul uk-accordion="collapsible: false">
           <li>
               <a class="uk-accordion-title" href="#">Livraison en Attente</a>
               <div class="uk-accordion-content">
                 <table class="uk-table uk-table-hover uk-table-striped uk-table-small">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Vendeur</th>
                      <th>Article</th>
                      <th>Commande</th>
                      <th>Quantite</th>
                      <th>Status</th>
                      <th class="uk-text-center">-</th>
                    </tr>
                  </thead>
                  <tbody id="livraison"></tbody>
                 </table>
               </div>
           </li>
           <li>
             <a href="#" class="uk-accordion-title">Livraison Confirmee</a>
             <div class="uk-accordion-content">
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
                <tbody id="livraison-confirmee"></tbody>
               </table>
             </div>
           </li>
         </ul>
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
                  <input type="hidden" name="livraison" id="livraison-id" value="">
                  <input type="hidden" name="with_serial" id="with-serial" value="">
                  <input type="hidden" name="quantite" id="quantite" value="">
                  {!!Form::label('Entrez le code de confirmation de la livraison')!!}
                  {!!Form::text('confirm_code','',['class'=>'uk-input uk-margin-small','placeholder'=>'Code de Confirmation'])!!}
                  {!!Form::label('Confirmez Votre Mot de passe')!!}
                  {!!Form::password('password',['class'=>'uk-input uk-margin-small','placeholder'=>'Entrez votre mot de passe'])!!}
                  {!!Form::submit('Confirmer',['class'=>'uk-button uk-button-primary uk-border-rounded uk-box-shadow-small','id'=>'confirm-button-livraison','disabled'=>''])!!}
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
    $logistique.ListLivraison($adminPage,"{{csrf_token()}}","{{url('/user/livraison')}}","{{url('/user/livraison/validate-serial')}}")
    // Envoi du formulaire d'envoi des numeros de serie
    $logistique.ListLivraisonConfirmee($adminPage,"{{csrf_token()}}","{{url('/user/livraison-confirmee')}}")



  })
</script>
@endsection
