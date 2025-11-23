@extends('viewmanager::layouts.app')

@use('Modules\Financial\Constants\Permissions')
@use('Modules\Financial\Enums\CashflowType')

@section('page-title', 'Create Transaction')

@section('content')
<div class="card">
  <div class="card-header text-end">
    <div class="float-start me-auto">
      <a href="{{ route('financial.wallets.transactions.index', ['wallet' => $wallet, 'year' => $year, 'month' => $month]) }}" class="btn btn-secondary" role="button">
        <svg class="icon">
          <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-arrow-thick-left') }}"></use>
        </svg>
      </a>
    </div>
    <h5 class="card-title">Create Transaction</h5>
  </div>
  <div class="card-body">
    <ul class="nav nav-pills mb-3">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="pills-create-tab" data-coreui-toggle="pill" data-coreui-target="#pills-create" type="button" role="tab" aria-controls="pills-create" aria-selected="true">Create</button>
      </li>
      @can(Permissions::UPLOAD_TRANSACTIONS)
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="pills-upload-tab" data-coreui-toggle="pill" data-coreui-target="#pills-upload" type="button" role="tab" aria-controls="pills-upload" aria-selected="false">Upload</button>
      </li>
      @endcan
    </ul>
    <div class="tab-content" id="pills-tabContent">
      <div class="tab-pane fade show active" id="pills-create" role="tabpanel" aria-labelledby="pills-create-tab">
        <form method="POST" action="{{route('financial.wallets.transactions.store', $wallet)}}" class="needs-validation" novalidate>
          @csrf
          <div class="mb-3">
            <label class="form-label">Wallet</label>
            <select class="form-select @error('wallet_id') is-invalid @enderror" name="wallet_id" disabled readonly>
              @foreach($wallets as $w)
              <option value="{{ $w->id }}" @selected($w->id === $wallet->id)>{{ $w->wallet_name}}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
              @forelse($categories as $category)
              <option value="{{$category->id}}">{{ $category->name }}</option>
              @empty
              <option value="">No category available</option>
              @endforelse
            </select>
            @error("category_id")
            <div class="invalid-feedback">{{$message}}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <input type="text" class="form-control @error('description') is-invalid @enderror" name="description" placeholder="What name is..." required>
            @error('description')
            <div class="invalid-feedback">{{$message}}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Amount</label>
            <input type="number" min="0" value="" class="form-control @error('amount') is-invalid @enderror" name="amount" placeholder="How many...">
            @error("amount")
            <div class="invalid-feedback">{{$message}}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="datetime-local" name="date" class="form-control @error('date') is-invalid @enderror" placeholder="When..." required>
            @error("date")
            <div class="invalid-feedback">{{$message}}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea class="form-control" name="notes"></textarea>
          </div>
          <div class="pt-2 mt-4 border-top border-info">
            <button type="submit" class="btn btn-block btn-success">
              <svg class="icon">
                <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-paper-plane') }}"></use>
              </svg>
              Save
            </button>
          </div>
        </form>
      </div>
      @can(Permissions::UPLOAD_TRANSACTIONS)
      <div class="tab-pane fade" id="pills-upload" role="tabpanel" aria-labelledby="pills-upload-tab">
        <form method="POST" action="{{ route('financial.wallets.transactions.upload', $wallet) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
          @csrf
          <div class="mb-3">
            <label class="form-label">From</label>
            <select class="form-select" name="apps_name">
              <option value="firefly">Firefly</option>
              <option value="vickyserver">Vickyserver</option>
              <option value="e-statement">E-Statement</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Upload Transaction File</label>
            <input type="file" class="form-control @error('file') is-invalid @enderror" name="file" required>
            @error("file")
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="text" class="form-control @error('password') is-invalid @enderror" name="password">
            @error("password")
            <div class="invalid-feedback">{{$message}}</div>
            @enderror
            <div class="row my-2">
              <div class="col">
                <span class="small text-muted">
                  <svg class="icon me-2 text-warning">
                    <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                  </svg>
                  If your file protect with password, just put it here to unlock (No required if your file un protect with password).
                </span>
              </div>
            </div>
          </div>
          <div class="pt-2 mt-4 border-top border-primary">
            <button type="submit"class="btn btn-block btn-success">
              <svg class="icon me-2">
                <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-paper-plane') }}"></use>
              </svg>
              Upload
            </button>
          </div>
        </form>
      </div>
      @endcan
    </div>
  </div>
</div>
@endsection