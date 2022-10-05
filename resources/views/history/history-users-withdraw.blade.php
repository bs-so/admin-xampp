@extends('layouts.afterlogin')

@section('title', trans('users-history.withdraw.title'))

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
                                    <label class="form-label">{{ trans('users-history.withdraw.userid') }}</label>
                                    <input type="text" id="filter-userid" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users-history.withdraw.user_name') }}</label>
                                    <input type="text" id="filter-nickname" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users-history.withdraw.currency') }}</label>
                                    <select id="filter-currency" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('CryptoSettingsData') as $currency => $data)
                                            <option value="{{ $currency }}">{{ $currency }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users-history.withdraw.destination') }}</label>
                                    <input type="text" id="filter-destination" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users-history.withdraw.tx_id') }}</label>
                                    <input type="text" id="filter-tx-id" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users-history.withdraw.status') }}</label>
                                    <select id="filter-status" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('UsersWithdrawStatus') as $status => $value)
                                            <option value="{{ $status }}">{{ $value[0] }}</option>
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
            echo 'var UsersWithdrawStatus = ' . json_encode(g_enum('UsersWithdrawStatus')) . ';';
            echo 'var UserWithdrawTypeData = ' . json_encode(g_enum('UserWithdrawTypeData')) . ';';
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
                    <a type="button" class="text-white btn btn-success" href="{{ route('users.history.download') }}?type=2">
                        <i class="fa fa-download"></i>&nbsp;{{ trans('ui.button.download') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="withdraw-list" class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('users-history.withdraw.no') }}</th>
                                <th>{{ trans('users-history.withdraw.userid') }}</th>
                                <th>{{ trans('users-history.withdraw.user_name') }}</th>
                                <th>{{ trans('users-history.withdraw.currency') }}</th>
                                <th>{{ trans('users-history.withdraw.destination') }}</th>
                                <th>{{ trans('users-history.withdraw.amount') }}</th>
                                <th>{{ trans('users-history.withdraw.withdraw_fee') }}</th>
                                <th>{{ trans('users-history.withdraw.transfer_fee') }}</th>
                                <th>{{ trans('users-history.withdraw.tx_id') }}</th>
                                <th>{{ trans('users-history.withdraw.remark') }}</th>
                                <th>{{ trans('users-history.withdraw.status') }}</th>
                                <th>{{ trans('users-history.withdraw.reged_at') }}</th>
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
    <script src="{{ cAsset("js/users-withdraw-history.js") }}"></script>

    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <script>
        function initTable() {
            listTable = $('#withdraw-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/users/history/withdraw/search',
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
				order: [11, 'desc'],
                columns: [
                    {data: 'id'},
                    {data: 'userid'},
                    {data: 'user_name'},
                    {data: 'currency'},
                    {data: 'destination'},
                    {data: 'amount', className: 'text-right'},
                    {data: 'withdraw_fee', className: 'text-right'},
                    {data: 'transfer_fee', className: 'text-right'},
                    {data: 'tx_id'},
                    {data: 'status'},
                    {data: 'remark'},
                    {data: 'created_at'},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = listTable.page.info();

                    // *********************************************************************
                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(4).html('').append(getReducedStr(data['destination'], 8, 8));
                    $('td', row).eq(5).html('').append(
                        _number_format(data['amount'], CryptoSettings[data['currency']]['rate_decimals'])
                    );
                    $('td', row).eq(6).html('').append(
                        _number_format(data['withdraw_fee'], CryptoSettings[data['currency']]['rate_decimals'])
                    );
                    let tooltip = '';
                    if (data['currency'] == 'ETH' || data['currency'] == 'USDT') {
                        tooltip += '{{ trans('users-history.withdraw.gas_price') }}' + ' : ' + _number_format(data['gas_price'], 2) + '\n';
                        tooltip += '{{ trans('users-history.withdraw.gas_used') }}' + ' : ' + _number_format(data['gas_used'], Math.min(15, CryptoSettings['ETH']['rate_decimals'])) + '\n';
                        tooltip += '{{ trans('ui.unit.name') }}' + ' : ' + 'ETH';
                    }
                    else {
                        tooltip += '{{ trans('ui.unit.name') }}' + ' : ' + data['currency'];
                    }
                    $('td', row).eq(7).html('').append(
                        '<span class="btn-flat-info user-tooltip" title="' + tooltip + '">' +
                            _number_format(data['transfer_fee'], Math.min(15, CryptoSettings['ETH']['rate_decimals'])) +
                        '</span>'
                    );

                    let txUrl = '';
                    let tx_id = data['tx_id'].substring(0, 10) + '...' + data['tx_id'].substring(data['tx_id'].length - 3, data['tx_id'].length);
                    if (data['currency'] == 'BTC') {
                        txUrl = '{{ BTC_CONFIRM_URL }}' + data['tx_id'];
                    }
                    else if (data['currency'] == 'ETH' || data['currency'] == 'USDT') {
                        txUrl = '{{ ETH_CONFIRM_URL }}' + data['tx_id'];
                    }
                    else if (data['currency'] == 'BCH') {
                        txUrl = '{{ BCH_CONFIRM_URL }}' + data['tx_id'];
                    }
                    $('td', row).eq(8).html('').append(
                        (data['tx_id'] == '') ? '' :
                        '<a target="_blank" href="' + txUrl + '" class="btn-flat-info">' + tx_id + '</a>'
                    );
                    $('td', row).eq(9).html('').append(
                        '<span class="text-white badge-glow badge badge-' + UsersWithdrawStatus[data['status']][1] + '">' + UsersWithdrawStatus[data['status']][0] + '</span>'
                    );
                },
            });
        }
    </script>
@endsection
