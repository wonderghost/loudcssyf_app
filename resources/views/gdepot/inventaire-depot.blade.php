@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
  <div class="uk-container">
    <h3>
			<a href="{{url()->previous()}}" uk-tooltip="Retour" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Inventaire</h3>
      <hr class="uk-divider-small">
      <div class="uk-child-width-1-2@m" uk-grid>
        <div class="">
          <!-- inventaire -->
          <table class="uk-table uk-table-divider">
            <thead>
              <tr>
                <th>Item</th>
                <th>Quantite</th>
                <th>Prix TTC</th>
              </tr>
            </thead>
            <tbody id="list-material"></tbody>
          </table>
        </div>
        <div class="">
          <!-- serial number -->
          <table class="uk-table uk-table-divider" >
            <thead>
              <tr>
                <th>Serials</th>
              </tr>
            </thead>
            <tbody id="serials"></tbody>
          </table>
        </div>
      </div>



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

        $.each(data.inventaire,function (index,value) {
          var tr = $("<tr></tr>");
          var td = [];

          for (var i = 0; i < 3 ; i++) {
            td[i] = $("<td></td>");
          }

          td[0].text(value.item);
          td[1].text(value.quantite);
          td[2].text(lisibilite_nombre(value.prix_ttc));

          for(var i = 0 ; i < 3 ; i++) {
            tr.append(td[i]);
          }
          $("#list-material").append(tr);
        });

        $.each(data.serials,function (index,value) {
          var tr = $("<tr></tr>");
          var td = $("<td></td>");
          td.text(lisibilite_nombre(value.exemplaire));
          tr.append(td);
          $("#serials").append(tr);
        });
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
