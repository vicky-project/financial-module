@extends('viewmanager::layouts.app')

@use('Modules\Financial\Constants\Permissions')
@use('Modules\Financial\Helpers\Helper')
@use('Illuminate\Support\Number')

@section('page-title', $wallet->wallet_name . ($year ? ' - '. $year : ''))

@section('content')
<div class="row pb-2 mb-4 border-bottom border-primary">
  <div class="col">
    <div class="card">
      <div class="card-body">
        <a href="{{ route('financial.wallets.index') }}" class="btn btn-secondary" role="button">
            <svg class="icon">
              <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-home') }}"></use>
            </svg>
          </a>
          @if(request()->has("year"))
          <a href="{{ route('financial.wallets.transactions.index', $wallet) }}" class="btn btn-secondary" role="button">
            <svg class="icon">
              <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-arrow-thick-left') }}"></use>
            </svg>
          </a>
          @endif
          @can(Permissions::CREATE_TRANSACTIONS)
          <a href="{{ route('financial.wallets.transactions.create', $wallet) }}" class="btn btn-primary" role="button">
            <svg class="icon">
              <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
            </svg>
          </a>
          @endcan
          @can(Permissions::MANAGE_WALLETS)
          <a href="{{ route('financial.wallets.recalculate', $wallet) }}" class="btn btn-info" role="button">
            <svg class="icon">
              <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-sync') }}"></use>
            </svg>
          </a>
          @endcan
      </div>
    </div>
  </div>
</div>
<div class="row mb-2">
  <div class="col-12">
    <div class="card text-white bg-primary">
      <div class="card-body pb-0 d-flex justify-content-between align-items-start">
        <div>
          <div class="fs-4 fw-semibold">@money(number_format((float) $widgets["balance"]["current_balance"], 2, ".", ""), $wallet->currency) (<span class="fs-6 fw-normal text-{{$widgets['balance']['is_increase'] ? 'success' : 'danger'}}">{{$widgets["balance"]["is_increase"] ? "+" : ""}}{{$widgets["balance"]["percentage_change"]}}%
              <svg class="icon">
                <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-arrow-'. ($widgets['balance']['is_increase'] ? 'top' : 'bottom')) }}"></use>
              </svg></span>)
          </div>
          <div>{{$wallet->wallet_name}}</div>
        </div>
      </div>
      <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
        <x-chartjs-component :chart="$widgets['chart']['monthly_balance']" />
      </div>
    </div>
  </div>
</div>
<div class="row mb-2 g-3">
  <div class="col-12 col-sm-6 col-xl-4 col-xxl-3">
    <div class="card overflow-hidden">
      <div class="card-body p-0 d-flex align-items-center">
        <div class="bg-danger text-white py-4 px-5 me-3">
          <svg class="icon icon-xl">
            <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-money') }}"></use>
          </svg>
        </div>
        <div>
          <div class="fs-6 fw-semibold text-{{$widgets["income"]["is_increase"] ? "success" : "danger"}}">{{ Number::percentage($widgets["income"]["percentage"], precision: 2) }}
            (<svg class="icon">
              <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-arrow-'. ($widgets['income']['is_increase'] ? 'top' : 'bottom')) }}"></use>
              </svg>)
          </div>
          <div class="text-body-secondary text-uppercase fw-semibold small">
            @money(number_format((float) $widgets["income"]["current"], 2, ".", ""), $wallet->currency)
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-4 col-xxl-3">
    <div class="card overflow-hidden">
      <div class="card-body p-0 d-flex align-items-center">
        <div class="bg-danger text-white py-4 px-5 me-3">
          <svg class="icon icon-xl">
            <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
          </svg>
        </div>
        <div>
          <div class="fs-6 fw-semibold text-{{$widgets["income"]["is_increase"] ? "success" : "danger"}}">{{ Number::percentage($widgets["expense"]["percentage"], precision: 2) }}
            (<svg class="icon">
              <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-arrow-'. ($widgets['expense']['is_increase'] ? 'top' : 'bottom')) }}"></use>
              </svg>)
          </div>
          <div class="text-body-secondary text-uppercase fw-semibold small">
            @money(number_format((float) $widgets["expense"]["current"], 2, ".", ""), $wallet->currency)
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-4 col-xxl-3">
    <div class="card overflow-hidden">
      <div class="card-body p-0 d-flex align-items-center">
        <div class="bg-warning text-white py-4 px-5 me-3">
          <svg class="icon icon-xl">
            <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-swap-horizontal') }}"></use>
          </svg>
        </div>
        <div>
          <div class="fs-6 fw-semibold text-primary">{{ $widgets["transaction_count"] }}</div>
          <div class="text-body-secondary text-uppercase fw-semibold small">Total Transactions</div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row my-2">
  <div class="col">
    <div class="card">
      <div class="card-header text-end">
        <div class="float-start me-auto">
          <h5 class="card-title">{{ $wallet->wallet_name}}</h5>
          @if(request()->has("year"))
          <span class="small ms-2">{{ $year }}</span>
          @endif
        </div>
        <a href="{{ route('financial.wallets.transactions.trash', ['wallet' => $wallet]) }}" class="btn btn-warning" role="button">
          <svg class="icon">
            <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-recycle') }}"></use>
          </svg>
        </a>
      </div>
      <div class="card-body">
        <div class="row g-4">
          @forelse($transactions as $currentYear => $months)
            @if(request()->input('year'))
            @foreach($months as $month => $transaction)
            <div class="col-6 col-sm-4 col-xl-2">
              <a href="{{route('financial.wallets.transactions.detail', ['wallet'=>$wallet, 'year' => $currentYear, 'month' => $month])}}" class="btn btn-ouline">
                <div class="card">
                  <div class="card-body text-center d-flex justify-content-between">
                    <div>
                      <div class="text-body-secondary small text-uppercase fw-semibold">{{$month}}</div>
                      <div class="fs-6 fw-semibold py-3">{{$transaction->count()}} transactions</div>
                    </div>
                    <div class="dropdown">
                      <button class="btn btn-transparent text-white p-0" type="button" data-coreui-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <svg class="icon">
                          <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-options') }}"></use>
                        </svg>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end">
                        <form method="POST" action="{{ route('financial.wallets.transactions.mass-destroy', ['wallet' => $wallet, 'year' => $currentYear, 'month' => $month]) }}" onsubmit="return if(confirm('Are you sure to delete all transaction in this month ? '+ {{ $month }} + {{ $currentYear }}))">
                          @csrf
                          @method("DELETE")
                          <button type="submit" class="dropdown-item bg-danger">
                            <svg class="icon me-2">
                              <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                            </svg>Delete
                          </button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </a>
            </div>
            @endforeach
            @else
            <div class="col-6 col-sm-4 col-xl-2">
              <a href="{{route('financial.wallets.transactions.index', ['wallet'=> $wallet, 'year' => $currentYear])}}" class="btn btn-ouline">
                <div class="card">
                  <div class="card-body text-center">
                    <div class="text-body-secondary small text-uppercase fw-semibold">{{$currentYear}}</div>
                    <div class="fs-6 fw-semibold py-3">{{$months->count()}} months</div>
                    <div class="c-chart-wrapper mx-auto" style="height:40px;width:80px">
                    </div>
                  </div>
                </div>
              </a>
            </div>
            @endif
          @empty
          <div class="text-center text-muted">
            <p class="card-text">You don't have any transaction now. Create one first.</p>
          </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>
@endsection