<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('add_new_addon') }}</title>

@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('public/assets/css/vendors/select2.css') }}">
<style>
.select2-container--default .select2-selection--multiple {
    border-color: #dee2e6;
    border-radius: 10px;
    padding: 4px 8px;
}
.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: var(--theme-default);
    box-shadow: 0 0 0 0.25rem rgba(115, 103, 240, 0.1);
}
</style>
@endpush
@section('content')
<div class="page-body"><div class="container-fluid"><div class="row"><div class="col-12"><div class="card" style="margin-top:20px;"><div class="card-header pb-0 d-flex justify-content-between align-items-center"><h4 class="mb-0">{{ __('add_new_addon') }}</h4><a href="{{ route('addons.manage') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left me-2"></i>{{ __('back_to_list') }}</a></div><div class="card-body"><form method="POST" action="{{ route('addons.store') }}" class="theme-form">@csrf<div class="row g-4"><div class="col-lg-6"><label class="col-form-label">{{ __('product_title') }} <span class="text-danger">*</span></label><select name="product_ids[]" class="form-select @error('product_ids') is-invalid @enderror" id="product_ids" multiple="multiple" required>@foreach($products as $product)<option value="{{ $product->id }}" {{ is_array(old('product_ids')) && in_array($product->id, old('product_ids')) ? 'selected' : '' }}>{{ $product->title }}</option>@endforeach</select>@error('product_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-lg-6"><label class="col-form-label">{{ __('addon_title') }} <span class="text-danger">*</span></label><input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-lg-6"><label class="col-form-label">{{ __('selection_type') }} <span class="text-danger">*</span></label><select name="selection_type" class="form-select @error('selection_type') is-invalid @enderror" required><option value="single" {{ old('selection_type','single') == 'single' ? 'selected' : '' }}>{{ __('single_select') }}</option><option value="multiple" {{ old('selection_type') == 'multiple' ? 'selected' : '' }}>{{ __('multiple_select') }}</option><option value="listing" {{ old('selection_type') == 'listing' ? 'selected' : '' }}>{{ __('listing_select') }}</option></select>@error('selection_type')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-lg-6"><label class="col-form-label">{{ __('status') }} <span class="text-danger">*</span></label><select name="status" class="form-select @error('status') is-invalid @enderror" required><option value="1" {{ old('status','1') == '1' ? 'selected' : '' }}>{{ __('active') }}</option><option value="0" {{ old('status') == '0' ? 'selected' : '' }}>{{ __('inactive') }}</option></select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div><hr><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">{{ __('addon_values') }}</h5><button class="btn btn-outline-primary btn-sm" type="button" id="addAddonValueRow">{{ __('add_more_value') }}</button></div><div id="addonValueRows"><div class="row g-3 addon-value-row mb-2"><div class="col-lg-5"><input type="text" name="value_titles[]" class="form-control" placeholder="{{ __('addon_value_title') }}" required></div><div class="col-lg-3"><input type="number" step="0.01" min="0" name="value_prices[]" class="form-control" placeholder="{{ __('addon_value_price_optional') }}"></div><div class="col-lg-3"><select name="value_statuses[]" class="form-select"><option value="1">{{ __('active') }}</option><option value="0">{{ __('inactive') }}</option></select></div><div class="col-lg-1"><button type="button" class="btn btn-outline-danger w-100 remove-addon-row">x</button></div></div></div><div class="text-end mt-4"><button type="submit" class="btn btn-primary px-5"><i class="fa fa-save me-2"></i>{{ __('save_addon') }}</button></div></form></div></div></div></div></div></div>
@endsection
@push('scripts')
<script src="{{ asset('public/assets/js/select2/select2.full.min.js') }}"></script>
<script>
$(function() {
    $('#product_ids').select2({
        placeholder: "{{ __('select_products') }}",
        width: '100%',
        closeOnSelect: false
    });

    document.addEventListener('click',function(e){if(e.target.id==='addAddonValueRow'){const wrap=document.getElementById('addonValueRows');const row=document.createElement('div');row.className='row g-3 addon-value-row mb-2';row.innerHTML='<div class="col-lg-5"><input type="text" name="value_titles[]" class="form-control" placeholder="{{ __('addon_value_title') }}" required></div><div class="col-lg-3"><input type="number" step="0.01" min="0" name="value_prices[]" class="form-control" placeholder="{{ __('addon_value_price_optional') }}"></div><div class="col-lg-3"><select name="value_statuses[]" class="form-select"><option value="1">{{ __('active') }}</option><option value="0">{{ __('inactive') }}</option></select></div><div class="col-lg-1"><button type="button" class="btn btn-outline-danger w-100 remove-addon-row">x</button></div>';wrap.appendChild(row);} if(e.target.classList.contains('remove-addon-row')){const rows=document.querySelectorAll('.addon-value-row'); if(rows.length>1){ e.target.closest('.addon-value-row').remove(); }}});
});
</script>@endpush
