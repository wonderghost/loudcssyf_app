@extends('layouts.app_users')


@section('user_content')
<div class="uk-section uk-section-default">
<div class="uk-container uk-container-large">
  <h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Commandes Credit</h3>
  <hr class="uk-divider-small">
  @if($errors->any())
  @foreach($errors->all() as $error)
  <div class="uk-alert-danger uk-border-rounded uk-box-shadow-small" uk-alert>
    <a href="#" class="uk-alert-close" uk-close></a>
    <p>{{$error}}</p>
  </div>
  @endforeach
  @endif
  @if(session('success'))
  <div class="uk-alert-success uk-border-rounded uk-box-shadow-small" uk-alert>
    <a href="#" class="uk-alert-close" uk-close></a>
    <p>{{session('success')}}</p>
  </div>
  @endif
  @if(session('_error'))
  <div class="uk-alert-danger uk-border-rounded uk-box-shadow-small" uk-alert>
    <a href="#" class="uk-alert-close" uk-close></a>
    <p>{{session("_error")}}</p>
  </div>
  @endif
  <!-- FILTRE POUR LA RECHERCHE RAPIDE -->

  {!!Form::open(['url'=>'/user/cga-credit/commande-filter','class'=>'uk-grid-small','uk-grid','id'=>'command-credit-filter'])!!}
  <div class="uk-width-1-6@m">
    <label for=""><span uk-icon="icon : calendar"></span> Du</label>
    {!!Form::date('debut_date',"",['class'=>'uk-input uk-border-rounded uk-margin-small'])!!}
  </div>
  <div class="uk-width-1-6@m">
    <label for=""><span uk-icon="icon : calendar"></span> Au</label>
    {!!Form::date('fin_date',"",['class'=>'uk-input uk-border-rounded uk-margin-small'])!!}
  </div>
  <div class="uk-width-1-6@m">
    <label for=""><span uk-icon="icon : users"></span> Vendeurs</label>
    <select name="vendeurs" class="uk-select uk-border-rounded uk-margin-small" name="">
      <option value="all">Tous</option>
      @if($users)
      @foreach($users as $user)
      <option value="{{$user->username}}">{{$user->localisation}}</option>
      @endforeach
      @endif
    </select>
  </div>
  <div class="uk-width-1-6@m">
    <label for=""><span uk-icon="icon : info"></span> Credit</label>
    <select class="uk-select uk-border-rounded uk-margin-small" name="type_credit">
      <option value="all">Tous</option>
      <option value="afro_cash_sg">AFROCASH</option>
      <option value="cga">CGA</option>
    </select>
    {!!Form::submit('ok',['class'=>'uk-button uk-button-primary uk-button-small uk-box-shadow-small uk-border-rounded uk-position-absolute uk-margin-small-top uk-margin-left'])!!}
  </div>
  {!!Form::close()!!}
  <a href="{{url()->current()}}" uk-tooltip="retirer le filtre" class="uk-float uk-float-right"> <i class="material-icons">delete_sweep</i> </a>
  <!-- // -->
  <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-fade">
      <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Commandes en attente de validation</a></li>
      <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Commandes deja validee</a></li>
      <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Commandes Annulee</a></li>
  </ul>
  <ul class="uk-switcher uk-margin">
      <li>
        <!-- COMMANDES EN ATTENTE DE VALIDATION -->
        <div class="loader" uk-spinner>

        </div>
        <table class="uk-table uk-table-divider uk-table-striped uk-table-small uk-table-hover uk-table-responsive">
          <thead>
            <tr>
              <th>date</th>
              <th>vendeurs</th>
              <th>type</th>
              <th>montant (GNF)</th>
              <th>status</th>
              <th>Numero recu</th>
              <th>recu</th>
              <th>-</th>
            </tr>
          </thead>
          <tbody id="unvalidated"></tbody>
        </table>
      </li>
      <li>
        <!-- COMMANDES DEJA VALIDEE -->
        <div class="loader" uk-spinner>

        </div>
        <table class="uk-table uk-table-divider uk-table-striped uk-table-small uk-table-hover uk-table-responsive">
          <thead>
            <tr>

              <th>date</th>
              <th>vendeurs</th>
              <th>type</th>
              <th>montant</th>
              <th>status</th>
              <th>Numero recu</th>
              <th>recu</th>
            </tr>
          </thead>
          <tbody id="validated"></tbody>
        </table>
      </li>
      <li>
        <!-- COMMANDES ANNULEE -->
        <table class="uk-table uk-table-striped uk-table-hover uk-table-divider uk-table-small uk-table-responsive">
          <thead>
            <tr>
              <th>Date</th>
              <th>Vendeurs</th>
              <th>type</th>
              <th>montant</th>
              <th>status</th>
              <th>numero recu</th>
              <th>recu</th>
            </tr>
          </thead>
          <tbody id="aborted"></tbody>
        </table>
      </li>
  </ul>
  <!-- MODAL VALIDATION  -->
  <div id="modal-validation" uk-modal>
      <div class="uk-modal-dialog uk-modal-body">
          <button class="uk-modal-close-default" type="button" uk-close></button>
          <p>Vous confirmez l'envoi de : <span id="validation-montant" class="uk-text-bold"></span> a : <span id="validation-vendeur" class="uk-text-bold"></span> </p>
          {!!Form::open(['url'=>'/user/send-afrocash'])!!}
          {!!Form::hidden('commande','',['id'=>'validation-commande'])!!}
          {!!Form::hidden('type_commande','',['id'=>'validation-type-commande'])!!}
          {!!Form::label('Saisissez le montant')!!}
          {!!Form::text('montant','',['class'=>'uk-input uk-margin-small uk-border-rounded','autofocus'])!!}
          {!!Form::label('Confirmez le mot de passe')!!}
          {!!Form::password('password_confirmed',['class'=>'uk-input uk-margin-small uk-border-rounded'])!!}
          {!!Form::submit('validez',['class'=>'uk-button uk-button-primary uk-border-rounded  uk-box-shadow-small'])!!}
          {!!Form::close()!!}
      </div>
  </div>
  <!-- // -->
</div>
</div>
@endsection
@section('script')
@if(Auth::user()->type == 'gcga')
<script type="text/javascript">
  $(function () {

  var intervalId =   setInterval(function() {
      $logistique.getCommandForCga("{{csrf_token()}}","{{url()->current()}}","{{url('/user/credit-cga/abort-commandes')}}")
		},10000);

    $logistique.getCommandForCga("{{csrf_token()}}","{{url()->current()}}","{{url('/user/credit-cga/abort-commandes')}}")
    $logistique.commandCreditFilter(intervalId)
  })
</script>
@elseif(Auth::user()->type == 'grex')
<script type="text/javascript">
  $(function() {

    setInterval(function() {
      $logistique.getCommandForCga("{{csrf_token()}}","{{url()->current()}}")
    },10000);

    $logistique.getCommandForCga("{{csrf_token()}}","{{url()->current()}}")
  })
</script>
@endif
@endsection
