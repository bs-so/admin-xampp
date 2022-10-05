@extends('layouts.afterlogin')

@section('title', trans('transfer.users.title'))

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
                        <a class="nav-link active" data-toggle="tab" href="#nav-history" aria-controls="history" role="tab" aria-selected="false">{{ trans('transfer.users.navs_history') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#nav-request" aria-controls="request" role="tab" aria-selected="false">{{ trans('transfer.users.navs_request') }}</a>
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
                                                    <label class="form-label">{{ trans('transfer.users.sender') }}</label>
                                                    <input type="text" id="filter-sender" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                                </div>
                                                <div class="col-md">
                                                    <label class="form-label">{{ trans('transfer.users.receiver') }}</label>
                                                    <input type="text" id="filter-receiver" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                                </div>
                                                <div class="col-md">
                                                    <label class="form-label">{{ trans('transfer.users.currency') }}</label>
                                                    <select class="form-control" id="filter-currency">
                                                        <option value="">{{ trans('ui.search.any') }}</option>
                                                        @foreach (g_enum('CryptoSettingsData') as $currency => $data)
                                                            <option value="{{ $currency }}">{{ $currency }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md">
                                                    <label class="form-label">{{ trans('transfer.users.status') }}</label>
                                                    <select id="filter-status" class="form-control">
                                                        <option value="">{{ trans('ui.search.any') }}</option>
                                                        @foreach (g_enum('StatusData') as $index => $status)
                                                            <option value="{{ $index }}">{{ $status[0] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md">
                                                    <label class="form-label">{{ trans('transfer.users.created_at') }}</label>
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
                                            <th>{{ trans('transfer.users.no') }}</th>
                                            <th>{{ trans('transfer.users.operator') }}</th>
                                            <th>{{ trans('transfer.users.sender') }}</th>
                                            <th>{{ trans('transfer.users.receiver') }}</th>
                                            <th>{{ trans('transfer.users.currency') }}</th>
                                            <th>{{ trans('transfer.users.amount') }}</th>
                                            <th>{{ trans('transfer.users.remark') }}</th>
                                            <th>{{ trans('transfer.users.status') }}</th>
                                            <th>{{ trans('transfer.users.created_at') }}</th>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="nav-request" aria-labelledby="nav-request" role="tabpanel">
                        <div class="card" id="request-info-div">
                            <div class="card-header">
                                <h3 class="text-danger">{{ trans('transfer.users.navs_request') }}</h3>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('users.transfer.request') }}" method="POST">
                                    @csrf
                                    <div class="form-row">
                                        <div class="form-group col">
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-inline-block mr-2">
                                                    <fieldset>
                                                        <div class="vs-radio-con">
                                                            <input type="radio" name="type" checked="" value="1">
                                                            <span class="vs-radio">
                                                                <span class="vs-radio--border"></span>
                                                                <span class="vs-radio--circle"></span>
                                                            </span>
                                                            <span class="">{{ trans('transfer.users.from_system') }}</span>
                                                        </div>
                                                    </fieldset>
                                                </li>
                                                <li class="d-inline-block mr-2">
                                                    <fieldset>
                                                        <div class="vs-radio-con">
                                                            <input type="radio" name="type" value="2">
                                                            <span class="vs-radio">
                                                                <span class="vs-radio--border"></span>
                                                                <span class="vs-radio--circle"></span>
                                                            </span>
                                                            <span class="">{{ trans('transfer.users.from_user') }}</span>
                                                        </div>
                                                    </fieldset>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="form-row" id="casino-balance-div">
                                        <div class="form-group col">
                                            <label class="form-label">{{ trans('transfer.users.casino_balance') }}</label>
                                            <input type="text" id="casino-balance" class="form-control mr-sm-2 mb-2 mb-sm-0" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row show-none" id="sender-balance-div">
                                        <div class="form-group col">
                                            <label class="form-label">{{ trans('transfer.users.sender_balance') }}</label>
                                            <input type="text" id="sender-balance" class="form-control mr-sm-2 mb-2 mb-sm-0" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row show-none" id="sender-div">
                                        <div class="form-group col">
                                            <label class="form-label">{{ trans('transfer.users.sender') }}</label>
                                            <input type="text" id="sender" name="sender" value="{{ old('sender') }}"
                                                   class="form-control mr-sm-2 mb-2 mb-sm-0 {{ $errors->has('sender') ? 'is-invalid' : '' }}">
                                            @if ($errors->has('sender'))
                                                <small class="invalid-feedback">{{ $errors->first('sender') }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col">
                                            <label class="form-label">{{ trans('transfer.users.receiver') }}</label>
                                            <input type="text" name="receiver" value="{{ old('receiver') }}"
                                                   class="form-control mr-sm-2 mb-2 mb-sm-0 {{ $errors->has('receiver') ? 'is-invalid' : '' }}">
                                            @if ($errors->has('receiver'))
                                                <small class="invalid-feedback">{{ $errors->first('receiver') }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col">
                                            <label class="form-label">{{ trans('transfer.users.currency') }}</label>
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
                                            <label class="form-label">{{ trans('transfer.users.amount') }}</label>
                                            <input type="text" name="amount" value="{{ old('amount') }}"
                                                   class="form-control mr-sm-2 mb-2 mb-sm-0 {{ $errors->has('amount') ? 'is-invalid' : '' }}">
                                            @if ($errors->has('amount'))
                                                <small class="invalid-feedback">{{ $errors->first('amount') }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col">
                                            <label class="form-label">{{ trans('transfer.users.remark') }}</label>
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
    <script src="{{ cAsset('js/users-transfer.js') }}"></script>

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
                    url: BASE_URL + 'ajax/users/transfer/search',
                    type: 'POST',
                    data: {
                        is_staff: 1,
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
                columnDefs: [],
                order: [8, 'desc'],
                columns: [
                    {data: 'id'},
                    {data: 'operator'},
                    {data: 'sender'},
                    {data: 'receiver'},
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

                    if (data['staff_id'] == 0) {
                        $('td', row).eq(1).html('').append('');
                    }

                    if (data['sender_id'] == 0) {
                        $('td', row).eq(2).html('').append('');
                    }
                    else {
                        $('td', row).eq(2).html('').append(
                            '<a class="link" href="' + BASE_URL + 'users/detail?id=' + data['sender_id'] + '">' + data['sender'] + '</a>'
                        );
                    }
                    $('td', row).eq(3).html('').append(
                        '<a class="link" href="' + BASE_URL + 'users/detail?id=' + data['receiver_id'] + '">' + data['receiver'] + '</a>'
                    );

                    $('td', row).eq(5).html('').append(
                        _number_format(data['amount'], Math.min('{{ MINIMUM_BALANCE_DECIMALS }}', CryptoSettings[data['currency']]['rate_decimals']))
                    );

                    $('td', row).eq(7).html('').append(
                        '<span class="text-white badge-glow badge badge-' + StatusData[data['status']][1] + '">' + StatusData[data['status']][0] + '</span>'
                    );
                },
            });
        }

        $('[name=type]').on('click', function() {
            let type = $('[name=type]:checked').val();

            $('#request-info-div').find('div').removeClass('show-block');
            $('#request-info-div').find('div').removeClass('show-none');
            if (type == 1) {
                // From system
                $('#casino-balance-div').addClass('show-block');
                $('#sender-balance-div').addClass('show-none');
                $('#sender-div').addClass('show-none');
            }
            else {
                // From user
                $('#casino-balance-div').addClass('show-none');
                $('#sender-balance-div').addClass('show-block');
                $('#sender-div').addClass('show-block');
            }

            getBalance();
        });

        $('#sender').on('change', function() {
            getBalance();
        });
        $('#casino-currency').on('change', function() {
            let currency = $('#casino-currency').val();
            if (currency == '') return;
            getBalance();
        });

        function getBalance() {
            let type = $('[name=type]:checked').val();

            if (type == 1) {
                // Get casino balance
                getCasinoBalance();
            }
            else {
                getSenderBalance();
            }
        }

        function getCasinoBalance() {
            let currency = $('#casino-currency').val();
            if (currency == '') return;
            showOverlay($('#request-info-div'), true, '{{ trans('ui.alert.updating') }}');

            $.ajax({
                url: BASE_URL + 'ajax/system/getBalance',
                type: 'POST',
                data: {
                    type: '{{ SYSTEM_BALANCE_TYPE_CASINO_MANUAL }}',
                    currency: currency,
                },
                success: function (result) {
                    showOverlay($('#request-info-div'), false);
                    $('#casino-balance').val(_number_format(result, Math.min(minimumBalanceDecimals, CryptoSettings[currency]['rate_decimals'])));
                },
                error: function (err) {
                    showOverlay($('#request-info-div'), false);
                    console.log(err);
                }
            });
        }

        function getSenderBalance() {
            let sender = $('#sender').val();
            let currency = $('#casino-currency').val();
            if (currency == '') return;
            showOverlay($('#request-info-div'), true, '{{ trans('ui.alert.updating') }}');

            $.ajax({
                url: BASE_URL + 'ajax/users/getBalance',
                type: 'POST',
                data: {
                    sender: sender,
                    currency: currency,
                },
                success: function (result) {
                    showOverlay($('#request-info-div'), false);
                    $('#sender-balance').val(_number_format(result, Math.min(minimumBalanceDecimals, CryptoSettings[currency]['rate_decimals'])));
                },
                error: function (err) {
                    showOverlay($('#request-info-div'), false);
                    console.log(err);
                }
            });
        }
    </script>
@endsection
