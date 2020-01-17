@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
  <div class="uk-container">
    <h3><a href="{{url('/')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Toutes les Transaction</h3>

    @if(Auth::user()->type == 'v_da' || Auth::user()->type == 'v_standart')
    <!-- FILTRE PAR DATE VISIBLE QUE POUR LES VENDEURS-->
    {!!Form::open(['url'=>'/user/afrocash/filter-transactions','uk-grid','id'=>'filter-transaction-afrocash'])!!}
    <div class="uk-width-1-3@m">
      <span uk-icon="icon : calendar"></span>{!!Form::label('Du')!!}
      {!!Form::date('date_debut','',['class'=>'uk-input uk-border-rounded'])!!}
    </div>
    <div class="uk-width-1-3@m">
      <span uk-icon="icon : calendar"></span>{!!Form::label('Au')!!}
      {!!Form::date('date_fin','',['class'=>'uk-input uk-border-rounded'])!!}
      {!!Form::submit('ok',['class'=>'uk-button uk-button-small uk-button-primary uk-visible@m uk-border-rounded uk-box-shadow-small uk-position-absolute uk-margin-small-left'])!!}
      {!!Form::submit('ok',['class'=>'uk-button uk-button-small uk-button-primary uk-hidden@m uk-border-rounded uk-box-shadow-small uk-margin-small'])!!}
    </div>
    {!!Form::close()!!}
    <a href="{{url()->current()}}" uk-tooltip="retirer le filtre" class="uk-float uk-float-right uk-visible@m"> <i class="material-icons">delete_sweep</i> </a>
    <a href="{{url()->current()}}" uk-tooltip="retirer le filtre" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small uk-margin-small uk-hidden@m"> retirer le filtre </a>
    @endif
    <!-- // -->
  <ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-fade">
      <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Historique</a></li>
  </ul>

  <ul class="uk-switcher uk-margin">
      <li>
        <!-- HISTORIQUE DES TRANSACTIONS -->

        @if(Auth::user()->type == 'gcga' || Auth::user()->type == 'admin')
        <table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
          <thead>
            <tr>
              <th>Date</th>
              <th>Expediteur</th>
              <th>Destinataire</th>
              <th>Montant</th>
            </tr>
          </thead>
          <tbody>
            @if($transactions)
            @foreach($transactions as $value)
            @php
            $date = new \Carbon\Carbon($value->created_at);
            $date->locale('fr_FR');
            @endphp
            <tr>
              <td>{{$date->toFormattedDateString()}} ({{$date-> diffForHumans()}})</td>
              @if($value->afrocash())
              <td>{{$value->afrocash()->vendeurs}}-{{$value->afrocash()->vendeurs()->localisation}}</td>
              @else
              <td>-</td>
              @endif
              @if($value->afrocashcredite())
              <td>{{$value->afrocashcredite()->vendeurs}}-{{$value->afrocashcredite()->vendeurs()->localisation}}</td>
              @else
              <td>-</td>
              @endif
              <td>{{number_format($value->montant)}}</td>
            </tr>
            @endforeach
            @endif
          </tbody>
        </table>
        {{$transactions->links()}}
        @else

        <table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
          <thead>
            <tr>
              <th>Date</th>
              <th>Expediteur</th>
              <th>Destinataire</th>
              <th>Montant</th>
            </tr>
          </thead>
          <tbody id="list-transactions"></tbody>
        </table>
        @endif
      </li>
  </ul>

</div>
</div>
@endsection

@section('script')
<script type="text/javascript">
  $(function (){
    $(".pagination").addClass('uk-pagination uk-flex-center')
    $logistique.getListTransactionAfrocashForVendeurs("{{csrf_token()}}","{{url('/user/afrocash/all-transactions')}}","{{Auth::user()->username}}")
    $logistique.filterAfrocashTransactionForVendeur()
  })
</script>
@endsection
