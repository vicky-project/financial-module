@extends('viewmanager::layouts.app')

@use('Modules\Financial\Constants\Permissions')

@section('page-title', 'Edit Wallet')

@section('content')
<div class="card">
  <div class="card-header text-end">
    <div class="float-start me-auto">
      <a href="{{ url()->previous() }}" class="btn btn-secondary" role="button">
        <svg class="icon">
          <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-arrow-thick-left') }}"></use>
        </svg>
      </a>
    </div>
    <div class="card-title">Edit - {{$wallet->wallet_name}}</div>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('financial.wallets.update', $wallet) }}">
      @csrf
      @method('PUT')
      <div class="pt-2 mt-4 border-top border-primary">
        <button type="submit" class="btn btn-block btn-success">
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