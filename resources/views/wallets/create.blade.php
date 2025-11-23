@extends('viewmanager::layouts.app')

@use('Modules\Financial\Constants\Permissions')
@use('Modules\Financial\Enums\WalletType')

@section('page-title', 'Your Wallets')

@section('content')
<div class="card">
  <div class="card-header text-end">
    <div class="float-start me-auto">
      <a href="{{ route('financial.wallets.index') }}" class="btn btn-secondary">
        <svg class="icon">
          <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-arrow-thick-left') }}"></use>
        </svg>
      </a>
    </div>
    <h5 class="card-title">Create Wallet</h5>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('financial.wallets.store') }}">
      @csrf
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" class="form-control" name="wallet_name" placeholder="Enter wallet name..." required>
      </div>
      <div class="mb-3">
        <label class="form-label">Number</label>
        <input type="number" min="1" class="form-control" name="wallet_number" placeholder="Enter number rekening or else..." required>
      </div>
      <div class="mb-3">
        <label class="form-label">Type</label>
        <select class="form-select" name="wallet_type">
          @foreach(WalletType::cases() as $type)
          <option value="{{$type->value}}">{{$type->value}}</option>
          @endforeach
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Initial Balance</label>
        <input type="number" class="form-control" name="initial_balance" placeholder="First balance if exists..." min="1" value="0">
      </div>
      <div class="mb-3">
        <label class="form-label">Currency</label>
        <select class="form-select" name="currsncy">
          @foreach($currencies as $currency => $name)
            <option value="{{$currency}}" @selected($currency ==="IDR")>{{$name}}</option>
          @endforeach
        </select>
      </div>
      <div class="mt-4 pt-2 border-top border-warning">
        <button type="submit" class="btn btn-outline bg-success">
          <svg class="icon me-2">
            <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-paper-plane') }}"></use>
          </svg>
          Save
        </button>
      </div>
    </form>
  </div>
</div>
@endsection