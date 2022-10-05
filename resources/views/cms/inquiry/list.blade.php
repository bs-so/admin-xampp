@extends('layouts.afterlogin')

@section('title', trans('inquiry.title'))

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
                                    <label class="form-label">{{ trans('inquiry.table.title') }}</label>
                                    <input type="text" id="filter-title" class="form-control"
                                        placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('inquiry.table.content') }}</label>
                                    <input type="text" id="filter-content" class="form-control"
                                        placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('inquiry.table.email') }}</label>
                                    <input type="text" id="filter-email" class="form-control"
                                        placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('inquiry.table.status') }}</label>
                                    <select id="filter-status" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('InquiryStatusData') as $index => $status)
                                            <option value="{{ $index }}">{{ $status[0] }}</option>
                                        @endforeach
                                    </select>
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

        <?php
        echo '<script>
            ';
            echo 'var InquiryStatusData = '.json_encode(g_enum('InquiryStatusData')).
            ';';
            echo '

        </script>';
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
                        <table id="inquiry-list" class="table">
                            <thead>
                                <tr>
                                    <th>{{ trans('inquiry.table.no') }}</th>
                                    <th>{{ trans('inquiry.table.title') }}</th>
                                    <th>{{ trans('inquiry.table.content') }}</th>
                                    <th>{{ trans('inquiry.table.email') }}</th>
                                    <th>{{ trans('inquiry.table.status') }}</th>
                                    <th>{{ trans('inquiry.table.actions') }}</th>
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

    <!-- Add FAQ modal -->
    <div class="modal fade" id="modal-inquiry">
        <div class="modal-dialog">
            <form id="frm-inquiry" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span id="modal-title">{{ trans('inquiry.edit_title') }}</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="inquiry-id" value="0">
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('inquiry.table.title') }}</label>
                            <input type="text" id="title" class="form-control mr-sm-2 mb-2 mb-sm-0" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('inquiry.table.content') }}</label>
                            <textarea rows="5" id="content" class="form-control mr-sm-2 mb-2 mb-sm-0" readonly></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('inquiry.table.status') }}</label>
                            <select id="status" class="form-control mr-sm-2 mb-2 mb-sm-0">
                                @foreach (g_enum('InquiryStatusData') as $status => $data)
                                    <option value="{{ $status }}">{{ $data[0] }}</option>
                                @endforeach
                            </select>
                            <small id="status-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i
                            class="fa fa-remove"></i>&nbsp;{{ trans('ui.button.cancel') }}</button>
                    <button type="button" class="btn btn-success" id="btn-modal-submit"><i
                            class="fa fa-save"></i>&nbsp;{{ trans('ui.button.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- / Add FAQ modal -->
@endsection


@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ cAsset('js/inquiry-list.js') }}"></script>

    <script>
        function editInquiry(id) {
            $.ajax({
                url: BASE_URL + 'ajax/cms/inquiry/getInfo',
                type: 'POST',
                data: {
                    id: id,
                },
                success: function(result) {
                    $('#title').val(result['title']);
                    $('#content').val(result['content']);
                    $('#status').val(result['status']);
                    $('#inquiry-id').val(result['id']);

                    $('#modal-inquiry').modal('show');
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        $('#btn-modal-submit').on('click', function() {
            let id = $('#inquiry-id').val();
            let status = $('#status').val();

            $.ajax({
                url: BASE_URL + 'ajax/cms/inquiry/edit',
                type: 'POST',
                data: {
                    id: id,
                    status: status,
                },
                success: function(result) {
                    $('#modal-inquiry').modal('hide');
                    listTable.ajax.reload();
                },
                error: function(err) {
                    var errorMsg = err['responseJSON']['errors'];
                    $('#frm-inquiry').find('select').removeClass('is-invalid');
                    $('#frm-inquiry').find('input').removeClass('is-invalid');

                    if (errorMsg['status'] != null) {
                        $('#status').addClass('is-invalid');
                        document.getElementById('status-error').innerHTML = errorMsg['status'];
                    }
                }
            });
        });

        function initTable() {
            listTable = $('#inquiry-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/cms/inquiry/search',
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
                    targets: [5],
                    orderable: false,
                    searchable: false
                }],
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'title'
                    },
                    {
                        data: 'content'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: null
                    },
                ],
                createdRow: function(row, data, index) {
                    var pageInfo = listTable.page.info();

                    // *********************************************************************
                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(3).html('').append(
                        data['user_id'] == undefined ? data['email'] : (
                            '<a target="_blank" class="link" href="' + BASE_URL + 'users/detail?id=' + data[
                                'user_id'] + '">' + data['email'] + '</a>')
                    );

                    $('td', row).eq(4).html('').append(
                        '<span class="text-white badge-glow badge badge-' + InquiryStatusData[data[
                            'status']][1] + '">' + InquiryStatusData[data['status']][0] + '</span>'
                    );

                    $('td', row).eq(5).html('').append(
                        '<a class="btn btn-icon btn-icon-rounded-circle text-primary btn-flat-primary user-tooltip" href="javascript:editInquiry(' +
                        data["id"] + ');" title="' + '{{ trans('ui.button.edit') }}' + '">' +
                        '<i class="fa fa-edit"></i></a>'
                    );
                },
            });
        }

    </script>
@endsection
