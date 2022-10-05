@extends('layouts.afterlogin')

@section('title', trans('transfer.system.title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
@endsection

@section('contents')
    <section class="users-list-wrapper">
        <?php
            $cryptoSettings = Session::get('crypto_settings');
        ?>
        @if ($message = Session::get('flash_message'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                {{ trans($message) }}
            </div>
        @endif
        @if ($message = Session::get('error_message'))
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                {{ trans($message) }}
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                {{ trans('affiliate.message.deposit_failed') }}
            </div>
        @endif
        <div class="row match-height">
            <div class="col-sm-12">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#nav-history" aria-controls="history" role="tab" aria-selected="false">{{ trans('transfer.system.navs_history') }}</a>
                    </li>
                    @if (Auth::user()->role != USER_ROLE_AFFILIATE)
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#nav-casino" aria-controls="casino" role="tab" aria-selected="false">{{ trans('transfer.system.navs_casino') }}</a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#nav-affiliate" aria-controls="affiliate" role="tab" aria-selected="false">{{ trans('transfer.system.navs_affiliate') }}</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="nav-history" aria-labelledby="nav-history" role="tabpanel">
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
                                                    <label class="form-label">{{ trans('transfer.system.type') }}</label>
                                                    <select class="form-control" id="filter-type">
                                                        <option value="">{{ trans('ui.search.any') }}</option>
                                                        @foreach (g_enum('SystemBalanceTypeData') as $type => $data)
                                                            @if (in_array($type, $types))
                                                            <option value="{{ $type }}">{{ $data[0] }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md">
                                                    <label class="form-label">{{ trans('transfer.system.direction') }}</label>
                                                    <select class="form-control" id="filter-direction">
                                                        <option value="">{{ trans('ui.search.any') }}</option>
                                                        @foreach (g_enum('TransferDirectionData') as $direction => $data)
                                                            <option value="{{ $direction }}">{{ trans($data[0]) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md">
                                                    <label class="form-label">{{ trans('transfer.system.user') }}</label>
                                                    <input type="text" id="filter-user" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                                </div>
                                                <div class="col-md">
                                                    <label class="form-label">{{ trans('transfer.system.currency') }}</label>
                                                    <select class="form-control" id="filter-currency">
                                                        <option value="">{{ trans('ui.search.any') }}</option>
                                                        @foreach (g_enum('CryptoSettingsData') as $currency => $data)
                                                            <option value="{{ $currency }}">{{ $currency }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md">
                                                    <label class="form-label">{{ trans('transfer.system.status') }}</label>
                                                    <select id="filter-status" class="form-control">
                                                        <option value="">{{ trans('ui.search.any') }}</option>
                                                        @foreach (g_enum('StatusData') as $index => $status)
                                                            <option value="{{ $index }}">{{ $status[0] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md">
                                                    <label class="form-label">{{ trans('transfer.system.created_at') }}</label>
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

                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="transfer-list" class="table">
                                            <thead>
                                            <th>{{ trans('transfer.system.no') }}</th>
                                            <th>{{ trans('transfer.system.operator') }}</th>
                                            <th>{{ trans('transfer.system.type') }}</th>
                                            <th>{{ trans('transfer.system.direction') }}</th>
                                            <th>{{ trans('transfer.system.user') }}</th>
                                            <th>{{ trans('transfer.system.currency') }}</th>
                                            <th>{{ trans('transfer.system.amount') }}</th>
                                            <th>{{ trans('transfer.system.remark') }}</th>
                                            <th>{{ trans('transfer.system.status') }}</th>
                                            <th>{{ trans('transfer.system.created_at') }}</th>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="nav-casino" aria-labelledby="nav-casino" role="tabpanel">
                        <div class="card" id="casino-info-div">
                            <div class="card-header">
                                <h3 class="text-danger">{{ trans('transfer.system.navs_casino') }}</h3>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('system.transfer.casino') }}" method="POST">
                                    @csrf
                                    <div class="form-row">
                                        <div class="form-group col">
                                            <label class="form-label">{{ trans('system.balance.balance1') }}</label>
                                            <input type="text" id="casino-balance1" class="form-control mr-sm-2 mb-2 mb-sm-0" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col">
                                            <label class="form-label">{{ trans('system.balance.balance2') }}</label>
                                            <input type="text" id="casino-balance2" class="form-control mr-sm-2 mb-2 mb-sm-0" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col">
                                            <label class="form-label">{{ trans('transfer.system.currency') }}</label>
                                            <select id="casino-currency" name="currency"
                                                    class="form-control mr-sm-2 mb-2 mb-sm-0 {{ $errors->has('currency') ? 'is-invalid' : '' }}">
                                                <option value="">{{ trans('transfer.system.select_currency') }}</option>
                                                @foreach (g_enum('CryptoSettingsData') as $currency => $data)
                                                    <option value="{{ $currency }}" {{ old('currency') == $currency ? 'selected' : '' }}>{{ $currency }}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('currency'))
                                                <small class="invalid-feedback">{{ $errors->first('currency') }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col">
                                            <label class="form-label">{{ trans('transfer.system.add_amount') }}</label>
                                            <input type="text" name="amount" value="{{ old('amount') }}"
                                                   class="form-control mr-sm-2 mb-2 mb-sm-0 {{ $errors->has('amount') ? 'is-invalid' : '' }}">
                                            @if ($errors->has('amount'))
                                                <small class="invalid-feedback">{{ $errors->first('amount') }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col">
                                            <label class="form-label">{{ trans('transfer.system.remark') }}</label>
                                            <textarea rows="5" type="text" name="remark" class="form-control mr-sm-2 mb-2 mb-sm-0">{{ old('remark') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success">{{ trans('ui.button.update') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="nav-affiliate" aria-labelledby="nav-affiliate" role="tabpanel">
                        <div class="card" id="affiliate-info-div">
                            <div class="card-header">
                                <h3 class="text-danger">{{ trans('transfer.system.navs_affiliate') }}</h3>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('system.transfer.affiliate') }}" method="POST">
                                    @csrf
                                    <div class="form-row">
                                        <div class="form-group col">
                                            <label class="form-label">{{ trans('transfer.system.currency') }}</label>
                                            <select id="affiliate-currency" name="currency"
                                                    class="form-control mr-sm-2 mb-2 mb-sm-0 {{ $errors->has('currency') ? 'is-invalid' : '' }}">
                                                <option value="">{{ trans('transfer.system.select_currency') }}</option>
                                                @foreach (g_enum('CryptoSettingsData') as $currency => $data)
                                                    <option value="{{ $currency }}" {{ old('currency') == $currency ? 'selected' : '' }}>{{ $currency }}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('currency'))
                                                <small class="invalid-feedback">{{ $errors->first('currency') }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col">
                                            <label class="form-label">{{ trans('transfer.system.casino_balance') }}</label>
                                            <input type="text" id="casino-balance" class="form-control mr-sm-2 mb-2 mb-sm-0" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col">
                                            <label class="form-label">{{ trans('transfer.system.user') }}</label>
                                            <input type="text" id="userid" name="userid" class="form-control mr-sm-2 mb-2 mb-sm-0" value="{{ old('userid') }}">
                                            @if ($errors->has('userid'))
                                                <small class="invalid-feedback">{{ $errors->first('userid') }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col">
                                            <label class="form-label">{{ trans('transfer.system.balance') }}</label>
                                            <input type="text" id="affiliate-balance" class="form-control mr-sm-2 mb-2 mb-sm-0" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col">
                                            <label class="form-label">{{ trans('transfer.system.amount') }}</label>
                                            <input type="text" name="amount" value="{{ old('amount') }}"
                                                   class="form-control mr-sm-2 mb-2 mb-sm-0 {{ $errors->has('amount') ? 'is-invalid' : '' }}">
                                            @if ($errors->has('amount'))
                                                <small class="invalid-feedback">{{ $errors->first('amount') }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col">
                                            <label class="form-label">{{ trans('transfer.system.remark') }}</label>
                                            <textarea rows="5" type="text" name="remark" class="form-control mr-sm-2 mb-2 mb-sm-0">{{ old('remark') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success">{{ trans('ui.button.update') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ cAsset('js/system-transfer.js') }}"></script>

    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <?php echo('<script>let SystemBalanceTypeData = ' . json_encode(g_enum('SystemBalanceTypeData')) . ';</script>') ?>
    <?php echo('<script>let TransferDirectionData = ' . json_encode(g_enum('TransferDirectionData')) . ';</script>') ?>
    <?php echo('<script>let StatusData = ' . json_encode(g_enum('StatusData')) . ';</script>') ?>
    <script>
        let minimumBalanceDecimals = '{{ MINIMUM_BALANCE_DECIMALS }}';
        TransferDirectionData['{{ TRANSFER_DIRECTION_IN }}'][0] = '{{ trans('transfer.direction.in') }}';
        TransferDirectionData['{{ TRANSFER_DIRECTION_OUT }}'][0] = '{{ trans('transfer.direction.out') }}';
        TransferDirectionData['{{ TRANSFER_DIRECTION_SET }}'][0] = '{{ trans('transfer.direction.set') }}';

        function initTable() {
            listTable = $('#transfer-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/system/transfer/search',
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
                order: [9, 'desc'],
                columns: [
                    {data: 'id'},
                    {data: 'operator'},
                    {data: 'type'},
                    {data: 'direction'},
                    {data: 'userid'},
                    {data: 'currency'},
                    {data: 'amount', className: "text-right"},
                    {data: 'remark'},
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

                    $('td', row).eq(2).html('').append(
                        '<span class="text-white badge-glow badge badge-' + SystemBalanceTypeData[data['type']][1] + '">' + SystemBalanceTypeData[data['type']][0] + '</span>'
                    );

                    $('td', row).eq(3).html('').append(
                        '<span class="text-white badge-glow badge badge-' + TransferDirectionData[data['direction']][1] + '">' + TransferDirectionData[data['direction']][0] + '</span>'
                    );

                    if (data['userid'] == '' || data['userid'] == null) {
                        $('td', row).eq(4).html('');
                    }
                    else {
                        $('td', row).eq(4).html('').append(
                            '<a class="link" href="' + BASE_URL + 'users/detail?id=' + data['user_id'] + '">' + data['userid'] + '</a>'
                        );
                    }

                    $('td', row).eq(6).html('').append(
                        _number_format(data['amount'], Math.min('{{ MINIMUM_BALANCE_DECIMALS }}', CryptoSettings[data['currency']]['rate_decimals']))
                    );

                    $('td', row).eq(8).html('').append(
                        '<span class="text-white badge-glow badge badge-' + StatusData[data['status']][1] + '">' + StatusData[data['status']][0] + '</span>'
                    );
                },
            });
        }

        $('#casino-currency').on('change', function() {
            let currency = $('#casino-currency').val();
            if (currency == '') return;
            showOverlay($('#casino-info-div'), true, '{{ trans('ui.alert.updating') }}');

            $.ajax({
                url: BASE_URL + 'ajax/system/getBalance',
                type: 'POST',
                data: {
                    type: '{{ SYSTEM_BALANCE_TYPE_CASINO_AUTO }}',
                    currency: currency,
                },
                success: function(result) {
                    $('#casino-balance1').val(_number_format(result, Math.min(minimumBalanceDecimals, CryptoSettings[currency]['rate_decimals'])));

                    $.ajax({
                        url: BASE_URL + 'ajax/system/getBalance',
                        type: 'POST',
                        data: {
                            type: '{{ SYSTEM_BALANCE_TYPE_CASINO_MANUAL }}',
                            currency: currency,
                        },
                        success: function(result) {
                            showOverlay($('#casino-info-div'), false);
                            $('#casino-balance2').val(_number_format(result, Math.min(minimumBalanceDecimals, CryptoSettings[currency]['rate_decimals'])));
                        },
                        error: function(err) {
                            showOverlay($('#casino-info-div'), false);
                            console.log(err);
                        }
                    });
                },
                error: function(err) {
                    showOverlay($('#casino-info-div'), false);
                    console.log(err);
                },
            });
        });
        $('#affiliate-currency').on('change', function() {
            let currency = $('#affiliate-currency').val();
            if (currency == '') return;
            showOverlay($('#affiliate-info-div'), true, '{{ trans('ui.alert.updating') }}');

            $.ajax({
                url: BASE_URL + 'ajax/system/getBalance',
                type: 'POST',
                data: {
                    type: '{{ SYSTEM_BALANCE_TYPE_CASINO_MANUAL }}',
                    currency: currency,
                },
                success: function(result) {
                    $('#casino-balance').val(_number_format(result, Math.min(minimumBalanceDecimals, CryptoSettings[currency]['rate_decimals'])));
                    showOverlay($('#affiliate-info-div'), false);

                    getAffiliateUserBalance();
                },
                error: function(err) {
                    showOverlay($('#affiliate-info-div'), false);
                    console.log(err);
                },
            });
        });

        $('#userid').on('change', function() {
            getAffiliateUserBalance();
        });

        function getAffiliateUserBalance() {
            let currency = $('#affiliate-currency').val();
            let userid = $('#userid').val();

            $.ajax({
                url: BASE_URL + 'ajax/system/getAffiliateBalance',
                type: 'POST',
                data: {
                    currency: currency,
                    userid: userid,
                },
                success: function(result) {
                    $('#affiliate-balance').val(_number_format(result, Math.min(minimumBalanceDecimals, CryptoSettings[currency]['rate_decimals'])));
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }
    </script>
@endsection
