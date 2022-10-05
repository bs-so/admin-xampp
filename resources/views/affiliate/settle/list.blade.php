@extends('layouts.afterlogin')

@section('title', trans('affiliate.settle.list_title'))

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
            </div>
            <div class="card-content collapse show">
                <div class="card-body">
                    <div class="users-list-filter">
                        <form>
                            <div class="row">
                                <div class="col-md">
                                    <label class="form-label">{{ trans('affiliate.settle.use_announce') }}</label>
                                    <select class="form-control" id="filter-use_announce">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('MailAnnounceData') as $status => $data)
                                            <option value="{{ $status }}">{{ $data[0] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('affiliate.settle.status') }}</label>
                                    <select id="filter-status" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('AffiliateSettleStatusData') as $status => $data)
                                            <option value="{{ $status }}">{{ $data[0] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('affiliate.settle.settled_at') }}</label>
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
        echo 'var StaffDepositStatus = ' . json_encode(g_enum('StaffDepositStatus')) . ';';
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
                    <a type="button" class="text-white btn btn-primary" href="{{ route('affiliate.settle.add') }}">
                        <i class="fa fa-plus"></i>&nbsp;{{ trans('ui.button.add') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="settle-list" class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('affiliate.settle.no') }}</th>
                                <th>{{ trans('affiliate.settle.operator') }}</th>
                                <th>{{ trans('affiliate.settle.remark') }}</th>
                                <th>{{ trans('affiliate.settle.use_announce') }}</th>
                                <th>{{ trans('affiliate.settle.announce_status') }}</th>
                                <th>{{ trans('affiliate.settle.status') }}</th>
                                <th>{{ trans('affiliate.settle.settled_at') }}</th>
                                <th>{{ trans('affiliate.settle.actions') }}</th>
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
    <script src="{{ cAsset("js/affiliate_settle-list.js") }}"></script>

    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <?php echo('<script>let MailAnnounceData = ' . json_encode(g_enum('MailAnnounceData')) . ';</script>') ?>
    <?php echo('<script>let AffiliateSettleStatusData = ' . json_encode(g_enum('AffiliateSettleStatusData')) . ';</script>') ?>
    <script>
        function initTable() {
            listTable = $('#settle-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/affiliate/settle/search',
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
                    targets: [4, 7],
                    orderable: false,
                    searchable: false
                }],
                order: [6, 'desc'],
                columns: [
                    {data: 'id'},
                    {data: 'operator'},
                    {data: 'remark'},
                    {data: 'use_announce'},
                    {data: null},
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

                    $('td', row).eq(3).html('').append(
                        '<span class="text-white badge badge-glow badge-' + MailAnnounceData[data['use_announce']][1] + '">' + MailAnnounceData[data['use_announce']][0] + '</span>'
                    );
                    $('td', row).eq(4).html('').append(
                        data['announce_count'] + '(' + data['announce_sent'] + ' / ' + data['announce_failed'] + ')'
                    );
                    $('td', row).eq(5).html('').append(
                        '<span class="text-white badge badge-glow badge-' + AffiliateSettleStatusData[data['status']][1] + '">' + AffiliateSettleStatusData[data['status']][0] + '</span>'
                    );
                    $('td', row).eq(7).html('').append(
                        (data['status'] != '{{ AFFILIATE_SETTLE_STATUS_FINISHED }}') ? '' :
                        '<a class="btn btn-icon btn-icon-rounded-circle text-info btn-flat-info user-tooltip" href="' + BASE_URL + 'affiliate/settle/detail?id=' +  data["id"] + '" title="' + '{{ trans('ui.button.detail') }}' + '">'
                        + '<i class="fa fa-edit"></i></a>'
                    );
                },
            });
        }
    </script>
@endsection
