@extends('layouts.afterlogin')

@section('title', trans('staff.title'))

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
                                    <label class="form-label">{{ trans('staff.table.login_id') }}</label>
                                    <input type="text" id="filter-login_id" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('staff.table.name') }}</label>
                                    <input type="text" id="filter-name" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('staff.table.role') }}</label>
                                    <select id="filter-role" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('UserRoleData') as $index => $role)
                                            <option value="{{ $index }}">{{ $role[0] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('staff.table.status') }}</label>
                                    <select id="filter-status" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('StatusData') as $index => $status)
                                            <option value="{{ $index }}">{{ $status[0] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('staff.table.reged_at') }}</label>
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
            echo 'var UserRoleData = ' . json_encode(g_enum('UserRoleData')) . ';';
            echo 'var StatusData = ' . json_encode(g_enum('StatusData')) . ';';
            echo '</script>';
        ?>

        @if ($message = Session::get('flash_message'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                {{ trans($message) }}
            </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="form-group">
                    <a type="button" class="text-white btn btn-primary" href="{{ route('staff.add') }}">
                        <i class="fa fa-plus"></i>&nbsp;{{ trans('ui.button.add') }}
                    </a>
                    <a type="button" class="text-white btn btn-success" href="{{ route('staff.download') }}">
                        <i class="fa fa-download"></i>&nbsp;{{ trans('ui.button.download') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="staff-list" class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('staff.table.no') }}</th>
                                <th>{{ trans('staff.table.login_id') }}</th>
                                <th>{{ trans('staff.table.name') }}</th>
                                <th>{{ trans('staff.table.email') }}</th>
                                <th>{{ trans('staff.table.role') }}</th>
                                <th>{{ trans('staff.table.status') }}</th>
                                <th>{{ trans('staff.table.reged_at') }}</th>
                                <th>{{ trans('staff.table.actions') }}</th>
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
    <script src="{{ cAsset("js/staff-list.js") }}"></script>

    <script>
        function deleteStaff(id) {
            bootbox.confirm({
                message: "{{ trans('ui.alert.ask_delete') }}",
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
                            url: BASE_URL + 'ajax/staff/delete',
                            type: 'POST',
                            data: {
                                'id': id,
                            },
                            success: function(result) {
                                if (result < 0) {
                                    bootbox.alert("{{ trans('ui.alert.delete_failed') }}");
                                }
                                else if (result == 0) {
                                    bootbox.alert("{{ trans('ui.alert.delete_admin') }}");
                                }
                                else if (result == 1) {
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

        function initTable() {
            listTable = $('#staff-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/staff/search',
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
                lengthMenu: [[10, 25, 50, 100, 1000, 2500, -1], [10, 25, 50, 100, 1000, 2500, "{{ trans('ui.table.all') }}"]],
                columnDefs: [{
                    targets: [7],
                    orderable: false,
                    searchable: false
                }],
                columns: [
                    {data: 'id'},
                    {data: 'login_id'},
                    {data: 'name'},
                    {data: 'email'},
                    {data: 'role'},
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

                    $('td', row).eq(4).html('').append(
                        '<span class="text-white badge-glow badge badge-' + UserRoleData[data['role']][1] + '">' + UserRoleData[data['role']][0] + '</span>'
                    );

                    $('td', row).eq(5).html('').append(
                        '<span class="text-white badge-glow badge badge-' + StatusData[data['status']][1] + '">' + StatusData[data['status']][0] + '</span>'
                    );

                    $('td', row).eq(7).html('').append(
                        '<a class="btn btn-icon btn-icon-rounded-circle text-primary btn-flat-primary user-tooltip" href="' + BASE_URL + 'staff/edit?id=' +  data["id"] + '" title="' + $('#edit-caption').val() + '">'
                        + '<i class="fa fa-edit"></i></a>' +
                        '<a class="btn btn-icon btn-icon-rounded-circle text-danger btn-flat-danger user-tooltip" onclick="deleteStaff(' +  data["id"] + ')" title="' + $('#delete-caption').val() +'">'
                        + '<i class="fa fa-remove"></i></a>'
                    );
                },
            });
        }
    </script>
@endsection
