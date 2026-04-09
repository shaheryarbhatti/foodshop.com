<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('edit_shipping_fee') }}</title>

@extends('layouts.app')
@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card" style="margin-top:20px;">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ __('edit_shipping_fee') }}</h4>
                        <a href="{{ route('shipping.manage') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa fa-arrow-left me-2"></i>{{ __('back_to_list') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('shipping.update', $shipping->id) }}" class="theme-form">
                            @csrf
                            @method('PUT')
                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <label class="col-form-label">{{ __('shipping_fee_title') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                        value="{{ old('title', $shipping->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label">{{ __('tax_title') }}</label>
                                    <select name="tax_id" class="form-select @error('tax_id') is-invalid @enderror">
                                        <option value="">{{ __('select_tax') }}</option>
                                        @foreach($taxes as $tax)
                                            <option value="{{ $tax->id }}" {{ old('tax_id', $shipping->tax_id) == $tax->id ? 'selected' : '' }}>
                                                {{ $tax->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tax_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label">{{ __('shipping_fee') }} <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" name="fee"
                                        class="form-control @error('fee') is-invalid @enderror"
                                        value="{{ old('fee', $shipping->fee) }}" required>
                                    @error('fee')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label">{{ __('min_order_amount') }}</label>
                                    <input type="number" step="0.01" min="0" name="min_order_amount"
                                        class="form-control @error('min_order_amount') is-invalid @enderror"
                                        value="{{ old('min_order_amount', $shipping->min_order_amount) }}">
                                    @error('min_order_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label">{{ __('shipping_condition') }} <span class="text-danger">*</span></label>
                                    <select name="condition_type"
                                        class="form-select @error('condition_type') is-invalid @enderror" required>
                                        <option value="less_than" {{ old('condition_type', $shipping->condition_type) == 'less_than' ? 'selected' : '' }}>
                                            {{ __('less_than') }}
                                        </option>
                                        <option value="equal_to" {{ old('condition_type', $shipping->condition_type) == 'equal_to' ? 'selected' : '' }}>
                                            {{ __('equal_to') }}
                                        </option>
                                        <option value="greater_than" {{ old('condition_type', $shipping->condition_type) == 'greater_than' ? 'selected' : '' }}>
                                            {{ __('greater_than') }}
                                        </option>
                                        <option value="between" {{ old('condition_type', $shipping->condition_type) == 'between' ? 'selected' : '' }}>
                                            {{ __('between') }}
                                        </option>
                                    </select>
                                    @error('condition_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label">{{ __('distance_value') }} <span class="text-danger">*</span>
                                        <small class="text-muted ms-2">{{ __('shipping_distance_between_note') }}</small>
                                    </label>
                                    <input type="text" name="distance_value"
                                        class="form-control @error('distance_value') is-invalid @enderror"
                                        value="{{ old('distance_value', $shipping->distance_value) }}" required>
                                    @error('distance_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label">{{ __('maximum_order_amount') }}</label>
                                    <input type="number" step="0.01" min="0" name="maximum_order_amount"
                                        class="form-control @error('maximum_order_amount') is-invalid @enderror"
                                        value="{{ old('maximum_order_amount', $shipping->maximum_order_amount) }}">
                                    @error('maximum_order_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label">{{ __('delivery_type') }} <span class="text-danger">*</span></label>
                                    <select name="delivery_type" class="form-select @error('delivery_type') is-invalid @enderror"
                                        required>
                                        <option value="delivery" {{ old('delivery_type', $shipping->delivery_type) == 'delivery' ? 'selected' : '' }}>
                                            {{ __('delivery') }}
                                        </option>
                                        <option value="pickup" {{ old('delivery_type', $shipping->delivery_type) == 'pickup' ? 'selected' : '' }}>
                                            {{ __('pickup') }}
                                        </option>
                                    </select>
                                    @error('delivery_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-6">
                                    <label class="col-form-label">{{ __('status') }} <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="1" {{ old('status', $shipping->status ? '1' : '0') == '1' ? 'selected' : '' }}>
                                            {{ __('active') }}
                                        </option>
                                        <option value="0" {{ old('status', $shipping->status ? '1' : '0') == '0' ? 'selected' : '' }}>
                                            {{ __('inactive') }}
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary px-5">
                                    <i class="fa fa-refresh me-2"></i>{{ __('update_shipping_fee') }}
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
