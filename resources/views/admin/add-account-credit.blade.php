@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Compte Credit ( CGA / REX / AFROCASH)</h3>
		<hr class="uk-divider-small">
		@if(session('success'))
		<div class="uk-alert-success uk-border-rounded uk-box-shadow-small" uk-alert>
			<button type="button" class="uk-align-right close-button"  uk-icon="icon:close"></button>
			<div>{{session('success')}}</div>
		</div>
		@endif
		@if(session('_error'))
		<div class="uk-alert-success uk-border-rounded uk-box-shadow-small" uk-alert>
			<button type="button" class="uk-align-right close-button"  uk-icon="icon:close"></button>
			<div>{{session('_error')}}</div>
		</div>
		@endif
		<!--  -->
		<ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-fade">
		    <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Comptes</a></li>
		    <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Soldes Vendeurs</a></li>
		</ul>

		<ul class="uk-switcher uk-margin">
			<li>
				<div class="uk-child-width-1-2@m uk-grid-divider " uk-grid>
					<div>
						<h4>...</h4>
						@foreach($credit as $values)
						<div class="uk-grid-small uk-text-lead" uk-grid>
								<div class="uk-width-expand uk-text-capitalize" uk-leader>SOLDE {{$values->designation}} (GNF)</div>
								<div>{{number_format($values->solde)}}</div>
						</div>
						@endforeach
						<div class="uk-grid-small uk-text-lead" uk-grid>
							<div class="uk-width-expand uk-text-capitalize" uk-leader> TOTAL (GNF)</div>
							<div class="">{{number_format($total)}}</div>
						</div>
					</div>
					<div id="">
						<h4>Crediter les comptes</h4>
						@if($errors->any())
						@foreach($errors->all() as $error)
						<div class="uk-alert-danger uk-border-rounded uk-box-shadow-small" uk-alert>
							<a href="#" class="uk-alert-close" uk-close></a>
							<p>{{$error}}</p>
						</div>
						@endforeach
						@endif
						{!!Form::open()!!}
						<label>
							{!!Form::radio('compte','cga',true,['class'=>'uk-radio'])!!} CGA
						</label>
						<label>
							{!!Form::radio('compte','rex','',['class'=>'uk-radio'])!!} REX
						</label>
						{!!Form::text('montant','',['class'=>'uk-input uk-margin-small','placeholder'=>'Montant'])!!}
						<button type="submit" class="uk-button uk-button-primary uk-border-rounded">valider <span uk-icon="icon:check;ratio:.8"></span></button>
						{!!Form::close()!!}
					</div>
				</div>
			</li>
			<li>
				<div class="uk-grid-small uk-grid" uk-grid>
          <div class="uk-width-1-2@m">
            <label for=""><span uk-icon= " icon : search"></span> Recherche</label>
            <input type="text" name="" value="" class="uk-input uk-border-rounded">
          </div>
          <div class="uk-width-1-2@m">
            <label for=""><span uk-icon= " icon : users"></span> Vendeurs</label>
            <select class="uk-select uk-border-rounded" name="">
              <option value="">Tous les Vendeurs</option>
            </select>
          </div>
      </div>
				<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
					<thead>
						<tr>
							<th>VENDEURS</th>
							<th>AFROCASH COURANT</th>
							<th>AFROCASH SEMI-GROSSISTE</th>
							<th>CGA</th>
							<th>REX</th>
						</tr>
					</thead>
					<tbody id="solde-vendeur"></tbody>
				</table>
			</li>
		</ul>
	</div>
</div>

@endsection
@section('script')
<script type="text/javascript">
	$(function() {
		$(".close-button").on('click',function () {
			$(this).parent().hide(500);
		})

		$logistique.getSoldeVendeur("{{csrf_token()}}","{{url('/admin/get-soldes')}}")
	});
</script>
@endsection
