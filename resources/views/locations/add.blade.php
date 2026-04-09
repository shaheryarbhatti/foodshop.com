<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('add_new_location') }}</title>

    @extends('layouts.app')
    @section('content')
    <div class="page-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card" style="margin-top: 20px;">
                        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">{{ __('add_new_location') }}</h4>
                            <a href="{{ route('locations.manage') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-arrow-left me-2"></i> {{ __('back_to_list') }}
                            </a>
                        </div>

                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong>Whoops!</strong> There were some problems with your input.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('locations.store') }}" class="theme-form">
                                @csrf

                                <div class="row g-4">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="mb-3">
                                            <label class="col-form-label" for="name">{{ __('location_name') }} <span class="text-danger">*</span></label>
                                            <input type="text" name="name" id="name"
                                                class="form-control @error('name') is-invalid @enderror"
                                                value="{{ old('name') }}" placeholder="{{ __('location_name') }}" required autofocus>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12">
                                        <div class="mb-3">
                                            <label class="col-form-label" for="region_id">{{ __('region_name') }} <span class="text-danger">*</span></label>
                                            <select name="region_id" id="region_id" class="form-select searchable-select @error('region_id') is-invalid @enderror" required>
                                                <option value="">{{ __('select_parent_module') }}</option>
                                                @foreach($regions as $region)
                                                    <option value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>
                                                        {{ $region->name }} ({{ optional($region->country)->name }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('region_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>


                                    <div class="col-lg-6 col-md-12">
                                        <div class="mb-3">
                                            <label class="col-form-label" for="status">{{ __('status') }} <span class="text-danger">*</span></label>
                                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>{{ __('active') }}</option>
                                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>{{ __('inactive') }}</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary px-5">
                                        <i class="fa fa-save me-2"></i> {{ __('save_location') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endsection

    @push('styles')
        <link rel="stylesheet" href="{{ asset('public/assets/css/vendors/select2.css') }}">
    @endpush

    @push('scripts')
        <script src="{{ asset('public/assets/js/select2/select2.full.min.js') }}"></script>
        <script>
            $(document).ready(function () {
                $('.searchable-select').select2({
                    width: '100%',
                    placeholder: @json(__('region_name')),
                    allowClear: true
                });
            });
        </script>
    @endpush
