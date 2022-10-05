@extends('layouts.afterlogin')

@section('title', trans('affiliate.settle.detail_title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/jstree/themes/default/style.min.css') }}" rel="stylesheet" />
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
@endsection

@section('contents')
    <?php $crypto_settings = Session::get('crypto_settings'); ?>
    <fieldset>
        <div class="row" id="div-main">
            <div class="col-md-12 col-lg-12">
                <a class="btn btn-primary text-white mb-2" href="{{ route('affiliate.settle') }}">{{ trans('ui.button.back') }}</a>
                <ul class="nav nav-tabs nav-fill" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#nav-main" aria-controls="main" role="tab" aria-selected="false">{{ trans('affiliate.settle.navs_main') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#nav-settle-data" aria-controls="settle-data" role="tab" aria-selected="false">{{ trans('affiliate.settle.navs_settle_data') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#nav-commission" aria-controls="commission" role="tab" aria-selected="false">{{ trans('affiliate.settle.navs_commission') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#nav-balances" aria-controls="balances" role="tab" aria-selected="false">{{ trans('affiliate.settle.navs_balances') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#nav-announces" aria-controls="announces" role="tab" aria-selected="false">{{ trans('affiliate.settle.navs_mails') }}</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="nav-main" aria-labelledby="nav-main" role="tabpanel">
                        <div class="card">
                            <div class="card-body table-responsive">
                                <div class="card-title mb-2">{{ trans('affiliate.settle.information') }}</div>
                                <div class="row form-group mb-0">
                                    <div class="col-sm-3 text-left">
                                        <h5 class="form-label">{{ trans('affiliate.settle.operator') }}</h5>
                                    </div>
                                    <div class="col-sm-8">
                                        <h5 class="form-label text-danger">{{ isset($settle->operator) ? $settle->operator : '' }}</h5>
                                    </div>
                                </div>
                                <div class="row form-group mb-0">
                                    <div class="col-sm-3 text-left">
                                        <h5 class="form-label">{{ trans('affiliate.settle.remark') }}</h5>
                                    </div>
                                    <div class="col-sm-8">
                                        <h5 class="form-label text-danger">{{ $settle->remark }}</h5>
                                    </div>
                                </div>
                                <div class="row form-group mb-0">
                                    <div class="col-sm-3 text-left">
                                        <h5 class="form-label">{{ trans('affiliate.settle.settled_at') }}</h5>
                                    </div>
                                    <div class="col-sm-8">
                                        <h5 class="form-label text-danger">{{ $settle->created_at }}</h5>
                                    </div>
                                </div>
                                <div class="row form-group mb-0">
                                    <div class="col-sm-3 text-left">
                                        <h5 class="form-label">{{ trans('affiliate.settle.status') }}</h5>
                                    </div>
                                    <div class="col-sm-8">
                                        <h5>
                                            <span class="text-white badge badge-glow badge-{{ g_enum('AffiliateSettleStatusData')[$settle->status][1] }}">
                                                {{ trans(g_enum('AffiliateSettleStatusData')[$settle->status][0]) }}
                                            </span>
                                        </h5>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-sm-12">
                                        <h3>{{ trans('affiliate.settle.summary') }}</h3>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead class="thead-dark">
                                                <th>{{ trans('affiliate.commission.currency') }}</th>
                                                <th class="text-right">{{ trans('affiliate.commission.balance') }}</th>
                                                <th class="text-right">{{ trans('affiliate.commission.total_user') }}</th>
                                                <th class="text-right">{{ trans('affiliate.commission.total_commission') }}</th>
                                                <th class="text-right">{{ trans('affiliate.commission.percent') }}(%)</th>
                                                </thead>
                                                <tbody>
                                                @foreach ($summaries as $index => $summary)
                                                    <tr>
                                                        <td>{{ $summary->currency }}</td>
                                                        <td class="text-right">{{ _number_format($summary->system_balance, $crypto_settings[$summary->currency]['rate_decimals']) }}</td>
                                                        <td class="text-right">{{ $summary->total_user }}</td>
                                                        <td class="text-right">{{ _number_format($summary->total_commission, $crypto_settings[$summary->currency]['rate_decimals']) }}</td>
                                                        <td class="text-right">{{ _number_format($summary->percent, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="nav-settle-data" aria-labelledby="nav-settle-data" role="tabpanel">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table" id="settle-data">
                                            <thead>
                                            <tr>
                                                <th>{{ trans('affiliate.settle.no') }}</th>
                                                <th>{{ trans('affiliate.settle.userid') }}</th>
                                                <th>{{ trans('affiliate.settle.currency') }}</th>
                                                <th>{{ trans('affiliate.settle.amount') }}</th>
                                            </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="nav-commission" aria-labelledby="commission" role="tabpanel">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="card-title mb-2">{{ trans('affiliate.settle.navs_commission') }}</div>
                                    <div class="table-responsive">
                                        <table class="table" id="commission-list">
                                            <thead>
                                            <tr>
                                                <th rowspan="2">{{ trans('affiliate.commission.no') }}</th>
                                                <th rowspan="2">{{ trans('affiliate.commission.userid') }}</th>
                                                <th rowspan="2">{{ trans('affiliate.commission.nickname') }}</th>
                                                <th class="text-right" colspan="{{ count($crypto_settings) }}">{{ trans('affiliate.commission.commission_curr') }}</th>
                                                <th class="text-right" colspan="{{ count($crypto_settings) }}">{{ trans('affiliate.commission.commission_prev') }}</th>
                                                <th rowspan="2">{{ trans('affiliate.commission.created_at') }}</th>
                                            </tr>
                                            <tr>
                                                @foreach ($crypto_settings as $currency => $data)
                                                    <th class="text-right">{{ $currency }}</th>
                                                @endforeach
                                                @foreach ($crypto_settings as $currency => $data)
                                                    <th class="text-right">{{ $currency }}</th>
                                                @endforeach
                                            </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="nav-balances" aria-labelledby="balances" role="tabpanel">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="card-title mb-2">{{ trans('affiliate.settle.navs_balances') }}</div>
                                    <div class="table-responsive">
                                        <table class="table" id="balance-list">
                                            <thead>
                                            <th>{{ trans('affiliate.balance.no') }}</th>
                                            <th>{{ trans('affiliate.balance.userid') }}</th>
                                            <th>{{ trans('affiliate.balance.nickname') }}</th>
                                            <th>{{ trans('affiliate.balance.currency') }}</th>
                                            <th>{{ trans('affiliate.balance.prev_balance') }}</th>
                                            <th>{{ trans('affiliate.balance.next_balance') }}</th>
                                            <th>{{ trans('affiliate.balance.created_at') }}</th>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="nav-announces" aria-labelledby="nav-announces" role="tabpanel">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table" id="announce-list">
                                            <thead>
                                            <tr>
                                                <th>{{ trans('affiliate.announce.no') }}</th>
                                                <th>{{ trans('affiliate.announce.userid') }}</th>
                                                <th>{{ trans('affiliate.announce.nickname') }}</th>
                                                <th>{{ trans('affiliate.announce.is_sent') }}</th>
                                                <th>{{ trans('affiliate.announce.sent_at') }}</th>
                                            </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </fieldset>
@endsection

@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ cAsset('vendor/loadingoverlay/loadingoverlay.min.js') }}"></script>
    <script src="{{ cAsset('vendor/jstree/jstree.min.js') }}"></script>
    <script src="{{ cAsset("js/__common.js") }}"></script>
    <script src="{{ cAsset("js/affiliate_settle-detail.js") }}"></script>

    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <?php echo '<script>let AffiliateSettleStatusData = ' . json_encode(g_enum('AffiliateSettleStatusData')) . '</script>' ?>
    <?php echo '<script>let AnnounceStatusData = ' . json_encode(g_enum('AnnounceStatusData')) . '</script>' ?>
    <script>
        function loadSettleData() {
            settleDataList = $('#settle-data').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/affiliate/settle/loadSettleData',
                    type: 'POST',
                    data: {
                        settle_id: '{{ $settle->id }}',
                        settle_status: '{{ ENTRY_SETTLE_STATUS_FINISHED }}',
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
                columnDefs: [],
                lengthMenu: [[10, 25, 50, 100, 1000, 2500, -1], [10, 25, 50, 100, 1000, 2500, "All"]],
                columns: [
                    {data: 'id'},
                    {data: 'userid'},
                    {data: 'currency'},
                    {data: 'amount', class: 'text-right'},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = balanceList.page.info();

                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(3).html('').append(
                        _number_format(data['amount'], Math.min('{{ MINIMUM_BALANCE_DECIMALS }}', CryptoSettings[data['currency']]['rate_decimals']))
                    );
                },
            });
        }

        function loadCommission() {
            let columns = [];
            columns.push({data: null});
            columns.push({data: 'userid'});
            columns.push({data: 'nickname'});
            for (let currency in CryptoSettings) {
                columns.push({data: 'curr_' + currency, className: "text-right"});
            }
            for (let currency in CryptoSettings) {
                columns.push({data: 'prev_' + currency, className: "text-right"});
            }
            columns.push({data: 'created_at'});

            let targets = [];
            for (let i = 0; i < columns.length; i ++) {
                targets.push(i);
            }

            commissionList = $('#commission-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/affiliate/settle/loadCommission',
                    type: 'POST',
                    data: {
                        settle_id: '{{ $settle->id }}',
                        settle_status: '{{ ENTRY_SETTLE_STATUS_FINISHED }}',
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
                columnDefs: [{
                    targets: targets,
                    orderable: false,
                    searchable: false
                }],
                lengthMenu: [[10, 25, 50, 100, 1000, 2500, -1], [10, 25, 50, 100, 1000, 2500, "All"]],
                columns: columns,
                createdRow: function (row, data, index) {
                    var pageInfo = commissionList.page.info();

                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    let danger = false;
                    for (let currency in CryptoSettings) {
                        if (data['curr_' + currency] == data['prev_' + currency]) danger = true;
                    }
                    if (danger) {
                        $('td', row).addClass('bg-danger');
                        $('td', row).addClass('text-white');
                    }
                },
            });
        }

        function loadBalances() {
            balanceList = $('#balance-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/affiliate/settle/loadBalances',
                    type: 'POST',
                    data: {
                        settle_id: '{{ $settle->id }}',
                        settle_status: '{{ ENTRY_SETTLE_STATUS_FINISHED }}',
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
                columnDefs: [],
                lengthMenu: [[10, 25, 50, 100, 1000, 2500, -1], [10, 25, 50, 100, 1000, 2500, "All"]],
                columns: [
                    {data: 'id'},
                    {data: 'userid'},
                    {data: 'user_name'},
                    {data: 'currency'},
                    {data: 'prev_balance', class: 'text-right'},
                    {data: 'next_balance', class: 'text-right'},
                    {data: 'created_at'},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = balanceList.page.info();

                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(4).html('').append(
                        _number_format(data['prev_balance'], Math.min('{{ MINIMUM_BALANCE_DECIMALS }}', CryptoSettings[data['currency']]['rate_decimals']))
                    );
                    $('td', row).eq(5).html('').append(
                        _number_format(data['next_balance'], Math.min('{{ MINIMUM_BALANCE_DECIMALS }}', CryptoSettings[data['currency']]['rate_decimals']))
                    );
                },
            });
        }

        function loadAnnounces() {
            announceList = $('#announce-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/affiliate/settle/loadAnnounces',
                    type: 'POST',
                    data: {
                        settle_id: '{{ $settle->id }}',
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
                columnDefs: [],
                lengthMenu: [[10, 25, 50, 100, 1000, 2500, -1], [10, 25, 50, 100, 1000, 2500, "All"]],
                columns: [
                    {data: 'id'},
                    {data: 'userid'},
                    {data: 'nickname'},
                    {data: 'is_sent'},
                    {data: 'updated_at'},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = announceList.page.info();

                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(3).html('').append(
                        '<span class="text-white badge badge-glow badge-' + AnnounceStatusData[data['is_sent']][1] + '">' + AnnounceStatusData[data['is_sent']][0] + '</span>'
                    );
                },
            });
        }
    </script>
@endsection
