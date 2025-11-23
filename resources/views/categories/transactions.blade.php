@extends('viewmanager::layouts.app')

@use('Modules\Financial\Constants\Permissions')
@use('Modules\Financial\Helpers\Helper')

@section('page-title', 'Category - '. $category->name)

@section('content')
<div class="card">
  <div class="card-header text-end">
    <div class="float-start me-auto">
      <a href="{{ route('financial.categories.show', $category) }}" class="btn btn-secondary" role="button">
        <svg class="icon">
          <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-arrow-thick-left') }}"></use>
        </svg>
      </a>
    </div>
    <h5 class="card-title"></h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover table-bordered">
        <thead>
          <th>Date</th>
          <th>Description</th>
          <th>Amount</th>
          <th>Action</th>
        </thead>
        <tbody>
          @forelse($transactions as $transaction)
          <tr>
            <td>{{ $transaction->date->format("d-m-Y H:i:s") }}</td>
            <td>{{ str($transaction->description)->limit(50) }}</td>
            <td class="{{Helper::getColortextAmount($category)}}">@money(number_format((float) $transaction->amount, 2, ".", ""), $transaction->wallet->currency)</td>
            <td>
              <div class="btn-xs btn-group">
                @can(Permissions::EDIT_TRANSACTIONS)
                <a href="{{ route('financial.wallets.transactions.edit', [$transaction->wallet, $transaction]) }}" class="btn btn-success" role="button">
                  <svg class="icon">
                    <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-pen') }}"></use>
                  </svg>
                </a>
                @endcan
                @can(Permissions::DELETE_TRANSACTIONS)
                <form method="POST" action="{{ route('financial.wallets.transactions.destroy', [$transaction->wallet, $transaction]) }}">
                  @csrf
                  @method("DELETE")
                  <button type="submit" class="btn btn-danger">
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
            <td colspan="" class="text-center"><em>No transaction available here.</em></td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-4 pt-2 border-top border-primary">
      {{ $transactions->links() }}
    </div>
  </div>
</div>
@endsection