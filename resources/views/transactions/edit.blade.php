@extends('viewmanager::layouts.app')

@use('Modules\Financial\Constants\Permissions')

@section('page-title', 'Edit Transaction')

@section('content')
<div class="row my-2">
  <div class="col">
    <div class="card">
      <div class="card-header text-end">
        <div class="float-start me-auto">
          <a href="{{ url()->previous() }}" class="btn btn-secondary" role="button">
            <svg class="icon">
              <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-arrow-thick-left') }}"></use>
            </svg>
          </a>
        </div>
        <h5 class="card-title">Edit Transaction</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{route('financial.wallets.transactions.update', [$wallet, $transaction])}}" class="needs-validation" novalidate>
          @csrf
          @method("PUT")
          <input type="hidden" name="wallet_id" value="{{$wallet->id}}">
          <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select @error('category_id') @enderror">
              @foreach($categories as $category)
              <option value="{{$category->id}}" @selected($category->id === $transaction->category->id)>{{$category->name}}</option>
              @endforeach
            </select>
            @error("category_id")
            <div class="invalid-feedback">{{$message}}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <input class="form-control @error('description') is-invalid @enderror" name="description" value="{{$transaction->description}}">
            @error("description")
            <div class="invalid-feedback">{{$message}}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Amount</label>
            <input type="number" class="form-control @error('amount') is-invalid @enderror" name="amount" min="0" value="{{$transaction->amount}}">
            @error("amount")
            <div class="invalid-feedback">{{$message}}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="datetime-local" class="form-control @error('date') is-invalid @enderror" name="date" value="{{$transaction->date}}">
            @error("date")
            <div class="is-invalid">{{$message}}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control">{{$transaction->notes}}</textarea>
          </div>
          <div class="pt-4 mt-2 border-top border-primary">
            <button type="submit" class="btn btn-block bg-success">
              <svg class="icon me-2">
                <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-paper-plane') }}"></use>
              </svg>
              Save
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection