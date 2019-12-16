@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
  <div class="uk-container">
    <h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Afrocash Central</h3>
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
    <ul uk-tab>
        <li><a href="#">Apport</a></li>
        <li><a href="#">Depenses</a></li>
    </ul>

    <ul class="uk-switcher uk-margin">
        <li>
          <button type="button" uk-toggle="target: #modal-apport" class="uk-width-1-6@m uk-button-primary  uk-border-rounded uk-box-shadow-small" name="button"><span uk-icon="icon : plus"></span> Effectuez un apport</button>
          <!-- AUGMENTATION CAPITAL / APPORT -->
          <div id="modal-apport" uk-modal>
              <div class="uk-modal-dialog uk-modal-body">
                  <h3>Effectuez un apport</h3>
                  <div class="uk-alert-info uk-border-rounded uk-box-shadow-small" uk-alert>
                    <a href="#" class="uk-alert-close" uk-close></a>
                    <p>Remplissez les champs vides!</p>
                  </div>
                  {!!Form::open(['url'=>'/admin/afrocash/apport','','id'=>'apport-form'])!!}
                  <div class="">
                    {!!Form::label('Montant')!!}
                    {!!Form::text('montant','',['class'=>'uk-input uk-margin-small uk-border-rounded','required'=>''])!!}
                  </div>
                  <div class="">
                    {!!Form::label('Description')!!}
                    {!!Form::textarea('description','',['class'=>'uk-textarea uk-border-rounded'])!!}
                  </div>
                  {!!Form::submit('validez',['class'=>'uk-button-primary uk-border-rounded uk-margin-small uk-box-shadow-small'])!!}
                  {!!Form::close()!!}
              </div>
          </div>
          <!-- HISTORIQUE DES APPORT -->
          <hr class="uk-divider-small">
          <h3>Historique des apports</h3>
          <table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
            <thead>
              <tr>
                <th>Date</th>
                <th>Montant</th>
                <th>Motif</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              @if($apports)
              @foreach($apports as $value)
              @php
              $date = new \Carbon\Carbon($value->created_at);
              $date->locale('fr_FR');
              @endphp
              <tr uk-tooltip="{{$value->description}}">
                <td>{{$date->toFormattedDateString()}} ({{$date-> diffForHumans()}})</td>
                <td>{{number_format($value->montant)}}</td>
                <td>{{$value->motif}}</td>
                <td>{{str_limit($value->description,100,'...')}}</td>
              </tr>
              @endforeach
              @endif
            </tbody>
          </table>
        </li>
        <li>
          <!-- DEPENSES -->
          <button type="button" uk-toggle="target: #modal-depenses" class="uk-width-1-6@m uk-button-primary  uk-border-rounded uk-box-shadow-small" name="button"><span uk-icon="icon : plus"></span> Ajouter une depense</button>
<!-- MODAL ADD DEPENSES -->
          <div id="modal-depenses" uk-modal>
              <div class="uk-modal-dialog uk-modal-body">
                  <h3 class="">Ajoutez une depense</h3>
                  <div class="uk-alert-info uk-border-rounded uk-box-shadow-small" uk-alert>
                    <a href="#" class="uk-alert-close" uk-close></a>
                    <p>Remplissez les champs vides!</p>
                  </div>
                  {!!Form::open(['url'=>'/admin/depenses/add'])!!}
                  {!!Form::label('Motif')!!}
                  {!!Form::select('motif', [
                  'paiement_salaire' => 'Paiement Salaire',
                   'loyers' => 'Loyers',
                   'connection_internet'  =>  'Connection Internet',
                   'carburant'  =>  'Carburant',
                   'credit_appel' =>  'Credit Appel',
                   'commission' =>  'Commission',
                   'autres' =>  'Autres'
                  ],'paiement_salaire',['class'=>'uk-select uk-border-rounded uk-margin-small'])!!}
                  {!!Form::label('Montant')!!}
                  {!!Form::number('montant','',['class'=>'uk-input uk-border-rounded uk-margin-small','placeholder' =>  'Montant'])!!}
                  {!!Form::label('Description')!!}
                  {!!Form::textarea('description','',['class'=>'uk-textarea uk-margin-small uk-border-rounded','placeholder'  =>  'Description'])!!}
                  {!!Form::submit('Envoyer',['class'=>'uk-button-primary uk-border-rounded uk-box-shadow-small'])!!}
                  {!!Form::close()!!}
              </div>
          </div>

          <!-- // -->
          <!-- HISTORIQUE DES DEPENSES DU JOUR -->
          <hr class="uk-divider-small">
          <h3>Historique des depenses</h3>
          <table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
            <thead>
              <tr>
                <th>Date</th>
                <th>Montant</th>
                <th>Motif</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              @if($depenses)
              @foreach($depenses as $value)
              @php
              $date = new \Carbon\Carbon($value->created_at);
              $date->locale('fr_FR');
              @endphp
              <tr uk-tooltip="{{$value->description}}">
                <td>{{$date->toFormattedDateString()}} ({{$date-> diffForHumans()}})</td>
                <td>{{number_format($value->montant)}}</td>
                <td>{{$value->motif}}</td>
                <td>{{str_limit($value->description,100,'...')}}</td>
              </tr>
              @endforeach
              @endif
            </tbody>
          </table>
        </li>
    </ul>
  </div>
</div>
@endsection
@section('script')
<script type="text/javascript">
  $(function () {

  })
</script>
@endsection
