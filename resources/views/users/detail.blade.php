@extends('layouts.afterlogin')

@section('title', sprintf(trans('users.detail_title'), $trader->userid))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/autocomplete/autocomplete.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
    <link href="{{ cAsset('app-assets/vendors/css/extensions/swiper.min.css') }}" rel="stylesheet">
    <link href="{{ cAsset('app-assets/css/plugins/extensions/swiper.css') }}" rel="stylesheet">

    <style>
        .title-img {
            width: 100%;
            max-width: 100px;
        }
    </style>
@endsection

@section('contents')
    <!-- page users view start -->
    <section class="users-list-wrapper">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="filter-email" value="{{ $trader->email }}">
                    </div>
                    <div class="col-sm-6">
                        <button id="btn-filter" class="btn btn-primary"><i class="fa fa-search"></i>&nbsp;{{ trans('ui.button.search') }}</button>
                        <button id="btn-back" class="btn btn-secondary"><i class="fa fa-arrow-left"></i>&nbsp;{{ trans('ui.button.back') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">{{ trans('users.section.profile') }}</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-2 col-md-2 col-lg-2">
                        <div class="users-view-image">
                            <img style="max-width: 120px !important; max-height: 120px !important;" src="{{ $trader->avatar == '' ? (cUrl('/') . '/uploads/avatars/_noneTrader.jpg') : $trader->avatar }}" class="users-avatar-shadow w-100 h-100 rounded mb-2 ml-1" alt="avatar">
                        </div>
                    </div>
                    <div class="col-sm-3 col-md-3 col-lg-3">
                        <div class="form-group">
                            <div class="row pb-1">
                                <div class="col-sm-4"><span class="font-weight-bold">{{ trans('users.table.userid') }}</span></div>
                                <div class="col-sm-8"><span>{{ $trader->userid }}</span></div>
                            </div>
                            <div class="row pb-1">
                                <div class="col-sm-4"><span class="font-weight-bold">{{ trans('users.table.email') }}</span></div>
                                <div class="col-sm-8"><span>{{ $trader->email }}</span></div>
                            </div>
                            <div class="row pb-1">
                                <div class="col-sm-4"><span class="font-weight-bold">{{ trans('users.table.firstname') }}</span></div>
                                <div class="col-sm-8"><span>{{ $trader->firstname }}</span></div>
                            </div>
                            <div class="row pb-1">
                                <div class="col-sm-4"><span class="font-weight-bold">{{ trans('users.table.lastname') }}</span></div>
                                <div class="col-sm-8"><span>{{ $trader->lastname }}</span></div>
                            </div>
                            <div class="row pb-1">
                                <div class="col-sm-4"><span class="font-weight-bold">{{ trans('users.table.nickname') }}</span></div>
                                <div class="col-sm-8"><span>{{ $trader->nickname }}</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-md-3 col-lg-3">
                        <div class="form-group">
                            <div class="row pb-1">
                                <div class="col-sm-4"><span class="font-weight-bold">{{ trans('users.table.birthday') }}</span></div>
                                <div class="col-sm-8"><span>{{ $trader->birthday }}</span></div>
                            </div>
                            <div class="row pb-1">
                                <div class="col-sm-4"><span class="font-weight-bold">{{ trans('users.table.kyc_status') }}</span></div>
                                <div class="col-sm-8"><span class="text-white badge badge-glow badge-{{ g_enum('KycStatusData')[$trader->kyc_status][1] }}">{{ g_enum('KycStatusData')[$trader->kyc_status][0] }}</span></div>
                            </div>
                            <div class="row pb-1">
                                <div class="col-sm-4"><span class="font-weight-bold">{{ trans('users.table.status') }}</span></div>
                                <div class="col-sm-8"><span class="text-white badge badge-glow badge-{{ g_enum('StatusData')[$trader->status][1] }}">{{ g_enum('StatusData')[$trader->status][0] }}</span></div>
                            </div>
                            <div class="row pb-1">
                                <div class="col-sm-4"><span class="font-weight-bold">{{ trans('users.table.gender') }}</span></div>
                                <div class="col-sm-8"><span class="text-white badge badge-glow badge-{{ g_enum('UserGenderData')[$trader->gender][1] }}">{{ g_enum('UserGenderData')[$trader->gender][0] }}</span></div>
                            </div>
                            <div class="row pb-1">
                                <div class="col-sm-4"><span class="font-weight-bold">{{ trans('users.table.country') }}</span></div>
                                <div class="col-sm-8"><span>{{ $trader->country }}</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-md-4 col-lg-4">
                        <div class="form-group">
                            <div class="row pb-1">
                                <div class="col-sm-4"><span class="font-weight-bold">{{ trans('users.table.mobile') }}</span></div>
                                <div class="col-sm-8"><span>{{ $trader->mobile }}</span></div>
                            </div>
                            <div class="row pb-1">
                                <div class="col-sm-4"><span class="font-weight-bold">{{ trans('users.table.city') }}</span></div>
                                <div class="col-sm-8"><span>{{ $trader->city }}</span></div>
                            </div>
                            <div class="row pb-1">
                                <div class="col-sm-4"><span class="font-weight-bold">{{ trans('users.table.postal_code') }}</span></div>
                                <div class="col-sm-8"><span>{{ $trader->postal_code }}</span></div>
                            </div>
                            <div class="row pb-1">
                                <div class="col-sm-4"><span class="font-weight-bold">{{ trans('users.table.address') }}</span></div>
                                <div class="col-sm-8"><span>{{ $trader->address }}</span></div>
                            </div>
                            <div class="row pb-1">
                                <div class="col-sm-4"><span class="font-weight-bold">{{ trans('users.table.reged_at') }}</span></div>
                                <div class="col-sm-8"><span>{{ $trader->created_at }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#nav-balance" aria-controls="balance" role="tab" aria-selected="false">{{ trans('users.nav.balance') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#nav-deposit" aria-controls="deposit" role="tab" aria-selected="false">{{ trans('users.nav.deposit') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#nav-withdraw" aria-controls="withdraw" role="tab" aria-selected="false">{{ trans('users.nav.withdraw') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#nav-send" aria-controls="send" role="tab" aria-selected="false">{{ trans('users.nav.send') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#nav-receive" aria-controls="receive" role="tab" aria-selected="false">{{ trans('users.nav.receive') }}</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="nav-balance" aria-labelledby="nav-balance" role="tabpanel">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="balance-list" class="table">
                                    <thead>
                                    <tr>
                                        <th>{{ trans('users.table.no') }}</th>
                                        <th>{{ trans('users.table.currency') }}</th>
                                        <th>{{ trans('users.table.balance') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $index = 1;
                                        $CryptoSettings = Session::get('crypto_settings');
                                    ?>
                                    @foreach ($CryptoSettings as $currency => $data)
                                        <tr>
                                            <td>{{ $index ++ }}</td>
                                            <td>{{ $currency }}</td>
                                            <td>{{ isset($balance[$currency]) ? _number_format($balance[$currency], $data['rate_decimals']) : 0 }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="nav-deposit" aria-labelledby="nav-deposit" role="tabpanel">
                <div class="card">
                    <div class="card-content">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-lg-12">
                                    <a type="button" class="text-white btn btn-success" href="{{ route('users.history.download') }}?type=0&trader={{ $trader->id }}">
                                        <i class="fa fa-download"></i>&nbsp;{{ trans('ui.button.download') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="deposit-list" class="table">
                                    <thead>
                                    <tr>
                                        <th>{{ trans('users-history.deposit.no') }}</th>
                                        <th>{{ trans('users-history.deposit.user_name') }}</th>
                                        <th>{{ trans('users-history.deposit.currency') }}</th>
                                        <th>{{ trans('users-history.deposit.wallet_addr') }}</th>
                                        <th>{{ trans('users-history.deposit.amount') }}</th>
                                        <th>{{ trans('users-history.deposit.deposit_fee') }}</th>
                                        <th>{{ trans('users-history.deposit.transfer_fee') }}</th>
                                        <th>{{ trans('users-history.deposit.status') }}</th>
                                        <th>{{ trans('users-history.deposit.tx_id') }}</th>
                                        <th>{{ trans('users-history.deposit.reged_at') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="nav-withdraw" aria-labelledby="nav-withdraw" role="tabpanel">
                <div class="card">
                    <div class="card-content">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-lg-12">
                                    <a type="button" class="text-white btn btn-success" href="{{ route('users.history.download') }}?type=2&trader={{ $trader->id }}">
                                        <i class="fa fa-download"></i>&nbsp;{{ trans('ui.button.download') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="withdraw-list" class="table">
                                    <thead>
                                    <tr>
                                        <th>{{ trans('users-history.withdraw.no') }}</th>
                                        <th>{{ trans('users-history.withdraw.user_name') }}</th>
                                        <th>{{ trans('users-history.withdraw.currency') }}</th>
                                        <th>{{ trans('users-history.withdraw.destination') }}</th>
                                        <th>{{ trans('users-history.withdraw.amount') }}</th>
                                        <th>{{ trans('users-history.withdraw.withdraw_fee') }}</th>
                                        <th>{{ trans('users-history.withdraw.transfer_fee') }}</th>
                                        <th>{{ trans('users-history.withdraw.status') }}</th>
                                        <th>{{ trans('users-history.withdraw.tx_id') }}</th>
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
            </div>
            <div class="tab-pane" id="nav-send" aria-labelledby="nav-send" role="tabpanel">
                <div class="card">
                    <div class="card-content">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-lg-12">
                                    <a type="button" class="text-white btn btn-success" href="{{ route('users.transfer.download') }}?type=2&trader={{ $trader->id }}">
                                        <i class="fa fa-download"></i>&nbsp;{{ trans('ui.button.download') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="send-list" class="table">
                                    <thead>
                                    <tr>
                                        <th>{{ trans('transfer.users.no') }}</th>
                                        <th>{{ trans('transfer.users.sender') }}</th>
                                        <th>{{ trans('transfer.users.receiver') }}</th>
                                        <th>{{ trans('transfer.users.currency') }}</th>
                                        <th>{{ trans('transfer.users.amount') }}</th>
                                        <th>{{ trans('transfer.users.fee') }}</th>
                                        <th>{{ trans('transfer.users.remark') }}</th>
                                        <th>{{ trans('transfer.users.created_at') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="nav-receive" aria-labelledby="nav-receive" role="tabpanel">
                <div class="card">
                    <div class="card-content">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-lg-12">
                                    <a type="button" class="text-white btn btn-success" href="{{ route('users.transfer.download') }}?type=3&trader={{ $trader->id }}">
                                        <i class="fa fa-download"></i>&nbsp;{{ trans('ui.button.download') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="receive-list" class="table">
                                    <thead>
                                    <tr>
                                        <th>{{ trans('transfer.users.no') }}</th>
                                        <th>{{ trans('transfer.users.sender') }}</th>
                                        <th>{{ trans('transfer.users.receiver') }}</th>
                                        <th>{{ trans('transfer.users.currency') }}</th>
                                        <th>{{ trans('transfer.users.amount') }}</th>
                                        <th>{{ trans('transfer.users.fee') }}</th>
                                        <th>{{ trans('transfer.users.remark') }}</th>
                                        <th>{{ trans('transfer.users.created_at') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- page users view end -->
@endsection

@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/autocomplete/autocomplete.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ cAsset('app-assets/vendors/js/extensions/swiper.min.js') }}"></script>
    <script src="{{ cAsset("js/users-detail.js") }}"></script>

    <?php echo '<script>let StatusData = ' . json_encode(g_enum('StatusData')) . '</script>' ?>;
    <?php echo '<script>let UsersDepositStatus = ' . json_encode(g_enum('UsersDepositStatus')) . '</script>' ?>;
    <?php echo '<script>let UsersExchangeStatus = ' . json_encode(g_enum('UsersExchangeStatus')) . '</script>' ?>;
    <?php echo '<script>let UsersWithdrawStatus = ' . json_encode(g_enum('UsersWithdrawStatus')) . '</script>' ?>;
    <?php echo '<script>let UsersWithdrawCashStatus = ' . json_encode(g_enum('UsersWithdrawCashStatus')) . '</script>' ?>;
    <?php echo '<script>let BankTypeData = ' . json_encode(g_enum('BankTypeData')) . '</script>' ?>;
    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <script>
        function initTable() {
            depositTable = $('#deposit-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/users/history/deposit/search',
                    type: 'POST',
                    data: {
                        user_id: '{{ $trader->id }}',
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
                },
                lengthMenu: [[10, 25, 50, 100, 1000, 2500, -1], [10, 25, 50, 100, 1000, 2500, "{{ trans('ui.table.all') }}"]],
                columnDefs: [],
                order: [9, 'desc'],
                columns: [
                    {data: 'id'},
                    {data: 'user_name'},
                    {data: 'currency'},
                    {data: 'wallet_addr'},
                    {data: 'amount', className: 'text-right'},
                    {data: 'deposit_fee', className: 'text-right'},
                    {data: 'transfer_fee', className: 'text-right'},
                    {data: 'status'},
                    {data: 'tx_id'},
                    {data: 'created_at'},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = depositTable.page.info();

                    // *********************************************************************
                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(3).html('').append(getReducedStr(data['wallet_addr'], 20, 5));
                    $('td', row).eq(4).html('').append(
                        _number_format(data['amount'], CryptoSettings[data['currency']]['rate_decimals'])
                    );
                    $('td', row).eq(5).html('').append(
                        _number_format(data['deposit_fee'], CryptoSettings[data['currency']]['rate_decimals'])
                    );
                    $('td', row).eq(6).html('').append(
                        _number_format(data['transfer_fee'], CryptoSettings[data['currency']]['rate_decimals'])
                    );

                    $('td', row).eq(7).html('').append(
                        '<span class="text-white badge-glow badge badge-' + UsersExchangeStatus[data['status']][1] + '">' + UsersExchangeStatus[data['status']][0] + '</span>'
                    );
                    $('td', row).eq(8).html('').append(
                        getTxLink(data['currency'], data['tx_id'])
                    );
                },
            });
            withdrawTable = $('#withdraw-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/users/history/withdraw/search',
                    type: 'POST',
                    data: {
                        user_id: '{{ $trader->id }}',
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
                },
                lengthMenu: [[10, 25, 50, 100, 1000, 2500, -1], [10, 25, 50, 100, 1000, 2500, "{{ trans('ui.table.all') }}"]],
                columnDefs: [],
                order: [9, 'desc'],
                columns: [
                    {data: 'id'},
                    {data: 'user_name'},
                    {data: 'currency'},
                    {data: 'destination'},
                    {data: 'amount', className: 'text-right'},
                    {data: 'withdraw_fee', className: 'text-right'},
                    {data: 'transfer_fee', className: 'text-right'},
                    {data: 'status'},
                    {data: 'tx_id'},
                    {data: 'created_at'},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = withdrawTable.page.info();

                    // *********************************************************************
                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(3).html('').append(getReducedStr(data['destination'], 20, 5));
                    $('td', row).eq(4).html('').append(
                        _number_format(data['amount'], CryptoSettings[data['currency']]['rate_decimals'])
                    );
                    $('td', row).eq(5).html('').append(
                        _number_format(data['withdraw_fee'], CryptoSettings[data['currency']]['rate_decimals'])
                    );
                    $('td', row).eq(6).html('').append(
                        _number_format(data['transfer_fee'], CryptoSettings[data['currency']]['rate_decimals'])
                    );

                    $('td', row).eq(7).html('').append(
                        '<span class="text-white badge-glow badge badge-' + UsersWithdrawStatus[data['status']][1] + '">' + UsersWithdrawStatus[data['status']][0] + '</span>'
                    );
                    $('td', row).eq(8).html('').append(
                        getTxLink(data['currency'], data['tx_id'])
                    );
                },
            });
            sendTable = $('#send-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/users/transfer/search',
                    type: 'POST',
                    data: {
                        is_user: 1,
                        sender: '{{ $trader->id }}',
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
                },
                lengthMenu: [[10, 25, 50, 100, 1000, 2500, -1], [10, 25, 50, 100, 1000, 2500, "{{ trans('ui.table.all') }}"]],
                columnDefs: [],
                order: [7, 'desc'],
                columns: [
                    {data: 'id'},
                    {data: 'sender'},
                    {data: 'receiver'},
                    {data: 'currency'},
                    {data: 'amount', className: 'text-right'},
                    {data: 'fee', className: 'text-right'},
                    {data: 'remark'},
                    {data: 'created_at'},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = sendTable.page.info();

                    // *********************************************************************
                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(4).html('').append(
                        _number_format(data['amount'], Math.min('{{ MINIMUM_BALANCE_DECIMALS }}', CryptoSettings[data['currency']]['rate_decimals']))
                    );
                    $('td', row).eq(5).html('').append(
                        _number_format(data['fee'], Math.min('{{ MINIMUM_BALANCE_DECIMALS }}', CryptoSettings[data['currency']]['rate_decimals']))
                    );
                },
            });
            receiveTable = $('#receive-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/users/transfer/search',
                    type: 'POST',
                    data: {
                        is_user: 1,
                        receiver: '{{ $trader->id }}',
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
                },
                lengthMenu: [[10, 25, 50, 100, 1000, 2500, -1], [10, 25, 50, 100, 1000, 2500, "{{ trans('ui.table.all') }}"]],
                columnDefs: [],
                order: [7, 'desc'],
                columns: [
                    {data: 'id'},
                    {data: 'sender'},
                    {data: 'receiver'},
                    {data: 'currency'},
                    {data: 'amount', className: 'text-right'},
                    {data: 'fee', className: 'text-right'},
                    {data: 'remark'},
                    {data: 'created_at'},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = receiveTable.page.info();

                    // *********************************************************************
                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(4).html('').append(
                        _number_format(data['amount'], Math.min('{{ MINIMUM_BALANCE_DECIMALS }}', CryptoSettings[data['currency']]['rate_decimals']))
                    );
                    $('td', row).eq(5).html('').append(
                        _number_format(data['fee'], Math.min('{{ MINIMUM_BALANCE_DECIMALS }}', CryptoSettings[data['currency']]['rate_decimals']))
                    );
                },
            });
        }

        $('#btn-back').on('click', function() {
            goBack();
        });
    </script>
@endsection
