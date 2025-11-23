@extends('viewmanager::layouts.app')

@use('Modules\Financial\Constants\Permissions')
@use('Modules\Financial\Enums\CashflowType')

@section('page-title', 'Edit Category - '. $category->name)

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
    <div class="card-title">Edit - {{ $category->name}}</div>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('financial.categories.update', $category) }}" class="needs-validation" novalidate>
      @csrf
      @method("PUT")
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{$category->name}}">
        @error("name")
        <div class="invalid-feedback">{{$message}}</div>
        @enderror
      </div>
      <div class="mb-3">
        <label class="form-label">Type</label>
        <select class="form-select @error('type') @enderror" name="type">
          @foreach(CashflowType::cases() as $type)
          <option value="{{$type->value}}" @selected($type === $category->type)>{{$type->value}}</option>
          @endforeach
        </select>
        @error("type")
        <div class="invalid-feedback">{{$message}}</div>
        @enderror
      </div>
      <div class="mb-3">
        <label class="form-label">Icon</label>
        <input class="form-control @error('icon') is-invalid @enderror" name="icon">
        @error("icon")
        <div class="invalid-feedback">{{$message}}</div>
        @enderror
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