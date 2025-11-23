@extends('viewmanager::layouts.app')

@use('Modules\Financial\Constants\Permissions')
@use('Modules\Financial\Helpers\Helper')

@section('page-title', 'Trash')

@section('content')
<div class="card">
  <div class="card-header text-end">
    <div class="d-flex float-start me-auto">
      <a href="{{ route('financial.wallets.transactions.detail', ['wallet' => $wallet, 'year' => $year, 'month' => $month]) }}" class="btn btn-secondary me-2" role="button" title="Back">
        <svg class="icon">
          <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-arrow-thick-left') }}"></use>
        </svg>
      </a>
      @can(Permissions::RESTORE_TRANSACTIONS)
      <form method="POST" action="{{route('financial.wallets.transactions.restore-all', ['wallet' => $wallet, 'year' => $year, 'month' => $month])}}">
        @csrf
        <button type="submit" class="btn btn-warning me-2" title="Restore All" @disabled($trashs->count() === 0)>
          <svg class="icon">
            <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-action-undo') }}"></use>
          </svg>
        </button>
      </form>
      @endcan
      @can(Permissions::DELETE_TRANSACTIONS)
      <form method="POST" action="{{route('financial.wallets.transactions.force-delete-all', ['wallet' =>$wallet, 'year' => $year, 'month' => $month])}}">
        @csrf
        @method("DELETE")
        <button type="submit" class="btn btn-danger me-2" title="Force Delete All Trash" @disabled($trashs->count() === 0)>
          <svg class="icon">
            <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
          </svg>
        </button>
      </form>
      @endcan
    </div>
    <h5 class="card-title">
      @if(isset($month) && isset($year))
      {{$month}} - {{$year}}
      @else
      All Trash
      @endisset
    </h5>
    <span class="small ms-2">
      Total {{$trashs->total()}} items.
    </span>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover table-bordered">
        <thead>
          <th>Date</th>
          <th>Amount</th>
          <th>Description</th>
          <th>Action</th>
        </thead>
        <tbody>
          @forelse($trashs as $trash)
          <tr>
            <td>{{$trash->date->format("d-m-Y H:i:s")}}</td>
            <td class="{{Helper::getColortextAmount($trash->category)}}">@money(number_format((float) $trash->amount, 2, ".", ""), $wallet->currency)</td>
            <td>{{ str($trash->description)->limit(50) }}</td>
            <td>
              <div class="btn-group">
                @can(Permissions::VIEW_TRANSACTIONS)
                <a href="{{ route('financial.wallets.transactions.show', ['wallet' => $wallet, 'transaction' => $trash, 'year' => $year, 'month' => $month]) }}" class="btn btn-outline-primary" role="button">
                  <svg class="icon">
                    <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-equalizer') }}"></use>
                  </svg>
                </a>
                @endcan
                @can(Permissions::DELETE_TRANSACTIONS)
                <form method="POST" action="{{ route('financial.wallets.transactions.force-delete', ['wallet' => $wallet, 'transaction' => $trash]) }}">
                  @csrf
                  @method("DELETE")
                  <button type="submit" class="btn btn-outline-danger" role="button" title="Force delete transaction">
                    <svg class="icon">
                      <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                    </svg>
                  </button>
                </form>
                @endcan
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="text-center"><em>No data in trash.</em></td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="pt-2 mt-4 border-top border-primary">
      {{ $trashs->links() }}
    </div>
  </div>
</div>
@endsection