@extends('viewmanager::layouts.app')

@use('Modules\Financial\Constants\Permissions')
@use('Modules\Financial\Helpers\Helper')

@section('page-title', 'Your Transaction')

@section('content')
<div class="card">
  <div class="card-header text-end">
    <div class="float-start me-auto">
      <a href="{{ route('financial.wallets.transactions.detail', ['wallet' => $wallet, 'year' => $year, 'month' => $month]) }}" class="btn btn-secondary" role="button">
        <svg class="icon">
          <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-arrow-thick-left') }}"></use>
        </svg>
      </a>
      @can(Permissions::EDIT_TRANSACTIONS)
      <a href="{{ route('financial.wallets.transactions.edit', ['wallet' => $wallet, 'transaction' => $transaction, 'year' => $year, 'month' => $month]) }}" class="btn btn-success" role="button">
        <svg class="icon">
          <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-pen') }}"></use>
        </svg>
      </a>
      @endcan
    </div>
    <h5 class="card-title">
      {{ $transaction->date->format("d-m-Y H:i:s") }}
    </h5>
  </div>
  <div class="card-body">
      <ul class="list-group list-group-flush">
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <strong>Date</strong>
          <span class="text-muted">{{$transaction->date->format("d-m-Y H:i:s")}}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <strong>Description</strong>
          <span class="text-muted text-end">{{$transaction->description}}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <strong>Amount</strong>
          <span class="text-muted">@money(number_format((float) $transaction->amount, 2, ".", ""), $wallet->currency)</span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <strong>Type</strong>
          <span class="text-{{Helper::getColortextAmount($transaction->category)}}">{{$transaction->category->name}}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <strong>Notes</strong>
          <span class="text-muted">{{$transaction->notes ?? "-"}}</span>
        </li>
      </ul>
  </div>
</div>
@endsection