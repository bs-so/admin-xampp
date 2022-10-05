@extends('layouts.afterlogin')

@section('title', trans('requests.kyc.title'))

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
                                    <label class="form-label">{{ trans('requests.kyc.userid') }}</label>
                                    <input type="text" id="filter-userid" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('requests.kyc.nickname') }}</label>
                                    <input type="text" id="filter-nickname" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('requests.kyc.email') }}</label>
                                    <input type="text" id="filter-email" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('requests.kyc.status') }}</label>
                                    <select id="filter-kyc" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('KycStatusData') as $index => $status)
                                            <option value="{{ $index }}">{{ $status[0] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('requests.kyc.reged_at') }}</label>
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
            echo 'var UserStatusData = ' . json_encode(g_enum('UserStatusData')) . ';';
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
                    <a type="button" class="text-white btn btn-success" href="{{ route('requests.kyc.csv') }}?type=0">
                        <i class="fa fa-download"></i>&nbsp;{{ trans('ui.button.download') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="kyc-list" class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('requests.kyc.no') }}</th>
                                <th>{{ trans('requests.kyc.userid') }}</th>
                                <th>{{ trans('requests.kyc.nickname') }}</th>
                                <th>{{ trans('requests.kyc.email') }}</th>
                                <th>{{ trans('requests.kyc.gender') }}</th>
                                <th>{{ trans('requests.kyc.mobile') }}</th>
                                <th>{{ trans('requests.kyc.status') }}</th>
                                <th>{{ trans('requests.kyc.reged_at') }}</th>
                                <th>{{ trans('requests.kyc.actions') }}</th>
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

    <!-- Add wallet modal -->
    <div class="modal fade" id="modal-download">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <form id="frm-download" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ trans('requests.kyc.download_title') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="download-list" class="table">
                                    <thead>
                                    <th>{{ trans('requests.kyc.no') }}</th>
                                    <th>{{ trans('requests.kyc.identity') }}</th>
                                    <th>{{ trans('requests.kyc.filesize') }}</th>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i class="fa fa-remove"></i>&nbsp;{{ trans('ui.button.cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- / Add wallet modal -->
@endsection


@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ cAsset("js/requests-kyc.js") }}"></script>

    <?php echo('<script>let UserGenderData = ' . json_encode(g_enum('UserGenderData')) . ';</script>') ?>
    <?php echo('<script>let KycStatusData = ' . json_encode(g_enum('KycStatusData')) . ';</script>') ?>
    <script>
        function updateKycStatus(id, status) {
            $.ajax({
                url: BASE_URL + 'ajax/requests/kyc/updateStatus',
                type: 'POST',
                data: {
                    id: id,
                    status: status,
                },
                success: function(result) {
                    listTable.ajax.reload();
                    showToast('{{ trans('requests.kyc.op_success') }}', '{{ trans('ui.alert.info') }}', 'success');
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        function initTable() {
            listTable = $('#kyc-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/requests/kyc/search',
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
                    targets: [8],
                    orderable: false,
                    searchable: false
                }],
                columns: [
                    {data: 'id'},
                    {data: 'userid'},
                    {data: 'nickname'},
                    {data: 'email'},
                    {data: 'gender'},
                    {data: 'mobile'},
                    {data: 'kyc_status'},
                    {data: 'created_at'},
                    {data: null, className: 'text-center'},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = listTable.page.info();

                    // *********************************************************************
                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(4).html('').append(
                        '<span class="text-white badge-glow badge badge-' + UserGenderData[data['gender']][1] + '">' + UserGenderData[data['gender']][0] + '</span>'
                    );
                    $('td', row).eq(6).html('').append(
                        '<span class="text-white badge-glow badge badge-' + KycStatusData[data['kyc_status']][1] + '">' + KycStatusData[data['kyc_status']][0] + '</span>'
                    );

                    $('td', row).eq(8).html('').append(
                        '<a class="btn btn-icon btn-icon-rounded-circle text-primary btn-flat-primary user-tooltip" href="javascript:showDownloadModal(' + data["id"] + ');" title="' + '{{ trans('ui.button.download') }}' + '">'
                        + '<i class="fa fa-download"></i></a>' +
                        ((data['kyc_status'] == '{{ KYC_STATUS_ACTIVE }}') ? '' :
                            '<a class="btn btn-icon btn-icon-rounded-circle text-success btn-flat-success user-tooltip" href="javascript:updateKycStatus(' + data["id"] + ',' + '{{ KYC_STATUS_ACTIVE }}' + ');" title="' + '{{ trans('ui.button.enable') }}' + '">'
                            + '<i class="fa fa-check"></i></a>') +
                        '<a class="btn btn-icon btn-icon-rounded-circle text-danger btn-flat-danger user-tooltip" href="javascript:updateKycStatus(' +  data["id"] + ',' + '{{ KYC_STATUS_BANNED }}' + ')" title="' + '{{ trans('ui.button.disable') }}' +'">'
                        + '<i class="fa fa-remove"></i></a>'
                    );
                    $('td', row).eq(8).css('min-width', '150px');
                },
            });
        }

        function showDownloadModal(user_id) {
            if (downloadTable != null) {
                downloadTable.destroy();
            }
            downloadTable = $('#download-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/requests/kyc/getIdentityList',
                    type: 'POST',
                    data: {
                        user_id: user_id,
                    }
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
                    targets: [2],
                    orderable: false,
                    searchable: false
                }],
                columns: [
                    {data: 'id'},
                    {data: 'photo_url'},
                    {data: 'filesize'},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = downloadTable.page.info();

                    // *********************************************************************
                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    let names = data['photo_url'].split('/');
                    $('td', row).eq(1).html('').append(
                        '<a href="' + BASE_URL + 'requests/kyc/download?id=' + data['id'] + '">' + names[names.length - 1] + '</a>'
                    );
                    $('td', row).eq(2).html('').append(
                        BigNumber(data['filesize'] / 1024 / 1024).toFixed(2) + ' MB'
                    );
                },
                initComplete: function() {
                    $('#modal-download').modal('show');
                }
            });
        }
    </script>
@endsection
