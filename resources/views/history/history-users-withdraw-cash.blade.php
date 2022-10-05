@extends('layouts.afterlogin')

@section('title', trans('users-history.withdraw_cash.title'))

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
                                    <label class="form-label">{{ trans('users-history.withdraw_cash.userid') }}</label>
                                    <input type="text" id="filter-userid" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users-history.withdraw_cash.user_name') }}</label>
                                    <input type="text" id="filter-nickname" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users-history.withdraw_cash.bank_name') }}</label>
                                    <input type="text" id="filter-bank_name" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users-history.withdraw_cash.type') }}</label>
                                    <select id="filter-type" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('BankTypeData') as $type => $value)
                                            <option value="{{ $type }}">{{ $value[0] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users-history.withdraw_cash.account_number') }}</label>
                                    <input type="text" id="filter-account_number" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users-history.withdraw_cash.status') }}</label>
                                    <select id="filter-status" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('UsersWithdrawCashStatus') as $status => $value)
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

        <?php
            echo '<script>';
            echo 'var UsersWithdrawCashStatus = ' . json_encode(g_enum('UsersWithdrawCashStatus')) . ';';
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
                    <a type="button" class="text-white btn btn-success" href="{{ route('users.history.download') }}?type=3">
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
                                <th>{{ trans('users-history.withdraw_cash.no') }}</th>
                                <th>{{ trans('users-history.withdraw_cash.userid') }}</th>
                                <th>{{ trans('users-history.withdraw_cash.user_name') }}</th>
                                <th>{{ trans('users-history.withdraw_cash.bank_name') }}</th>
                                <th>{{ trans('users-history.withdraw_cash.branch_name') }}</th>
                                <th>{{ trans('users-history.withdraw_cash.type') }}</th>
                                <th>{{ trans('users-history.withdraw_cash.account_number') }}</th>
                                <th>{{ trans('users-history.withdraw_cash.account_name') }}</th>
                                <th>{{ trans('users-history.withdraw_cash.amount') }}</th>
                                <th>{{ trans('users-history.withdraw_cash.withdraw_fee') }}</th>
                                <th>{{ trans('users-history.withdraw_cash.transfer_fee') }}</th>
                                <th>{{ trans('users-history.withdraw_cash.status') }}</th>
                                <th>{{ trans('users-history.withdraw_cash.reged_at') }}</th>
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
    <script src="{{ cAsset("js/users-withdraw-cash-history.js") }}"></script>

    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <?php echo('<script>let BankTypeData = ' . json_encode(g_enum('BankTypeData')) . ';</script>') ?>
    <script>
        function initTable() {
            listTable = $('#withdraw-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/users/history/withdraw_cash/search',
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
				order: [12, 'desc'],
                columns: [
                    {data: 'id'},
                    {data: 'userid'},
                    {data: 'user_name'},
                    {data: 'bank_name'},
                    {data: 'branch_name'},
                    {data: 'type'},
                    {data: 'account_number'},
                    {data: 'account_name'},
                    {data: 'amount', className: 'text-right'},
                    {data: 'withdraw_fee', className: 'text-right'},
                    {data: 'transfer_fee', className: 'text-right'},
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

                    $('td', row).eq(5).html('').append(
                        '<span class="text-white badge-glow badge badge-' + BankTypeData[data['type']][1] + '">' + BankTypeData[data['type']][0] + '</span>'
                    );

                    $('td', row).eq(8).html('').append(
                        _number_format(data['amount'], CryptoSettings['{{ MAIN_CURRENCY }}']['rate_decimals'])
                    );
                    $('td', row).eq(9).html('').append(
                        _number_format(data['withdraw_fee'], CryptoSettings['{{ MAIN_CURRENCY }}']['rate_decimals'])
                    );
                    $('td', row).eq(10).html('').append(
                        _number_format(data['transfer_fee'], CryptoSettings['{{ MAIN_CURRENCY }}']['rate_decimals'])
                    );

                    $('td', row).eq(11).html('').append(
                        '<span class="text-white badge-glow badge badge-' + UsersWithdrawCashStatus[data['status']][1] + '">' + UsersWithdrawCashStatus[data['status']][0] + '</span>'
                    );
                },
            });
        }
    </script>
@endsection
