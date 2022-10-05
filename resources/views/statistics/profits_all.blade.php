@extends('layouts.afterlogin')

@section('title', sprintf(trans('statistics.profits.all_title' . $sel_type), $sel_date, $sel_currency))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
@endsection

@section('contents')
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <a type="button" class="text-white btn btn-secondary" href="{{ route('statistics.profits.detail') }}?date={{ $sel_date }}&currency={{ $sel_currency }}&type={{ $sel_type }}">
                    <i class="fa fa-arrow-right"></i>&nbsp;{{ trans('ui.button.back') }}
                </a>
            </div>
        </div>
    </div>

    <!-- users list start -->
    <section class="users-list-wrapper">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="profits-list" class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('statistics.profits.no') }}</th>
                                <th>{{ trans('statistics.profits.userid') }}</th>
                                <th>{{ trans('statistics.profits.nickname') }}</th>
                                @if ($sel_type == SYSTEM_PROFIT_TYPE_WALLET)
                                    <th>{{ trans('statistics.profits.type') }}</th>
                                @endif
                                <th>{{ trans('statistics.profits.profit') }}</th>
                                <th>{{ trans('statistics.profits.occurred_at') }}</th>
                            </tr>
                            </thead>
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
    <script src="{{ cAsset("js/profits-all.js") }}"></script>

    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <?php echo('<script>let ProfitTypeData = ' . json_encode(g_enum('ProfitTypeData')) . ';</script>') ?>
    <script>
        function initTable() {
            let columns = [
                {data: 'id'},
                {data: 'userid'},
                {data: 'nickname'},
            ];
            if ('{{ $sel_type == SYSTEM_PROFIT_TYPE_WALLET }}') {
                columns.push({data: 'type'});
            }
            columns.push({data: 'profit'});
            columns.push({data: 'created_at'});

            listTable = $('#profits-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/statistics/profits/search',
                    type: 'POST',
                    data: {
                        date: '{{ $sel_date }}',
                        currency: '{{ $sel_currency }}',
                        type: '{{ $sel_type }}',
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
                lengthMenu: [[10, 25, 50, 100, 1000, 2500, 5000, 10000], [10, 25, 50, 100, 1000, 2500, 5000, 10000]],
                columnDefs: [],
                columns: columns,
                createdRow: function (row, data, index) {
                    var pageInfo = listTable.page.info();

                    // *********************************************************************
                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    if ('{{ $sel_type == SYSTEM_PROFIT_TYPE_WALLET }}') {
                        $('td', row).eq(3).html('').append(
                            '<span class="text-white badge badge-glow badge-' + ProfitTypeData[data['type']][1] + '">' + ProfitTypeData[data['type']][0] + '</span>'
                        );
                        $('td', row).eq(4).html('').append(
                            _number_format(data['profit'], Math.min('{{ MINIMUM_BALANCE_DECIMALS }}', CryptoSettings[data['currency']]['rate_decimals']))
                        );
                    }
                    else {
                        $('td', row).eq(3).html('').append(
                            _number_format(data['profit'], Math.min('{{ MINIMUM_BALANCE_DECIMALS }}', CryptoSettings[data['currency']]['rate_decimals']))
                        );
                    }
                },
            });
        }
    </script>
@endsection
