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
	</div>
</div>
@endsection
