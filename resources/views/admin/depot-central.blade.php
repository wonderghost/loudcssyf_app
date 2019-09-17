@extends('layouts.app_admin')

@section('content')
<div class="uk-section uk-section-default">
  <div class="uk-container">
    <h3><a href="{{url()->previous()}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Depot Central</h3>
		<hr class="uk-divider-small"></hr>
    <div class="loader" uk-spinner> Patientez un instant ...  </div>
    <table class="uk-table uk-table-divider">
      <thead>
        <tr>
          <th>Designation</th>
          <th>Quantite</th>
          <th>Prix Initial (GNF)</th>
          <th>Prix Ttc (GNF)</th>
          <th>Ht (GNF)</th>
          <th>Tva (18%) (GNF)</th>
          <th>Marge (GNF)</th>
        </tr>
      </thead>
      <tbody id="list-material"></tbody>
    </table>
  </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
  $(function () {
    // recuperation des informations (liste des produits)
    var form  = $adminPage.makeForm("{{csrf_token()}}","{{url()->current()}}","")
    form.on('submit',function (e) {
      e.preventDefault()
      $.ajax({
        url : $(this).attr('action'),
        type : 'post',
        dataType : 'json',
        data : $(this).serialize(),
        beforeSend : $adminPage.onLoading(true)
      }).
      done(function (data) {
        $adminPage.onLoading(false);
        $adminPage.showListMaterial(data);
      })
      .fail(function (data) {
        UIkit.modal.alert("<div class='uk-alert-danger' uk-alert>Erreur de chargement!</div>").then(function () {
          $(location).attr('href',"{{url()->previous()}}")
        })
      })
    })
    form.submit()

  })
</script>
@endsection
