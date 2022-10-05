@extends('layouts.afterlogin')

@section('title', trans('users.title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
@endsection

@section('contents')
    <!-- users list start -->
    <section class="users-list-wrapper">
        <!-- users filter start -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ trans('ui.search.filters') }}</h4>
                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                <div class="heading-elements">
                    <ul class="list-inline mb-0">
                        <li><a data-action="collapse"><i class="feather icon-chevron-down"></i></a></li>
                        <li><a data-action="close"><i class="feather icon-x"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="card-content collapse show">
                <div class="card-body">
                    <div class="users-list-filter">
                        <form>
                            <div class="row">
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users.table.userid') }}</label>
                                    <input type="text" id="filter-userid" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users.table.nickname') }}</label>
                                    <input type="text" id="filter-nickname" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users.table.email') }}</label>
                                    <input type="text" id="filter-email" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users.table.kyc_status') }}</label>
                                    <select id="filter-auth-status" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('KycStatusData') as $index => $status)
                                            <option value="{{ $index }}">{{ $status[0] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users.table.status') }}</label>
                                    <select id="filter-status" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('UserStatusData') as $index => $status)
                                            <option value="{{ $index }}">{{ $status[0] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users.table.reged_at') }}</label>
                                    <input type="text" id="filter-date" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md col-xl-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" onclick="javascript:doSearch()" class="btn btn-primary btn-block">
                                        <i class="fa fa-search"></i>&nbsp;{{ trans('ui.button.search') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- users filter end -->

        <input type="hidden" id="edit-caption" value="{{ trans('ui.button.edit') }}">
        <input type="hidden" id="delete-caption" value="{{ trans('ui.button.delete') }}">
        <?php
            echo '<script>';
            echo 'var KycStatusData = ' . json_encode(g_enum('KycStatusData')) . ';';
            echo 'var UserStatusData = ' . json_encode(g_enum('UserStatusData')) . ';';
            echo '</script>';
        ?>

        @if ($message = Session::get('flash_message'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                {{ trans($message) }}
            </div>
        @endif
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="users-list" class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('users.table.no') }}</th>
                                <th>{{ trans('users.table.userid') }}</th>
                                <th>{{ trans('users.table.nickname') }}</th>
                                <th>{{ trans('users.table.email') }}</th>
                                <th>{{ trans('users.table.referrer') }}</th>
                                <th>{{ trans('users.table.kyc_status') }}</th>
                                <th>{{ trans('users.table.status') }}</th>
                                <th>{{ trans('users.table.reged_at') }}</th>
                                <th>{{ trans('users.table.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- users list ends -->
@endsection


@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ cAsset("js/users-list.js") }}"></script>

    <script>
        function deleteUser(id) {
            bootbox.confirm({
                message: "{!! nl2br(trans('users.message.delete_confirm')) !!}",
                buttons: {
                    cancel: {
                        className: 'btn btn-light',
                        label: '<i class="fa fa-times"></i> {{ trans('ui.button.cancel') }}'
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> {{ trans('ui.button.confirm') }}'
                    }
                },
                callback: function(result) {
                    if (result) {
                        $.ajax({
                            url: BASE_URL + 'ajax/users/delete',
                            type: 'POST',
                            data: {
                                'id': id,
                            },
                            success: function(result) {
                                if (result < 0) {
                                    bootbox.alert("{{ trans('ui.alert.delete_failed') }}");
                                } else if (result == 0) {
                                    bootbox.alert("{{ trans('ui.alert.delete_admin') }}");
                                } else if (result == 1) {
                                    listTable.ajax.reload();
                                }
                            },
                            error: function(err) {
                                bootbox.alert("{{ trans('ui.alert.delete_failed') }}");
                                console.log(err);
                            }
                        });
                    }
                }
            });
        }

        function updateStatus(id, status) {
            $.ajax({
                url: BASE_URL + 'ajax/users/updateStatus',
                type: 'POST',
                data: {
                    id: id,
                    status: status,
                },
                success: function() {
                    listTable.ajax.reload();
                },
                error: function() {
                    console.log(err);
                }
            });
        }

        function register(id) {
            $.ajax({
                url: BASE_URL + 'ajax/users/register',
                type: 'POST',
                data: {
                    id: id,
                },
                success: function(result) {
                    if (result == '{{ CASINO_REGISTER_SUCCESS }}') {
                        listTable.ajax.reload();
                        showToast('{{ trans('users.message.register_success') }}', '{{ trans('ui.alert.info') }}', 'success');
                    }
                    else if (result == '{{ CASINO_REGISTER_EXIST }}') {
                        showToast('{{ trans('users.message.register_exist') }}', '{{ trans('ui.alert.info') }}', 'warning');
                    }
                    else {
                        showToast('{{ trans('users.message.register_failed') }}', '{{ trans('ui.alert.info') }}', 'warning');
                    }
                },
                error: function() {
                    console.log(err);
                }
            });
        }

        function initTable() {
            listTable = $('#users-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/users/search',
                    type: 'POST',
                },
                language: {
                    paginate: {
                        previous: '&nbsp;',
                        next: '&nbsp;',
                    },
                    sLengthMenu: "{{ trans('ui.table.sLengthMenu') }}",
                    zeroRecords: "{{ trans('ui.table.zeroRecords') }}",
                    info: "{{ trans('ui.table.info') }}",
                    infoEmpty: "{{ trans('ui.table.infoEmpty') }}",
                    infoFiltered: "{{ trans('ui.table.infoFiltered') }}",
                },
                lengthMenu: [
                    [10, 25, 50, 100, 1000, 2500, -1],
                    [10, 25, 50, 100, 1000, 2500, "{{ trans('ui.table.all') }}"]
                ],
                columnDefs: [{
                    targets: [8],
                    orderable: false,
                    searchable: false
                }],
                columns: [
                    {data: 'id'},
                    {data: 'userid'},
                    {data: 'nickname'},
                    {data: 'email'},
					{data: 'referrer'},
                    {data: 'kyc_status'},
                    {data: 'status'},
                    {data: 'created_at'},
                    {data: null},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = listTable.page.info();

                    // *********************************************************************
                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(5).html('').append(
                        '<span class="text-white badge-glow badge badge-' + KycStatusData[data['kyc_status']][1] + '">' + KycStatusData[data['kyc_status']][0] + '</span>'
                    );

                    $('td', row).eq(6).html('').append(
                        '<span class="text-white badge-glow badge badge-' + UserStatusData[data['status']][1] + '">' + UserStatusData[data['status']][0] + '</span>'
                    );

                    let statusHtml = '';
                    if (data['status'] == '{{ STATUS_ACTIVE }}') {
                        statusHtml = '<a class="btn btn-icon btn-icon-rounded-circle text-primary btn-flat-primary user-tooltip" href="javascript:updateStatus(' + data['id'] + ', ' + '{{ STATUS_BANNED }}' + ')" title="' + '{{ trans('ui.button.disable') }}' + '">'
                            + '<i class="fa fa-ban"></i></a>';
                    }
                    else if (data['status'] == '{{ STATUS_REGISTER_FAILED }}') {
                        statusHtml = '<a class="btn btn-icon btn-icon-rounded-circle text-danger btn-flat-danger user-tooltip" href="javascript:register(' + data['id'] + ')" title="' + '{{ trans('ui.button.register') }}' + '">'
                            + '<i class="fa fa-gg"></i></a>';
                    }
                    else {
                        statusHtml = '<a class="btn btn-icon btn-icon-rounded-circle text-primary btn-flat-primary user-tooltip" href="javascript:updateStatus(' + data['id'] + ', ' + '{{ STATUS_ACTIVE }}' + ')" title="' + '{{ trans('ui.button.enable') }}' + '">'
                            + '<i class="fa fa-check"></i></a>';
                    }
                    $('td', row).eq(8).html('').append(
                        statusHtml +
                        '<a class="btn btn-icon btn-icon-rounded-circle text-info btn-flat-info user-tooltip" href="' +
                        BASE_URL + 'users/detail?id=' + data["id"] + '" title="' +
                        '{{ trans('ui.button.detail') }}' + '">' +
                        '<i class="fa fa-edit"></i></a>' +
                        '<span class="btn btn-icon btn-icon-rounded-circle text-warning btn-flat-warning user-tooltip item-trash" data-id="' +
                        data["id"] + '" title="' +
                        '{{ trans('ui.button.delete') }}' + '">' +
                        '<i class="fa fa-trash"></i></span>'
                    );
                },
            });
        }

        $(document).on('click',
            'span.btn.btn-icon.btn-icon-rounded-circle.text-warning.btn-flat-warning.user-tooltip.item-trash',
            function() {
                id = $(this).data('id');
                /*$('#user-id').val(id);
                $('#modal-delete').modal("show");*/
                deleteUser(id);
            });

    </script>
@endsection
