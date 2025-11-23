@extends('viewmanager::layouts.app')

@use('Modules\Financial\Constants\Permissions')
@use('Modules\Financial\Enums\CashflowType')

@section('page-title', 'Create Category')

@section('content')
<div class="card">
  <div class="card-header text-end">
    <div class="float-start me-auto">
      <a href="{{ route('financial.categories.index') }}" class="btn btn-secondary" role="button">
        <svg class="icon">
          <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-arrow-thick-left') }}"></use>
        </svg>
      </a>
    </div>
    <div class="float-start me-auto"></div>
    <div class="card-title">Create Category</div>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('financial.categories.store') }}" class="needs-validation" novalidate>
      @csrf
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input class="form-control @error('name') is-invalid @enderror" name="name">
        @error("name")
        <div class="invalid-feedback">{{$message}}</div>
        @enderror
      </div>
      <div class="mb-3">
        <label class="form-label">Type</label>
        <select class="form-select @error('type') is-invalid @enderror" name="type">
          @foreach(CashflowType::cases() as $type)
          <option value="{{$type->value}}">{{$type->value}}</option>
          @endforeach
        </select>
        @error("type")
        <div class="invalid-feedback">{{$message}}</div>
        @enderror
      </div>
      <div class="mb-3">
        <label class="form-label">Icon</label>
        <input class="form-control" name="icon">
      </div>
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