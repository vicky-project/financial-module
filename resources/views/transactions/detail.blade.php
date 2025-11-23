@extends('viewmanager::layouts.app')

@use('Modules\Financial\Constants\Permissions')
@use('Modules\Financial\Enums\CashflowType')
@use('Modules\Financial\Helpers\Helper')
@use('Illuminate\Support\Number')

@section('page-title', $month. " - " .$year)

@section('content')
<div class="row mb-2 g-4">
    <div class="col-12 col-sm-6 col-xl-4 col-xxl-3">
    <div class="card text-white bg-primary">
      <div class="card-body pb-0 d-flex justify-content-between align-items-start">
        <div>
          <div class="fs-4 fw-semibold">@money(number_format((float) $balanceChange["current_balance"], 2, ".", ""), $wallet->currency) (<span class="fs-6 fw-normal text-{{$balanceChange['is_increase'] ? 'success' : 'danger'}}">{{Number::percentage($balanceChange["percentage_change"], precision: 2)}}
              <svg class="icon">
                <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-arrow-'. ($balanceChange['is_increase'] ? 'top' : 'bottom')) }}"></use>
              </svg></span>)
          </div>
          <div>{{$wallet->wallet_name}}</div>
        </div>
      </div>
      <div class="c-chart-wrapper mt-3 mx-3" style="height:70px;">
        <x-chartjs-component :chart="$chartBalance" />
      </div>
    </div>
  </div>
</div>
<div class="row my-2">
  <div class="col">
    <div class="card">
      <div class="card-header text-end">
        <div class="float-start me-auto">
          <a href="{{ route('financial.wallets.transactions.index', ['wallet' => $wallet, 'year' => $year, 'month' => $month]) }}" class="btn btn-secondary" role="button">
            <svg class="icon">
              <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-arrow-thick-left') }}"></use>
            </svg>
          </a>
          @can(Permissions::CREATE_TRANSACTIONS)
          <a href="{{ route('financial.wallets.transactions.create', [$wallet, 'year' => $year, 'month' => $month]) }}" class="btn btn-primary" role="button">
            <svg class="icon">
              <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
            </svg>
          </a>
          @endcan
          <a href="{{ route('financial.wallets.transactions.trash', ['wallet' => $wallet, 'year' => $year, 'month' => $month]) }}" class="btn btn-warning" role="button">
            <svg class="icon">
              <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-recycle') }}"></use>
            </svg>
          </a>
        </div>
        <h5 class="card-title">{{$month}} - {{$year}}</h5>
        <span class="small ms-2">Total {{$transactions->total()}} items.</span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-bordered">
            <thead>
              <th>Date</th>
              <th>Amount/Detail</th>
              <th>Description</th>
              <th>Action</th>
            </thead>
            <tbody>
              @forelse($transactions as $transaction)
              <tr>
                <td>
                  <strong>{{$transaction->date->format("d-m-Y")}}
                  </strong>
                  <small>{{$transaction->date->format("H:i:s")}}</small>
                </td>
                <td>
                  <div class="row">
                    <div class="col-auto">
                      <span class="{{ Helper::getColortextAmount($transaction->category)}}">@money(number_format((float) $transaction->amount, 2, ".", ""), $wallet->currency)</span>
                    </div>
                    <div class="col-auto">
                      <small class="{{ Helper::getColortextAmount($transaction->category)}}">{{ $transaction->category->name }}</small>
                    </div>
                  </div>
                </td>
                <td>{{ str($transaction->description)->limit(50) }}</td>
                <td>
                  <nobr>
                    @can(Permissions::VIEW_TRANSACTIONS)
                    <a href="{{ route('financial.wallets.transactions.show', ['wallet' => $wallet, 'transaction' => $transaction, 'year' => $year, 'month' => $month]) }}" class="btn btn-outline-primary" role="button">
                      <svg class="icon">
                        <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-equalizer') }}"></use>
                      </svg>
                    </a>
                    @endcan
                    @can(Permissions::EDIT_TRANSACTIONS)
                    <a href="{{ route('financial.wallets.transactions.edit', [$wallet, $transaction]) }}" class="btn btn-outline-success" role="button">
                      <svg class="icon">
                        <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-pen') }}"></use>
                      </svg>
                    </a>
                    @endcan
                  </nobr>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="3" class="text-center"><em>No transaction available.</em></td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="mt-4 pt-2 border-top border-primary">
          {{$transactions->links()}}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection