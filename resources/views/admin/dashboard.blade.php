@extends('layouts.app_admin')

@section('content')
<div class="uk-section uk-section-muted uk-padding-small uk-margin-remove">
  <div class="uk-container">
    <h3>Tableau de bord</h3>
    <hr class="uk-divider-small">
    <div class="uk-child-width-1-2@m" uk-grid>
      <div class="">
        <div class="uk-grid-small uk-child-1-1@m" uk-grid>

          <div class="uk-card uk-card-default uk-border-rounded uk-width-1-1@m">
            <div class="uk-card-header uk-margin-remove">
              <h3><span class="uk-button-default uk-border-circle uk-padding-small" uk-icon = "icon : credit-card"></span> Comptes</h3>
            </div>
            <div class="uk-card-body uk-padding-small">
              <ul uk-accordion>
                  <li>
                      <a class="uk-accordion-title" href="#">CGA</a>
                      <div class="uk-accordion-content">
                        <!-- CENTRAL -->
                        <div class="uk-grid-small" uk-grid>
                            <div class="uk-width-expand" uk-leader="fill: -">CENTRAL</div>
                            <div class="uk-text-lead">{{number_format($cga['solde_central'])}} (GNF)</div>
                        </div>
                        <!-- // -->
                        <!-- RESEAUX -->
                        <div class="uk-grid-small" uk-grid>
                            <div class="uk-width-expand" uk-leader="fill: -">RESEAUX</div>
                            <div class="uk-text-lead">{{number_format($cga['solde_reseau'])}} (GNF)</div>
                        </div>
                        <!-- // -->
                        <!-- VENDEURS STANDART -->
                        <div class="uk-grid-small" uk-grid>
                            <div class="uk-width-expand" uk-leader="fill: -">VENDEURS STANDART</div>
                            <div class="uk-text-lead">{{number_format($cga['solde_vstandart'])}} (GNF)</div>
                        </div>
                        <!-- // -->
                      </div>
                  </li>
                  <li>
                      <a class="uk-accordion-title" href="#">AFROCASH</a>
                      <div class="uk-accordion-content">
                        <!-- CENTRAL -->
                        <div class="uk-grid-small" uk-grid>
                            <div class="uk-width-expand" uk-leader="fill: -">CENTRAL</div>
                            <div class="uk-text-lead">{{number_format($afrocash['solde_central'])}} (GNF)</div>
                        </div>
                        <!-- // -->
                        <!-- RESEAUX -->
                        <div class="uk-grid-small" uk-grid>
                            <div class="uk-width-expand" uk-leader="fill: -">RESEAUX</div>
                            <div class="uk-text-lead">{{number_format($afrocash['solde_courant_reseau'])}} (GNF)</div>
                        </div>
                        <!-- // -->
                        <!-- VENDEURS STANDART -->
                        <div class="uk-grid-small" uk-grid>
                            <div class="uk-width-expand" uk-leader="fill: -">VENDEURS STANDART</div>
                            <div class="uk-text-lead">{{number_format($afrocash['solde_courant_vstandart'])}} (GNF)</div>
                        </div>
                        <!-- // -->
                        <!-- SEMI GROSSISTE -->
                        <div class="uk-grid-small" uk-grid>
                            <div class="uk-width-expand" uk-leader="fill: -">COMPTE DEPOT</div>
                            <div class="uk-text-lead">{{number_format($afrocash['solde_semigrossiste'])}} (GNF)</div>
                        </div>
                        <!-- // -->
                      </div>
                  </li>
                  <li>
                      <a class="uk-accordion-title" href="#">REX</a>
                      <div class="uk-accordion-content">
                        <!-- CENTRAL -->
                        <div class="uk-grid-small" uk-grid>
                            <div class="uk-width-expand" uk-leader="fill: -">Central</div>
                            <div></div>
                        </div>
                        <!-- // -->
                        <!-- RESEAUX -->
                        <div class="uk-grid-small" uk-grid>
                            <div class="uk-width-expand" uk-leader="fill: -">Reseaux</div>
                            <div></div>
                        </div>
                        <!-- // -->
                        <!-- VENDEURS STANDART -->
                        <div class="uk-grid-small" uk-grid>
                            <div class="uk-width-expand" uk-leader="fill: -">Vendeurs Standart</div>
                            <div></div>
                        </div>
                        <!-- // -->
                      </div>
                  </li>
              </ul>
            </div>
          </div>

          <div class="uk-card uk-card-default uk-border-rounded uk-width-1-1@m">
            <div class="uk-card-header uk-margin-remove">
              <h3><span class="uk-button-default uk-border-circle uk-padding-small" uk-icon = "icon : users"></span> Utilisateurs</h3>
            </div>
            <div class="uk-card-body uk-child-width-1-1@m" uk-grid>
              <div class="">
                <span>
                  <span href="#" class="uk-padding-small uk-button-default uk-border-circle  ">{{$users['da']}}</span> DISTRIBUTEUR(S) AGREE(S)
              </span>
              </div>
              <div class="">
                <span>
                  <span href="#" class="uk-padding-small uk-button-default uk-border-circle ">{{$users['v_standart']}}</span> VENDEURS STANDART(S)
              </span>
              </div>
            </div>
          </div>

        </div>
      </div>
      <div class="">
        <div class="uk-grid-small" uk-grid>

          <div class="uk-card uk-card-default uk-border-rounded uk-width-1-1@m">
            <div class="uk-card-header">
              <h3><span class="uk-button-default uk-border-circle uk-padding-small uk-icon uk-icon-image" style="background-image : url('img/logistic-icon.svg')"></span> Materiels</h3>
            </div>

            <div class="uk-card-body uk-padding-small">
              <ul uk-accordion>
                  <li>
                      <a class="uk-accordion-title" href="#">ENTREPOT</a>
                      <div class="uk-accordion-content">
                        <ul class="uk-list uk-list-divider">
                          @foreach($materiels['entrepot'] as $key => $value)
                          <li>
                            <span class="uk-text-bold">{{$value->libelle}}</span>
                            <span class="uk-button uk-button-default uk-border-rounded uk-text-bold uk-float-right">{{$value->quantite_centrale}}</span>
                          </li>
                          @endforeach
                        </ul>
                      </div>
                  </li>
                  <li>
                      <a class="uk-accordion-title" href="#">DEPOTS</a>
                      <div class="uk-accordion-content">
                        {{$materiels['depot']}}
                      </div>
                  </li>
                  <li>
                      <a class="uk-accordion-title" href="#">RESEAU</a>
                      <div class="uk-accordion-content">

                      </div>
                  </li>
              </ul>
            </div>
          </div>

          <div class="uk-card uk-card-default uk-border-rounded uk-width-1-1@m">
            <div class="uk-card-header">
              <h3><span class="uk-button-default uk-border-circle uk-padding-small" uk-icon = "icon : shrink"></span> Transactions</h3>
            </div>
            <div class="uk-card-body uk-padding-small">
              <canvas id="myChart" width="400" height="100"></canvas>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

@endsection
@section('script')
<script type="text/javascript">
  $(function () {
    $logistique.transactionDashboardView("{{csrf_token()}}","{{url('/admin/transaction-for-dashboard')}}")
    //
    // var ctx = $("#myChart")
    // var myChart = new Chart(ctx,{
    //   type : 'line',
    //   data : [20,10]
    // })
    // // console.log(ctx)
  })
</script>
@endsection
