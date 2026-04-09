<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('manage_categories') }}</title>

    @extends('layouts.app')
    @section('content')
    @php
        $user = auth()->user();
        $legacyBase = 'manage-' . \Illuminate\Support\Str::singular('categories');
        $canCategoryAdd = $user && $user->canAny(['categories.add', $legacyBase . '.add']);
    @endphp

    <div class="page-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card" style="margin-top: 20px;">
                        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">{{ __('manage_categories') }}</h4>
                            @if($canCategoryAdd)
                                <a href="{{ route('categories.add') }}" class="btn btn-primary">
                                    <i class="fa fa-plus me-2"></i> {{ __('add_new_category') }}
                                </a>
                            @endif
                        </div>

                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <div class="table-responsive custom-scrollbar mb-4">
                                <table id="categoriesTable" class="display table table-hover table-bordered" style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('category_title') }}</th>
                                            <th>{{ __('category_permalink') }}</th>
                                            <th>{{ __('status') }}</th>
                                            <th>{{ __('action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endsection

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
        <style>
        div.dataTables_wrapper div.dataTables_filter {
            position: absolute;
            right: 53px;
            top: 0px;
        }

        .dataTables_paginate .pagination {
            margin: 0 auto !important;
            justify-content: center;
            gap: 0.2rem;
            padding: 0.25rem 0;
        }

        .dataTables_paginate .page-item .page-link {
            border-radius: 8px !important;
            width: 30px;
            height: 30px;
            line-height: 30px;
            text-align: center;
            padding: 0;
            margin: 0 1px;
            border: 1px solid #e2e6ea;
            color: #5a6470;
            background-color: #fff;
            transition: all 0.15s ease;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .dataTables_paginate .page-item .page-link:hover {
            background-color: var(--theme-default, #7367f0) !important;
            color: white !important;
            border-color: var(--theme-default, #7367f0) !important;
        }

        .dataTables_paginate .page-item.active .page-link {
            background-color: var(--theme-default, #7367f0) !important;
            border-color: var(--theme-default, #7367f0) !important;
            color: white !important;
            box-shadow: 0 3px 10px rgba(115, 103, 240, 0.25);
            font-weight: 700;
        }

        .dataTables_paginate .page-item.disabled .page-link {
            color: #b7c0c8 !important;
            background-color: #f6f7f9 !important;
            border-color: #e2e6ea !important;
            cursor: not-allowed;
        }

        .dataTables_paginate .page-item:first-child .page-link,
        .dataTables_paginate .page-item:last-child .page-link {
            border-radius: 10px !important;
            width: auto;
            padding: 0 0.6rem;
            min-width: 44px;
            font-size: 0.8rem;
        }

        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 2.5rem !important;
        }

        .dataTables_wrapper .dataTables_paginate {
            margin-top: 1rem !important;
        }

        @media (max-width: 576px) {
            .dataTables_paginate .page-link {
                width: 28px;
                height: 28px;
                line-height: 28px;
                font-size: 0.8rem;
            }
        }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#categoriesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('categories.manage') }}",
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'title', name: 'title' },
                        { data: 'permalink', name: 'permalink' },
                        { data: 'status_badge', name: 'status' },
                        { data: 'action', name: 'action', orderable: false, searchable: false }
                    ],
                    pageLength: 10,
                    lengthMenu: [10, 25, 50, 100],
                    order: [[1, 'asc']],
                    language: {
                        processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                    },
                    responsive: true,
                    dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>tip'
                });
            });
        </script>
    @endpush
