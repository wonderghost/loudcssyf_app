@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container uk-container-large">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Toutes les commandes</h3>
		<hr class="uk-divider-small">
		<command></command>
		<!-- MODAL DETAIL LIVRAISON TO DOWNLOAD -->

		<!-- <div id="modal-livraison-detail" class="uk-flex-top" uk-modal>
				<div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical uk-text-center">
						<button class="uk-modal-close-default" type="button" uk-close></button>
						<p class="uk-text-lead">Cliquez sur le button pour telecharger le fichier text</p>
						<a id="file-link" download="" target="_blank" class="uk-button uk-button-primary uk-border-rounded uk-box-shadow-small">Telecharger <span uk-icon="icon : download"></span> </a>
				</div>
		</div> -->
		<!-- // -->

		<!-- <ul class="uk-switcher uk-margin">
			<li>

				<div class="uk-gird-small" uk-grid>
            <div class="uk-width-auto@m">
                <ul class="uk-tab-left" uk-tab="connect: #component-tab-left; animation: uk-animation-fade">
                    <li><a href="#">En Attente de Confirmation</a></li>
                    <li><a href="#">Deja Confirmer</a></li>
                    <li><a href="#">Livraison a valider</a></li>
                    <li><a href="#">Livraison validee</a></li>
                </ul>
            </div>
            <div class="uk-width-expand@m">
                <ul id="component-tab-left" class="uk-switcher">
                    <li>
											<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small command-table">
												<thead>
													<tr>
														<th>Date</th>
														<th>Vendeurs</th>
														<th>Designation</th>
														<th>Quantite</th>
														<th>Parabole a llivrer</th>
														<th>status</th>
													</tr>
												</thead>
												<tbody id="non-confirm-commande"><div id="loader" uk-spinner></div></tbody>
											</table>
										</li>
                    <li>
											<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small command-table">
												<thead>
													<tr>
														<th>Date</th>
														<th>Vendeurs</th>
														<th>Designation</th>
														<th>Quantite</th>
														<th>Parabole a llivrer</th>
														<th>status</th>
													</tr>
												</thead>
												<tbody id="confirm-commande"><div id="loader" uk-spinner></div></tbody>
											</table>
										</li>
                    <li>
											<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
												<thead>
													<tr>
														<th>Date</th>
														<th>Vendeurs</th>
														<th>Designation</th>
														<th>Commande</th>
														<th>Quantite</th>
														<th>status</th>
													</tr>
												</thead>
												<tbody id="livraison-unvalidate"><div id="loader" uk-spinner></div></tbody>
											</table>
										</li>
                    <li>
											<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
												<thead>
													<tr>
														<th>Date</th>
														<th>Vendeurs</th>
														<th>Designation</th>
														<th>Commande</th>
														<th>Quantite</th>
														<th>status</th>
													</tr>
												</thead>
												<tbody id="livraison-validate"><div id="loader" uk-spinner></div></tbody>
											</table>
										</li>
                </ul>
            </div>
        </div> -->

			<!-- </li>
			<li>

				<div uk-grid>
          <div class="uk-width-auto@m">
              <ul class="uk-tab-left" uk-tab="connect: #cga-tab-left; animation: uk-animation-fade">
                  <li><a href="#">En attente de confirmation</a></li>
                  <li><a href="#">Deja confirmee</a></li>
                  <li><a href="#">Commandes Annulee</a></li>

              </ul>
          </div>
          <div class="uk-width-expand@m">
              <ul id="cga-tab-left" class="uk-switcher">
                  <li>
										<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
											<thead>
												<tr>
													<th>Date</th>
													<th>Vendeurs</th>
													<th>Type</th>
													<th>Montant</th>
													<th>status</th>
													<th>Numero Recu</th>
													<th>Recu</th>
												</tr>
											</thead>
											<tbody id="credit-unvalidate-commande"><div id="loader" uk-spinner></div></tbody>
										</table>
									</li>
                  <li>
										<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
											<thead>
												<tr>
													<th>Date</th>
													<th>Vendeurs</th>
													<th>Type</th>
													<th>Montant</th>
													<th>status</th>
													<th>Numero Recu</th>
													<th>Recu</th>
												</tr>
											</thead>
											<tbody id="credit-validate-commande"><div id="loader" uk-spinner></div></tbody>
										</table>
									</li>
									<li>
										<div class="loader" uk-spinner></div>
										<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
											<thead>
												<tr>
													<th>Date</th>
													<th>Vendeurs</th>
													<th>Type</th>
													<th>Montant</th>
													<th>status</th>
													<th>Numero Recu</th>
													<th>Recu</th>
												</tr>
											</thead>
											<tbody id="credit-aborted-commande"></tbody>
										</table>
									</li>
              </ul>
          </div>
      </div> -->
				<!--
			</li>
		</ul>
-->
	</div>
</div>
@endsection
