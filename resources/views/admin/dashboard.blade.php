@extends('layouts.app_admin')

@section('content')
<div class="uk-section uk-section-default uk-padding-small uk-margin-remove">
  <div class="uk-container">
    <h3>Tableau de bord</h3>
    <hr class="uk-divider-small">
    
    @if(session('msg'))
    <div class="uk-alert-info" uk-alert>
      <a href="#" class="uk-alert-close" uk-close></a>
      <p>{{session('msg')}}</p>
    </div>
    @endif
    <div class="uk-grid-small uk-child-width-1-4@m" uk-grid>
      <div class="">
        <!-- USERS -->
        <h3 class="">Utilisateurs</h3>
        <canvas id="userChart" width="400" height="400"></canvas>
        <!-- // -->
      </div>
      <div class="">
        <!-- DEPOT -->
        <h3 class="">Depots</h3>
        <canvas id="depotChart" width="400" height="400"></canvas>
        <!-- // -->
      </div>
      <div class="">
        <h3></h3>
      </div>
    </div>
  </div>
</div>

@endsection
@section('script')
<script type="text/javascript">
  $(function () {

    setInterval(function () {
      // USER CHARTS
      $logistique.userChart("{{csrf_token()}}","{{url('/admin/dashboard/user-data')}}")
      // DEPOT CHARTS
      $logistique.depotChart("{{csrf_token()}}","{{url('/admin/dashboard/depot-data')}}")
    },5000);

    // USER CHARTS
    $logistique.userChart("{{csrf_token()}}","{{url('/admin/dashboard/user-data')}}")
    // DEPOT CHARTS
    $logistique.depotChart("{{csrf_token()}}","{{url('/admin/dashboard/depot-data')}}")
    // // console.log(ctx)

  })
</script>
@endsection
