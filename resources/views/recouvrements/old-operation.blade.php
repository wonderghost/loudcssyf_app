@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
  <div class="uk-container">
    <h3><a href="" uk-tooltip="Nouveau Client" uk-icon="icon:arrow-left;ratio:1.5"></a> Recouvrements</h3>
		<hr class="uk-divider-small">
    @if($errors->any())
    @foreach($errors->all() as $error)
    <div class="uk-alert-danger uk-border-rounded uk-box-shadow-small uk-width-1-2@m" uk-alert>
      <a href="#" class="uk-alert-close" uk-close></a>
      <p>{{$error}}</p>
    </div>
    @endforeach
    @endif
    @if(session('_errors'))
    <div class="uk-alert-danger uk-border-rounded uk-box-shadow-small uk-width-1-2@m" uk-alert>
      <a href="#" class="uk-alert-close" uk-close></a>
      <p>{{session('_errors')}}</p>
    </div>
    @endif

    @if(session('success'))
    <div class="uk-alert-success uk-border-rounded uk-box-shadow-small uk-width-1-2@m" uk-alert>
      <a href="#" class="uk-alert-close" uk-close></a>
      <p>{{session('success')}}</p>
    </div>
    @endif
    <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-fade">
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Nouveau Recouvrement</a></li>
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Tous les recouvrements</a></li>
        <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Toutes les transactions</a></li>
    </ul>
    <ul class="uk-switcher uk-margin">
      <li>
        {!!Form::open(['url'=>'/user/recouvrement/add','class'=>'uk-width-1-2@m'])!!}
        <h3>Enregistrer un recouvrement</h3>
        {!!Form::label('Vendeurs')!!}
        <select class="uk-select uk-border-rounded uk-margin-small" id="vendeurs" name="vendeurs">
          <option value="">--Selectionnez un vendeur --</option>
          @if($users)
          @foreach($users as $value)
          <option value="{{$value->username}}">{{$value->localisation}}</option>
          @endforeach
          @endif
        </select>
        {!!Form::label("Montant Dû")!!}
        {!!Form::text('montantdu',0,['class'=>'uk-input uk-margin-small uk-border-rounded','id'=>'montant-du','disabled'])!!}
        {!!Form::hidden('montant_du',0,['id'=>'montantdu'])!!}
        {!!Form::label('Montant')!!}
        {!!Form::number('montant','',['class'=>'uk-input uk-margin-small uk-border-rounded'])!!}
        {!!Form::label('Numero Recu')!!}
        {!!Form::text('numero_recu','',['class'=>'uk-input uk-margin-small uk-border-rounded'])!!}
        {!!Form::submit('validez',['class'=>'uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small'])!!}
        {!!Form::close()!!}
      </li>
      <li>
        <!-- TOUS LES RECOUVREMENTS -->
        <div class="uk-grid-small uk-grid" uk-grid>
          <div class="uk-width-1-4@m">
            <label for=""><span uk-icon= " icon : search"></span> Recherche</label>
            <input type="text" name="" value="" class="uk-input uk-border-rounded">
          </div>
          <div class="uk-width-1-4@m">
            <label for=""><span uk-icon= " icon : users"></span> Vendeurs</label>
            <select class="uk-select uk-border-rounded" name="" id="user-filter-recouvrement">
              <option value="all">Tous les Vendeurs</option>
              @if($users)
              @foreach($users as $user)
              <option value="{{$user->username}}">{{$user->localisation}}</option>
              @endforeach
              @endif
            </select>
          </div>
          <div class="uk-width-1-4@m">
            <label for=""><span uk-icon="icon : calendar"></span> Du</label>
            <input type="date" name="debut" value="" class="uk-input uk-border-rounded">
          </div>
          <div class="uk-width-1-4@m">
            <label for=""><span uk-icon="icon : calendar"></span> Au</label>
            <input type="date" name="fin" value="" class="uk-input uk-border-rounded">
          </div>
      </div>
      <div uk-spinner class="loader" style="display : none;"></div>
        <table class="uk-table uk-table-small uk-table-divider uk-table-hover uk-table-striped uk-table-responsive">
          <thead>
            <tr>
              <th>Id</th>
              <th>Date</th>
              <th>Vendeurs</th>
              <th>Montant</th>
              <th>Numero Recu</th>
            </tr>
          </thead>
          <tbody id="all-recouvrement"></tbody>
        </table>
        <!-- // -->
      </li>
      <li>
        <!-- TOUTES LES TRANSACTIONS -->
        <div class="uk-grid-small uk-grid" uk-grid>
          <div class="uk-width-1-4@m">
            <label for=""><span uk-icon= " icon : users"></span> Vendeurs</label>
            <select class="uk-select uk-border-rounded " id="user-filter-transaction">
              <option value="all">Tous les Vendeurs</option>
              @if($users)
              @foreach($users as $user)
              <option value="{{$user->username}}">{{$user->localisation}}</option>
              @endforeach
              @endif
            </select>
          </div>
          <div class="uk-width-1-4@m">
            <label for=""><span uk-icon= " icon : info"></span> Status</label>
          <select class="uk-select uk-border-rounded" id="state-filter-transaction" name="">
            <option value="all">all</option>
            <option value="recouvre">Recouvre</option>
            <option value="non_recouvre">Non Recouvre</option>
          </select>
        </div>
        <div class="uk-width-1-4@m">
          <label for=""> <span uk-icon="icon : calendar"></span> Du</label>
          <input type="date" name="transaction-date-debut" value="" class="uk-input uk-border-rounded">
        </div>
        <div class="uk-width-1-4@m">
          <label for=""> <span uk-icon="icon : calendar"></span> Au</label>
          <input type="date" name="transaction-date-fin" value="" class="uk-input uk-border-rounded">
        </div>
      </div>
      <div uk-spinner class="loader" style="display:none"></div>
        <table class="uk-table uk-table-striped uk-table-small uk-table-hover uk-table-divider uk-table-responsive">
          <thead>
            <tr>
              <th>Date</th>
              <th>Expediteur</th>
              <th>Destinataire</th>
              <th>Montant</th>
              <th>Type</th>
              <th>Status de recouvrement</th>
            </tr>
          </thead>
          <tbody id="all-transactions"></tbody>
        </table>
        <!-- // -->
      </li>
  </ul>

  </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
  $(function () {
    $logistique.allTransactionForRecouvrement("{{csrf_token()}}","{{url('/user/recouvrement/all-transactions')}}","all")

    $logistique.allRecouvrement("{{csrf_token()}}","{{url('/user/recouvrement/all-recouvrement')}}","all")
    // RECUPERATION DU MONTANT DU

    $("#vendeurs").on('change',function(e) {
      $logistique.getMontantDuRecouvrement("{{csrf_token()}}","{{url('/user/recouvrement/get-montant-du')}}",$(this).val())
    })

    // FILTRE UTILISATEURS POUR LE LISTING DES RECOUVREMENTS
    $("#user-filter-recouvrement").on('change',function (e) {
      $logistique.allRecouvrement("{{csrf_token()}}","{{url('/user/recouvrement/all-recouvrement')}}",$(this).val())
    })

    $("#user-filter-transaction").on('change',function (e) {
      $logistique.allTransactionForRecouvrement("{{csrf_token()}}","{{url('/user/recouvrement/all-transactions')}}",$(this).val())
    })
    $("#state-filter-transaction").on('change',function (e) {
      $logistique.allTransactionForRecouvrement("{{csrf_token()}}","{{url('/user/recouvrement/all-transactions')}}",$("#user-filter-transaction").val(),$(this).val())
    })


  })
</script>
@endsection
