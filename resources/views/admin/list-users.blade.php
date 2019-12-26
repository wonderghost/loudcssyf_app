@extends('layouts.app_admin')
@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Tous les utilisateurs</h3>
		<hr class="uk-divider-small"></hr>
		<div class="uk-grid-collapse uk-grid-divider" uk-grid>
			<div class="uk-width-1-2@m">
				<h4><span uk-icon="icon:search"></span> Search</h4>
				{!!Form::text('search','',['class'=>'uk-input','placeholder'=>'...'])!!}
			</div>
			<div class="uk-width-1-2@m">
				<h4><span uk-icon="icon:more-vertical"></span> Filter </h4>
				{!!Form::select('filter',['All'=>'all','da'=>'Distributeur Agree','v_stand'=>'Vendeur Standart'],null,['class' => 'uk-select'])!!}
			</div>
		</div>

		<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-hover">
			<thead>
				<tr>
					<th>Username</th>
					<th>Type</th>
					<th>Email</th>
					<th>Phone</th>
					<th>Agence</th>
					<th>Status</th>
					<th colspan="2" class="uk-text-center">-</th>
				</tr>
			</thead>
			<tbody>
				@if($users)
				@foreach($users as $values)
				<tr>
					<td class="uk-text-bold">{{$values->username}}</td>
					<td>{{$values->type}}</td>
					<td>{{$values->email}}</td>
					<td>{{$values->phone}}</td>
					<td>{{$values->localisation}}</td>
					<td><span class="{{$values->status == 'unblocked' ? 'uk-border-rounded uk-alert-success': 'uk-border-rounded uk-alert-danger'}}">{{$values->status}}</span></td>
					<td><button type="button" class="uk-button uk-padding-remove uk-button-primary uk-border-rounded user-action" id="edit" title="{{$values->username}}"><span uk-icon="icon:pencil;ratio:.8"></span> edit</button></td>
					@if($values->status == 'unblocked')
					<td><button type="button" class="uk-button uk-padding-remove uk-button-danger uk-border-rounded user-action" id="blocked" title="{{$values->username}}"><span uk-icon="icon:lock;ratio:.8"></span> blocker</button></td>
					@else
					<td><button type="button" class="uk-button uk-padding-remove uk-button-default uk-border-rounded uk-alert-success user-action" id="unblocked" title="{{$values->username}}"><span uk-icon="icon:unlock;ratio:.8"></span> deblocker</button></td>
					@endif
				</tr>
				@endforeach
				@endif
			</tbody>
		</table>
	</div>
</div>
@endsection

@section('script')
<script type="text/javascript">
	$(function() {
		$(".user-action").on('click',function() {
			if($(this).attr('id') == 'edit') {
				$(location).attr('href','/admin/edit-users/'+$(this).attr('title'));
			} else if($(this).attr('id') == 'blocked') {
				$adminPage.blockUser("{{csrf_token()}}","/admin/block-user",$(this).attr('title'));
			} else if($(this).attr('id') == 'unblocked') {
				$adminPage.unblockUser("{{csrf_token()}}","/admin/unblock-user",$(this).attr('title'));
			}
		});
	});
</script>
@endsection
