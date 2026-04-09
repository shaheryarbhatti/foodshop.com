<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('customer_details') }}</title>

@extends('layouts.app')
@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card" style="margin-top:20px;">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ __('customer_details') }}</h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit me-2"></i>{{ __('edit_customer') }}</a>
                            <a href="{{ route('customers.manage') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left me-2"></i>{{ __('back_to_list') }}</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6"><label class="fw-bold d-block mb-2">{{ __('customer_name') }}</label><div>{{ $customer->full_name }}</div></div>
                            <div class="col-md-6"><label class="fw-bold d-block mb-2">{{ __('customer_type') }}</label><div>{{ $customer->customer_type === 'company' ? __('company') : __('individual') }}</div></div>
                            <div class="col-md-6"><label class="fw-bold d-block mb-2">{{ __('company_name') }}</label><div>{{ $customer->company_name ?: '-' }}</div></div>
                            <div class="col-md-6"><label class="fw-bold d-block mb-2">{{ __('email') }}</label><div>{{ $customer->email }}</div></div>
                            <div class="col-md-6"><label class="fw-bold d-block mb-2">{{ __('billing_phone') }}</label><div>{{ $customer->phone }}</div></div>
                            <div class="col-md-6"><label class="fw-bold d-block mb-2">{{ __('address') }}</label><div>{{ $customer->address }}</div></div>
                            <div class="col-md-6"><label class="fw-bold d-block mb-2">{{ __('city') }}</label><div>{{ $customer->city }}</div></div>
                            <div class="col-md-6"><label class="fw-bold d-block mb-2">{{ __('billing_postal_code') }}</label><div>{{ $customer->postal_code }}</div></div>
                            <div class="col-md-6"><label class="fw-bold d-block mb-2">{{ __('country') }}</label><div>{{ $customer->country }}</div></div>
                            <div class="col-md-6"><label class="fw-bold d-block mb-2">{{ __('status') }}</label><div>{!! $customer->status ? '<span class="badge rounded-pill bg-success">' . __('active') . '</span>' : '<span class="badge rounded-pill bg-secondary">' . __('inactive') . '</span>' !!}</div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
