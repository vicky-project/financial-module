@extends('viewmanager::layouts.app')

@use('Modules\Financial\Constants\Permissions')
@use('Modules\Financial\Enums\CashflowType')

@section('page-title', 'All Categories')

@section('content')
<div class="row mb-4 pb-2 border-bottom border-primary">
  <div class="col-12 col-sm-6 col-xl-4 col-xxl-3 mb-2">
    <div class="card overflow-hidden">
      <div class="card-body p-0 d-flex align-items-center">
        <div class="bg-success text-white py-4 px-5 me-3">
          <svg class="icon icon-xl">
            <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-dollar') }}"></use>
          </svg>
        </div>
        <div>
          <div class="fs-6 fw-semibold text-primary">{{ $inCount }}</div>
          <div class="text-body-secondary text-uppercase fw-semibold small">Total Income</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-xl-4 col-xxl-3 mb-2">
    <div class="card overflow-hidden">
      <div class="card-body p-0 d-flex align-items-center">
        <div class="bg-danger text-white py-4 px-5 me-3">
          <svg class="icon icon-xl">
            <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
          </svg>
        </div>
        <div>
          <div class="fs-6 fw-semibold text-primary">{{ $exCount }}</div>
          <div class="text-body-secondary text-uppercase fw-semibold small">Total Expense</div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="card">
  <div class="card-header text-end">
    <div class="float-start me-auto text-start">
      <h5 class="card-title">Categories</h5>
      <span class="small">{{ $categories->total() }} items.</span>
    </div>
    @can(Permissions::CREATE_CATEGORIES)
      <a href="{{ route('financial.categories.create') }}" class="btn btn-success" role="button">
        <svg class="icon">
          <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
        </svg>
      </a>
    @endcan
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover table-bordered">
        <thead>
          <th>#</th>
          <th>Name</th>
          <th>Type</th>
          <th>Transactions</th>
          <th>Action</th>
        </thead>
        <tbody>
          @forelse($categories as $category)
          <tr>
            <td>{{ $loop->iteration}}</td>
            <td>{{ $category->name }}</td>
            <td><span class="text-{{$category->type === CashflowType::INCOME ? 'success' : 'danger'}}">{{ $category->type }}</span></td>
            <td>{{ $category->transactions_count}} transactions</td>
            <td>
              <div class="btn-group btn-xs">
                @can(Permissions::VIEW_CATEGORIES)
                <a href="{{ route('financial.categories.show', $category) }}" class="btn btn-info" role="button">
                  <svg class="icon">
                    <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-equalizer') }}"></use>
                  </svg>
                </a>
                @endcan
                @can(Permissions::EDIT_CATEGORIES)
                <a href="{{ route('financial.categories.edit', $category) }}" class="btn btn-success" role="button">
                  <svg class="icon">
                    <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-pen') }}"></use>
                  </svg>
                </a>
              </div>
              @endcan
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="text-center">No category available.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="pt-2 mt-4 border-primary border-top">{{ $categories->links() }}</div>
  </div>
</div>
@endsection