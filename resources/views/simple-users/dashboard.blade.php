@extends('layouts.app_users')

@section('content')
<div class="uk-section uk-section-muted uk-padding-small uk-margin-remove">
  <div class="uk-container">
    <h3>Tableau de bord</h3>
    <hr class="uk-divider-small">
    <div class="uk-child-width-1-2@m" uk-grid>
      <div class="">
        <div class="uk-grid-small uk-child-1-1@m" uk-grid>

          <div class="uk-card uk-card-default uk-box-shadow-small uk-border-rounded">
            <div class="uk-card-header uk-margin-remove">
              <h3><span class="uk-button-default uk-border-circle uk-padding-small" uk-icon = "icon : credit-card"></span> COMPTES</h3>
            </div>
            <div class="uk-card-body uk-padding-small">
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
            </div>
          </div>

          <div class="uk-card uk-card-default uk-box-shadow-small uk-border-rounded">
            <div class="uk-card-header uk-margin-remove">
              <h3><span class="uk-button-default uk-border-circle uk-padding-small uk-icon uk-icon-image" style="background-image : url('img/logistic-icon.svg')"></span> MATERIELS</h3>
            </div>
            <div class="uk-card-body uk-padding-small">
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
            </div>
          </div>

        </div>
      </div>
      <div class="">
        <div class="uk-grid-small" uk-grid>

          <div class="uk-card uk-card-default uk-box-shadow-small uk-border-rounded">
            <div class="uk-card-header">
              <h3><span class="uk-button-default uk-border-circle uk-padding-small" uk-icon = "icon : shrink"></span> TRANSACTIONS</h3>
            </div>
            <div class="uk-card-body uk-padding-small">
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
            </div>
          </div>

          <div class="uk-card uk-card-default uk-box-shadow-small uk-border-rounded">
            <div class="uk-card-header">
              <h3><span class="uk-button-default uk-border-circle uk-padding-small" uk-icon = "icon : history"></span> RAPPORTS</h3>
            </div>
            <div class="uk-card-body uk-padding-small">
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
@endsection('content')
