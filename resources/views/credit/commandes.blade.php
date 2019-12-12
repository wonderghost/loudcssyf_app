@extends('layouts.app_users')


@section('user_content')
<div class="uk-section uk-section-default">
<div class="uk-container">
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
  <ul uk-tab>
      <li><a href="#">Commandes en attente de validation</a></li>
      <li><a href="#">Commandes deja validee</a></li>
  </ul>
  <ul class="uk-switcher uk-margin">
      <li>
        <!-- COMMANDES EN ATTENTE DE VALIDATION -->
        <table class="uk-table uk-table-divider uk-table-striped uk-table-small uk-table-hover">
          <thead>
            <tr>
              <th>date</th>
              <th>vendeurs</th>
              <th>montant (GNF)</th>
              <th>type</th>
              <th>status</th>
              <th>Numero recu</th>
              <th>recu</th>
              <th>-</th>
            </tr>
          </thead>
          <tbody>
            @if($commandes)
            @foreach($commandes as $commande)
            @php
            $date = new Carbon\Carbon($commande->created_at);
            $date->locale('fr_FR');
            @endphp
            <tr>

            <td>{{$date->toFormattedDateString()}} ({{$date-> diffForHumans()}})</td>
              <td>{{$commande->vendeurs}} ( {{$commande->vendeurs()->localisation}} )</td>
              <td>{{number_format($commande->montant)}}</td>
              <td>{{$commande->type}}</td>
              <td><span class="{{$commande->status == 'unvalidated' ? 'uk-alert-danger': 'uk-alert-success'}} uk-border-rounded">{{$commande->status}}</span></td>
              <td>{{$commande->numero_recu}}</td>
              <td>
                <div uk-lightbox>
                    <a class="uk-button-default uk-border-rounded uk-box-shadow-small " href="{{asset('uploads/'.$commande->recu)}}" data-caption="{{$commande->numero_recu}}">voir le recu</a>
                </div>
              </td>
              <td>
                <a class="uk-button-primary uk-border-rounded uk-box-shadow-small validate-button" uk-toggle="target : #modal-validation" id="{{$commande->id}}">valider <span uk-icon="icon : check"></span> </a>
              </td>
            </tr>
            @endforeach
            @endif
          </tbody>
        </table>
      </li>
      <li>
        <!-- COMMANDES DEJA VALIDEE -->
        <table class="uk-table uk-table-divider uk-table-striped uk-table-small uk-table-hover">
          <thead>
            <tr>
              <th>date</th>
              <th>vendeurs</th>
              <th>montant</th>
              <th>type</th>
              <th>status</th>
              <th>Numero recu</th>
              <th>recu</th>
            </tr>
          </thead>
          <tbody>
            @if($validate)
            @foreach($validate as $commande)
            @php
            $date = new Carbon\Carbon($commande->created_at);
            $date->locale('fr_FR');
            @endphp
            <tr>

            <td>{{$date->toFormattedDateString()}} ({{$date-> diffForHumans()}})</td>
            <td>{{$commande->vendeurs}} ( {{$commande->vendeurs()->localisation}} )</td>
            <td>{{number_format($commande->montant)}}</td>
            <td>{{$commande->type}}</td>
            <td><span class="{{$commande->status == 'unvalidated' ? 'uk-alert-danger': 'uk-alert-success'}} uk-border-rounded">{{$commande->status}}</span></td>
            <td>{{$commande->numero_recu}}</td>
            <td>
              <div uk-lightbox>
                  <a class="uk-button-default uk-border-rounded uk-box-shadow-small " href="{{asset('uploads/'.$commande->recu)}}" data-caption="{{$commande->numero_recu}}">voir le recu</a>
              </div>
            </td>
          </tr>
            @endforeach
            @endif
          </tbody>
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
          {!!Form::text('montant','',['class'=>'uk-input uk-margin-small uk-border-rounded'])!!}
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
<script type="text/javascript">
  $(function () {
    $(".validate-button").on('click',function () {
      var row = $(this).parent().parent()
      $("#validation-montant").text(row.children().eq(2).text())
      $("#validation-vendeur").text(row.children().eq(1).text())
      $("#validation-commande").val($(this).attr('id'))
      $("#validation-type-commande").val(row.children().eq(3).text())
    })
  })
</script>
@endsection
