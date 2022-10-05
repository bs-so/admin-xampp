@extends('layouts.afterlogin')

@section('title', trans('users-history.exchange.title'))

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
                                    <label class="form-label">{{ trans('users-history.exchange.userid') }}</label>
                                    <input type="text" id="filter-userid" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users-history.exchange.user_name') }}</label>
                                    <input type="text" id="filter-nickname" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users-history.exchange.currency_from') }}</label>
                                    <select id="filter-currency_from" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        <option value="{{ MAIN_CURRENCY }}">{{ MAIN_CURRENCY }}</option>
                                        @foreach (g_enum('CryptoSettingsData') as $currency => $data)
                                            <option value="{{ $currency }}">{{ $currency }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users-history.exchange.currency_to') }}</label>
                                    <select id="filter-currency_to" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        <option value="{{ MAIN_CURRENCY }}">{{ MAIN_CURRENCY }}</option>
                                        @foreach (g_enum('CryptoSettingsData') as $currency => $data)
                                            <option value="{{ $currency }}">{{ $currency }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('users-history.exchange.status') }}</label>
                                    <select id="filter-status" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('UsersExchangeStatus') as $status => $value)
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
            echo 'var UsersExchangeStatus = ' . json_encode(g_enum('UsersExchangeStatus')) . ';';
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
                    <a type="button" class="text-white btn btn-success" href="{{ route('users.history.download') }}?type=1">
                        <i class="fa fa-download"></i>&nbsp;{{ trans('ui.button.download') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="exchange-list" class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('users-history.exchange.no') }}</th>
                                <th>{{ trans('users-history.exchange.userid') }}</th>
                                <th>{{ trans('users-history.exchange.user_name') }}</th>
                                <th>{{ trans('users-history.exchange.currency_from') }}</th>
                                <th>{{ trans('users-history.exchange.currency_to') }}</th>
                                <th>{{ trans('users-history.exchange.amount_total') }}</th>
                                <th>{{ trans('users-history.exchange.exchange_fee') }}</th>
                                <th>{{ trans('users-history.exchange.amount_exch') }}</th>
                                <th>{{ trans('users-history.exchange.amount_to') }}</th>
                                <th>{{ trans('users-history.exchange.ex_rate') }}</th>
                                <th>{{ trans('users-history.exchange.status') }}</th>
                                <th>{{ trans('users-history.exchange.reged_at') }}</th>
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
    <script src="{{ cAsset("js/users-exchange-history.js") }}"></script>

    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <script>
        function initTable() {
            listTable = $('#exchange-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/users/history/exchange/search',
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
                    {data: 'currency_from'},
                    {data: 'currency_to'},
                    {data: 'amount_total', className: 'text-right'},
                    {data: 'exchange_fee', className: 'text-right'},
                    {data: 'amount_exch', className: 'text-right'},
                    {data: 'amount_to', className: 'text-right'},
                    {data: 'ex_rate', className: 'text-right'},
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
                        '<span class="user-tooltip" title="' + '{{ trans('ui.unit.name') }}' + ' : ' + data['currency_from'] + '">' +
                            _number_format(data['amount_total'], CryptoSettings[data['currency_from']]['rate_decimals']) +
                        '</span>'
                    );
                    $('td', row).eq(6).html('').append(
                        '<span class="user-tooltip" title="' + '{{ trans('ui.unit.name') }}' + ' : ' + data['currency_from'] + '">' +
                        _number_format(data['exchange_fee'], CryptoSettings[data['currency_from']]['rate_decimals']) +
                        '</span>'
                    );
                    $('td', row).eq(7).html('').append(
                        '<span class="user-tooltip" title="' + '{{ trans('ui.unit.name') }}' + ' : ' + data['currency_from'] + '">' +
                            _number_format(data['amount_exch'], CryptoSettings[data['currency_from']]['rate_decimals']) +
                        '</span>'
                    );
                    $('td', row).eq(8).html('').append(
                        '<span class="user-tooltip" title="' + '{{ trans('ui.unit.name') }}' + ' : ' + data['currency_to'] + '">' +
                            _number_format(data['amount_to'], CryptoSettings[data['currency_to']]['rate_decimals']) +
                        '</span>'
                    );
                    $('td', row).eq(9).html('').append(
                        _number_format(data['ex_rate'], '{{ EX_RATE_DECIMALS }}')
                    );

                    $('td', row).eq(10).html('').append(
                        '<span class="text-white badge-glow badge badge-' + UsersExchangeStatus[data['status']][1] + '">' + UsersExchangeStatus[data['status']][0] + '</span>'
                    );
                },
            });
        }
    </script>
@endsection
