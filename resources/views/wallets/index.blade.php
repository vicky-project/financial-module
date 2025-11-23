@extends('viewmanager::layouts.app')

@use('Modules\Financial\Constants\Permissions')
@use('Illuminate\Support\Number')

@section('page-title', 'Your Wallets')

@section('content')
<div class="row my-4">
  <div class="col">
    <div class="float-end ms-auto">
      @can(Permissions::MANAGE_WALLETS)
      <a href="{{ route('financial.wallets.recalculate-all') }}" class="btn btn-block btn-info" role="button" title="Re Calculate Balance">
        <svg class="icon">
          <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-sync') }}"></use>
        </svg>
      </a>
      @endcan
      @can(Permissions::CREATE_WALLETS)
      <a href="{{ route('financial.wallets.create') }}" class="btn btn-block btn-success" role="button" title="Create Wallet">
        <svg class="icon">
          <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
        </svg>
      </a>
      @endcan
    </div>
  </div>
</div>
<div class="card">
  <div class="card-header">
    <h5 class="card-title">Wallets</h5>
  </div>
  <div class="card-body">
    <div class="row g-4">
      @forelse($walletsData as $wal)
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card text-white bg-primary">
          <div class="card-body pb-0 d-flex justify-content-between align-items-start">
            <div>
              <div class="fs-4 fw-semibold">@money(number_format((float) $wal["balance_percentage"]["current_balance"], 2, ".", ""), $wal["wallet"]["currency"]) <span class="fs-6 fw-normal {{$wal['balance_percentage']['is_increase'] ? 'text-success' : 'text-danger'}}">({{ Number::percentage($wal["balance_percentage"]["percentage_change"], precision: 2) }}
                  <svg class="icon">
                    <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-arrow-'. ($wal['balance_percentage']['is_increase'] ? 'top' : 'bottom')) }}"></use>
                  </svg>)</span>
              </div>
              <div>{{$wal["wallet"]["name"]}}</div>
            </div>
            <div class="dropdown">
              <button class="btn btn-transparent text-white p-0" type="button" data-coreui-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <svg class="icon">
                  <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-options') }}"></use>
                </svg>
              </button>
              <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="{{ route('financial.wallets.show',['wallet' => $wal['wallet']['id']]) }}">Detail</a>
                @can(Permissions::EDIT_WALLETS)
                <a class="dropdown-item" href="{{ route('financial.wallets.edit', ['wallet' => $wal['wallet']['id']]) }}">Edit</a>
                @endcan
                @can(Permissions::VIEW_TRANSACTIONS)
                <a class="dropdown-item" href="{{ route('financial.wallets.transactions.index', ['wallet' => $wal['wallet']['id']]) }}">All Transactions</a>
                @endcan
              </div>
            </div>
          </div>
          <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
            <x-chartjs-component :chart="$wal['chart']['monthly_balance']" />
          </div>
        </div>
      </div>
      @empty
      <div class="col-12">
        <p class="fw-bold">You don't have any wallet. Create one first.</p>
      </div>
      @endforelse
    </div>
  </div>
</div>
@endsection