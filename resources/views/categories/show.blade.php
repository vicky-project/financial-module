@extends('viewmanager::layouts.app')

@use('Modules\Financial\Constants\Permissions')
@use('Modules\Financial\Helpers\Helper')

@section('page-title', 'Category - '. $category->name)

@section('content')
<div class="card">
  <div class="card-header text-end">
    <div class="float-start me-auto">
      <a href="{{  route('financial.categories.index') }}" class="btn btn-secondary" role="button">
        <svg class="icon">
          <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-arrow-thick-left') }}"></use>
        </svg>
      </a>
      @can(Permissions::EDIT_CATEGORIES)
        <a href="{{ route('financial.categories.edit', $category) }}" class="btn btn-success" role="button">
          <svg class="icon">
              <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-pen') }}"></use>
          </svg>
        </a>
      @endcan
    </div>
    <div class="card-title">{{ $category->name}}</div>
  </div>
  <div class="card-body">
    <ul class="list-group list-group-flush">
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Name</strong>
        <span class="text-muted">{{ $category->name }}</span>
      </li>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Type</strong>
        <span class="badge {{ Helper::getColortextAmount($category) }}">{{ str($category->type->value)->upper() }}</span>
      </li>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Icon</strong>
        <span class="badge"></span>
      </li>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Transactions</strong>
        <a href="{{ route('financial.categories.transactions.index', $category) }}" class="btn btn-xs btn-warning" role="button" title="See all transaction that use this category">{{ $category->transactions->count()}} items</a>
      </li>
    </ul>
  </div>
</div>
@endsection