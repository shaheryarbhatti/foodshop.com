@extends('layouts.app')

@section('title', __('manage_allergies'))

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card" style="margin-top:20px;">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ __('manage_allergies') }}</h4>
                        <div class="d-flex gap-2">
                            <form action="{{ route('allergies.destroyAll') }}" method="POST" class="bulk-delete-form js-confirm-delete" data-text="{{ __('delete_selected_records_confirm') }}">
                                @csrf
                                @method('DELETE')
                                <div class="selected-ids-wrapper"></div>
                                <button type="submit" class="btn btn-danger">
                                    <i class="fa fa-trash me-2"></i>{{ __('delete_all') }}
                                </button>
                            </form>
                            <a href="{{ route('allergies.add') }}" class="btn btn-primary">
                                <i class="fa fa-plus me-2"></i>{{ __('add_new_allergy') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif

                        <div class="table-responsive">
                            <table id="allergiesTable" class="display table table-hover table-bordered" style="width:100%">
                                <thead class="table-light">
                                    <tr>
                                        <th><input type="checkbox" class="form-check-input select-all-checkbox"></th>
                                        <th>#</th>
                                        <th>{{ __('allergy_title') }}</th>
                                        <th>{{ __('allergy_code') }}</th>
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
div.dataTables_wrapper div.dataTables_filter { position: absolute; right: 53px; top: 0px; }
.dataTables_paginate .pagination { margin: 0 auto !important; justify-content: center; gap: 0.2rem; padding: 0.25rem 0; }
.dataTables_paginate .page-item .page-link { border-radius: 8px !important; width: 30px; height: 30px; line-height: 30px; text-align: center; padding: 0; margin: 0 1px; border: 1px solid #e2e6ea; color: #5a6470; background-color: #fff; transition: all 0.15s ease; font-weight: 600; font-size: 0.85rem; }
.dataTables_paginate .page-item .page-link:hover { background-color: var(--theme-default, #7367f0) !important; color: white !important; border-color: var(--theme-default, #7367f0) !important; }
.dataTables_paginate .page-item.active .page-link { background-color: var(--theme-default, #7367f0) !important; border-color: var(--theme-default, #7367f0) !important; color: white !important; box-shadow: 0 3px 10px rgba(115, 103, 240, 0.25); font-weight: 700; }
.dataTables_wrapper .dataTables_filter { margin-bottom: 2.5rem !important; }
.dataTables_wrapper .dataTables_paginate { margin-top: 1rem !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
$(function() {
    var table = $('#allergiesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('allergies.manage') }}',
        columns: [
            {data: 'bulk_select', name: 'bulk_select', orderable: false, searchable: false},
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'title', name: 'title'},
            {data: 'code', name: 'code'},
            {data: 'status_badge', name: 'status_badge', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        order: [[2, 'asc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "{{ __('search') }}...",
            paginate: {
                previous: '<i class="fa fa-chevron-left"></i>',
                next: '<i class="fa fa-chevron-right"></i>'
            }
        }
    });

    $('.select-all-checkbox').on('click', function() {
        $('.row-checkbox').prop('checked', this.checked);
    });

    $(document).on('change', '.row-checkbox', function() {
        if (!this.checked) {
            $('.select-all-checkbox').prop('checked', false);
        }
    });

    $('.bulk-delete-form').on('submit', function(e) {
        e.preventDefault();
        var ids = $('.row-checkbox:checked').map(function() { return $(this).val(); }).get();
        if (ids.length === 0) {
            alert("{{ __('select_records_to_delete') }}");
            return;
        }

        if (confirm($(this).data('text'))) {
            $.ajax({
                url: '{{ route('allergies.destroyAll') }}',
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}', ids: ids },
                success: function() { table.draw(); $('.select-all-checkbox').prop('checked', false); }
            });
        }
    });
});
</script>
@endpush
