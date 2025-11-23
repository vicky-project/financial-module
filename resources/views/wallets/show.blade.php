@extends('viewmanager::layouts.app')

@use('Modules\Financial\Constants\Permissions')

@section('page-title', 'Your Wallet')

@section('content')
<div class="card">
  <div class="card-header text-end">
    <div class="float-start me-auto">
      <a href="{{ route('financial.wallets.index') }}"class="btn btn-secondary" role="button">
        <svg class="icon">
          <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-arrow-thick-left') }}"></use>
        </svg>
      </a>
    </div>
    <h5 class="card-title">{{ $wallet->wallet_name}}</h5><span class="small ms-2">{{ $wallet->wallet_number }}</span>
  </div>
  <div class="card-body">
    <ul class="list-group list-group-flush">
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Type</strong>
        <span class="text-muted">{{ $wallet->wallet_type}}</span>
      </li>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Balance</strong>
        <span class="text-muted">@money(number_format((float) $wallet->balance, 2,  ".", ""), $wallet->currency)</span>
      </li>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Initial Balance</strong>
        <span class="text-muted">@money(number_format((float) $wallet->initial_balance, 2,  ".", ""), $wallet->currency)</span>
      </li>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Currency</strong>
        <span class="text-muted">{{ $wallet->currency }}</span>
      </li>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Total Transactions</strong>
        <span class="text-muted">{{ $wallet->transactions_count }}</span>
      </li>
    </ul>
  </div>
</div>
@endsection