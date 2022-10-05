@extends('layouts.afterlogin')

@section('title', trans('transfer.users.title_history'))

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

        <div class="row">
            <div class="col-lg-12">
                <div class="form-group">
                    <a type="button" class="text-white btn btn-success" href="{{ route('users.transfer.download') }}">
                        <i class="fa fa-download"></i>&nbsp;{{ trans('ui.button.download') }}
                    </a>
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
                            <th>{{ trans('transfer.users.sender') }}</th>
                            <th>{{ trans('transfer.users.receiver') }}</th>
                            <th>{{ trans('transfer.users.currency') }}</th>
                            <th>{{ trans('transfer.users.amount') }}</th>
                            <th>{{ trans('transfer.users.fee') }}</th>
                            <th>{{ trans('transfer.users.remark') }}</th>
                            <th>{{ trans('transfer.users.created_at') }}</th>
                            </thead>
                        </table>
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
                        is_staff: 0,
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
                order: [7, 'desc'],
                columns: [
                    {data: 'id'},
                    {data: 'sender'},
                    {data: 'receiver'},
                    {data: 'currency'},
                    {data: 'amount', className: "text-right"},
                    {data: 'fee', className: "text-right"},
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

                    if (data['sender_id'] == 0) {
                        $('td', row).eq(1).html('').append('');
                    }
                    else {
                        $('td', row).eq(1).html('').append(
                            '<a class="link" href="' + BASE_URL + 'users/detail?id=' + data['sender_id'] + '">' + data['sender'] + '</a>'
                        );
                    }
                    $('td', row).eq(2).html('').append(
                        '<a class="link" href="' + BASE_URL + 'users/detail?id=' + data['receiver_id'] + '">' + data['receiver'] + '</a>'
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
    </script>
@endsection
