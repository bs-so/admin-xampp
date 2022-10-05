@extends('layouts.afterlogin')

@section('title', trans('transactions.title'))

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
                                    <label class="form-label">{{ trans('transactions.table.currency') }}</label>
                                    <select class="form-control" id="filter-currency">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('CryptoSettingsData') as $currency => $data)
                                            <option value="{{ $currency }}">{{ $currency }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('transactions.table.address') }}</label>
                                    <input type="text" id="filter-address" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('transactions.table.tx_id') }}</label>
                                    <input type="text" id="filter-tx_id" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('transactions.table.status') }}</label>
                                    <select id="filter-status" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('TransferStatusData') as $index => $status)
                                            <option value="{{ $index }}">{{ $status[0] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('transactions.table.created_at') }}</label>
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

        <div class="row">
            <div class="col-lg-12">
                <div class="form-group">
                    <a type="button" class="text-white btn btn-success" href="{{ route('transactions.download') }}">
                        <i class="fa fa-download"></i>&nbsp;{{ trans('ui.button.download') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="transactions-list" class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('transactions.table.no') }}</th>
                                <th>{{ trans('transactions.table.currency') }}</th>
                                <th>{{ trans('transactions.table.from_address') }}</th>
                                <th>{{ trans('transactions.table.to_address') }}</th>
                                <th>{{ trans('transactions.table.amount') }}</th>
                                <th>{{ trans('transactions.table.tx_id') }}</th>
                                <th>{{ trans('transactions.table.status') }}</th>
                                <th>{{ trans('transactions.table.created_at') }}</th>
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
    <script src="{{ cAsset("js/transactions-list.js") }}"></script>

    <?php echo('<script>let TransferStatusData = ' . json_encode(g_enum('TransferStatusData')) . ';</script>') ?>
    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <script>
        function initTable() {
            listTable = $('#transactions-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/transactions/search',
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
                columnDefs: [],
                order: [7, 'desc'],
                columns: [
                    {data: 'id'},
                    {data: 'currency'},
                    {data: 'from_address'},
                    {data: 'to_address'},
                    {data: 'amount'},
                    {data: 'tx_id'},
                    {data: 'status'},
                    {data: 'created_at'},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = listTable.page.info();

                    // *********************************************************************
                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(2).html('').append(getReducedStr(data['from_address'], 20, 5));
                    $('td', row).eq(3).html('').append(getReducedStr(data['to_address'], 20, 5));

                    $('td', row).eq(4).html('').append(
                        _number_format(data['amount'], CryptoSettings[data['currency']]['rate_decimals'])
                    );

                    let transfer_fee = _number_format(data['transfer_fee'], CryptoSettings[data['currency']]['rate_decimals']);
                    let tx_id = data['tx_id'].substring(0, 10) + '...' + data['tx_id'].substring(data['tx_id'].length - 3, data['tx_id'].length);
                    let txUrl = '';
                    if (data['currency'] == 'BTC') {
                        txUrl = '{{ BTC_CONFIRM_URL }}' + data['tx_id'];
                    }
                    else if (data['currency'] == 'ETH' || data['currency'] == 'USDT') {
                        txUrl = '{{ ETH_CONFIRM_URL }}' + data['tx_id'];
                    }
                    else if (data['currency'] == 'BCH') {
                        txUrl = '{{ BCH_CONFIRM_URL }}' + data['tx_id'];
                    }
                    $('td', row).eq(5).html('').append(
                        (data['tx_id'] == '') ? '' :
                        '<a target="_blank" href="' + txUrl + '" class="btn-flat-info user-tooltip" title="' + data['nonce'] + '/' + transfer_fee + '">' + tx_id + '</a>'
                    );

                    $('td', row).eq(6).html('').append(
                        '<span class="text-white badge-glow badge badge-' + TransferStatusData[data['status']][1] + '">' + TransferStatusData[data['status']][0] + '</span>'
                    );
                },
            });
        }
    </script>
@endsection
