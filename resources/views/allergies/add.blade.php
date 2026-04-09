@extends('layouts.app')

@section('title', __('add_new_allergy'))

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card" style="margin-top:20px;">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ __('add_new_allergy') }}</h4>
                        <a href="{{ route('allergies.manage') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa fa-arrow-left me-2"></i>{{ __('back_to_list') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('allergies.store') }}" method="POST" class="theme-form">
                            @csrf
                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <label class="col-form-label">{{ __('allergy_title') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label">{{ __('allergy_code') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label">{{ __('status') }} <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>{{ __('active') }}</option>
                                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>{{ __('inactive') }}</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 text-end pt-3">
                                    <button type="submit" class="btn btn-primary px-4">{{ __('save_allergy') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
