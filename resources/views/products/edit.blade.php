<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('edit_product') }}</title>

@extends('layouts.app')
@section('content')
<style>
    .js-multi-toggle {
        height: auto !important;
        overflow-y: auto;
    }
    .js-multi-toggle option {
        padding: 8px 12px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
    }
    .js-multi-toggle option:last-child {
        border-bottom: none;
    }
    .js-multi-toggle option:checked {
        background-color: var(--theme-default) !important;
        color: #ffffff !important;
    }
</style>
<div class="page-body"><div class="container-fluid"><div class="row"><div class="col-12"><div class="card" style="margin-top:20px;"><div class="card-header pb-0 d-flex justify-content-between align-items-center"><h4 class="mb-0">{{ __('edit_product') }}</h4><a href="{{ route('products.manage') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left me-2"></i>{{ __('back_to_list') }}</a></div><div class="card-body"><form method="POST" enctype="multipart/form-data" action="{{ route('products.update', $product->id) }}" class="theme-form">@csrf @method('PUT')<div class="row g-4"><div class="col-lg-6"><label class="col-form-label">{{ __('serial_number') }} <span class="text-danger">*</span></label><input type="number" min="1" name="serial_number" class="form-control @error('serial_number') is-invalid @enderror" value="{{ old('serial_number', $product->serial_number) }}" required>@error('serial_number')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-lg-6"><label class="col-form-label">{{ __('category_title') }} <span class="text-danger">*</span></label><select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required><option value="">{{ __('select_category') }}</option>@foreach($categories as $category)<option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->title }}</option>@endforeach</select>@error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-lg-6"><label class="col-form-label">{{ __('product_title') }} <span class="text-danger">*</span></label><input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $product->title) }}" required>@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-lg-6"><label class="col-form-label">{{ __('product_permalink') }}</label><input type="text" name="permalink" class="form-control @error('permalink') is-invalid @enderror" value="{{ old('permalink', $product->permalink) }}">@error('permalink')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-lg-6"><label class="col-form-label">{{ __('base_price') }} <span class="text-danger">*</span></label><input type="number" step="0.01" min="0" name="base_price" class="form-control @error('base_price') is-invalid @enderror" value="{{ old('base_price', $product->base_price) }}" required>@error('base_price')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-lg-6"><label class="col-form-label">{{ __('product_image') }}</label><input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp">@error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror @if($product->image)<div class="mt-2"><img src="{{ asset('public/' . $product->image) }}" style="width:90px;height:90px;object-fit:cover;border-radius:12px;border:1px solid #ddd;"></div>@endif</div><div class="col-lg-6"><label class="col-form-label">{{ __('status') }} <span class="text-danger">*</span></label><select name="status" class="form-select @error('status') is-invalid @enderror" required><option value="1" {{ old('status', $product->status) == '1' ? 'selected' : '' }}>{{ __('active') }}</option><option value="0" {{ old('status', $product->status) == '0' ? 'selected' : '' }}>{{ __('inactive') }}</option></select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-lg-6"><label class="col-form-label">{{ __('allergy_title') }}</label><select name="allergy_ids[]" class="form-select js-multi-toggle @error('allergy_ids') is-invalid @enderror" multiple size="8">@foreach($allergies as $allergy)<option value="{{ $allergy->id }}" {{ collect(old('allergy_ids', $product->allergies->pluck('id')))->contains($allergy->id) ? 'selected' : '' }}>{{ $allergy->title }} ({{ $allergy->code }})</option>@endforeach</select>@error('allergy_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-12"><label class="col-form-label">{{ __('product_description') }}</label><textarea id="product-description-editor" name="description" rows="6" class="form-control ckeditor-field @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>@error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div></div><div class="text-end mt-4"><button type="submit" class="btn btn-primary px-5"><i class="fa fa-refresh me-2"></i>{{ __('update_product') }}</button></div></form></div></div></div></div></div></div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.ClassicEditor) {
            return;
        }

        document.querySelectorAll('.ckeditor-field').forEach(function (element) {
            ClassicEditor
                .create(element)
                .then(function (editor) {
                    element.ckeditorInstance = editor;
                    editor.ui.view.editable.element.style.minHeight = '280px';
                })
                .catch(function (error) {
                    console.error(error);
                });
        });

        $('.js-multi-toggle option').on('mousedown', function (e) {
            e.preventDefault();
            var select = $(this).parent();
            var scrollTop = select.scrollTop();
            $(this).prop('selected', !$(this).prop('selected'));
            setTimeout(function() { 
                select.scrollTop(scrollTop); 
                select.focus();
            }, 0);
            select.trigger('change');
        }).on('mousemove', function(e) { e.preventDefault(); });

        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                document.querySelectorAll('.ckeditor-field').forEach(function (element) {
                    if (element.ckeditorInstance) {
                        element.value = element.ckeditorInstance.getData();
                    }
                });
            });
        });
    });
</script>
@endpush
