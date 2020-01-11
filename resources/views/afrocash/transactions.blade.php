@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
  <div class="uk-container">
    <h3><a href="{{url('/')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Toutes les Transaction</h3>

  <ul uk-tab>
      <li><a href="#">Historique</a></li>
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
    $logistique.getListTransactionAfrocashForVendeurs("{{csrf_token()}}","{{url('/user/afrocash/all-transactions')}}","{{Auth::user()->username}}")
  })
</script>
@endsection
