@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
  <div class="uk-container">
    <h3>
			<a href="{{url()->previous()}}" uk-tooltip="Retour" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Inventaire</h3>
      <hr class="uk-divider-small">

      <ul uk-tab>
          <li><a href="#">Materiels</a></li>
          <li><a href="#">Numero Materiel</a></li>
      </ul>

      <ul class="uk-switcher uk-margin">
          <li>
            <table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
              <thead>
                <tr>
                  <th>Designation</th>
                  <th>Quantite</th>
                  <th>Prix Initial</th>
                  <th>Prix ttc</th>
                  <th>Prix ht</th>
                  <th>Prix tva</th>
                  <th>Marge</th>
                </tr>
              </thead>
              <tbody id="list-material"></tbody>
            </table>
          </li>
          <li>
            <!-- serial number -->
            <table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small" >
              <thead>
                <tr>
                  <th>Serials</th>
                  <th>Vendeurs</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="serials"></tbody>
            </table>
          </li>
      </ul>
</div>
</div>
@endsection
@section('script')
<script type="text/javascript">
$(function () {
  @php
    $localisation = Auth::user()->depot()->first()->localisation;
  @endphp
  var form = $adminPage.makeForm("{{csrf_token()}}","{{url('/user/inventaire-depot/get-list')}}","{{$localisation}}");
  form.on('submit',function(e) {
    e.preventDefault();
    $.ajax({
      url : $(this).attr('action'),
      type  : 'post',
      dataType : 'json',
      data : $(this).serialize()
    })
    .done(function (data) {
      if(data) {
        $logistique.dataList(data.inventaire,$("#list-material"))
        $logistique.dataList(data.serials,$("#serials"))

    }
  })
    .fail(function(data) {
      UIkit.modal.alert(data).then(function () {
        $(location).attr('href',"{{url()->current()}}");
      })
    })
  });

  form.submit();
})
</script>
@endsection
