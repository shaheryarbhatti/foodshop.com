<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('edit_coupon') }}</title>

@extends('layouts.app')
@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card" style="margin-top: 20px;">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ __('edit_coupon') }}</h4>
                        <a href="{{ route('coupons.manage') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left me-2"></i>{{ __('back_to_list') }}</a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('coupons.update', $coupon) }}" class="theme-form">
                            @csrf
                            @method('PUT')
                            @include('coupons.partials.form')
                            <div class="text-end mt-4"><button type="submit" class="btn btn-primary px-5"><i class="fa fa-save me-2"></i>{{ __('update_coupon') }}</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
