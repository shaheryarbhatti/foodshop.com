<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('edit_category') }}</title>

    @extends('layouts.app')
    @section('content')
    <div class="page-body"><div class="container-fluid"><div class="row"><div class="col-12"><div class="card" style="margin-top: 20px;">
        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ __('edit_category') }}</h4>
            <a href="{{ route('categories.manage') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left me-2"></i> {{ __('back_to_list') }}</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('categories.update', $category->id) }}" class="theme-form">
                @csrf @method('PUT')
                <div class="row g-4">
                    <div class="col-lg-6"><label class="col-form-label" for="title">{{ __('category_title') }} <span class="text-danger">*</span></label><input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $category->title) }}" required autofocus>@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-lg-6"><label class="col-form-label" for="permalink">{{ __('category_permalink') }} <span class="text-danger">*</span></label><input type="text" name="permalink" id="permalink" class="form-control @error('permalink') is-invalid @enderror" value="{{ old('permalink', $category->permalink) }}" required>@error('permalink')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-lg-6"><label class="col-form-label" for="status">{{ __('status') }} <span class="text-danger">*</span></label><select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required><option value="1" {{ old('status', $category->status ? '1' : '0') == '1' ? 'selected' : '' }}>{{ __('active') }}</option><option value="0" {{ old('status', $category->status ? '1' : '0') == '0' ? 'selected' : '' }}>{{ __('inactive') }}</option></select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
                <div class="text-end mt-4"><button type="submit" class="btn btn-primary px-5"><i class="fa fa-refresh me-2"></i> {{ __('update_category') }}</button></div>
            </form>
        </div>
    </div></div></div></div></div>
    @endsection
