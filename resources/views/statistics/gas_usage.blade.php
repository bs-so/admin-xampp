@extends('layouts.afterlogin')

@section('title', trans('statistics.gas_usage.title'))

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
                                    <label class="form-label">{{ trans('statistics.gas_usage.currency') }}</label>
                                    <select id="filter-currency" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        <option value="ETH">ETH</option>
                                        <option value="USDT">USDT</option>
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('statistics.gas_usage.tx_id') }}</label>
                                    <input type="text" id="filter-tx_id" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('statistics.gas_usage.to_address') }}</label>
                                    <input type="text" id="filter-to_address" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('statistics.gas_usage.remark') }}</label>
                                    <input type="text" id="filter-remark" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('statistics.gas_usage.used_at') }}</label>
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
                    <a type="button" class="text-white btn btn-success" href="{{ route('statistics.gas_usage.download') }}">
                        <i class="fa fa-download"></i>&nbsp;{{ trans('ui.button.download') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex flex-column align-items-start pb-0">
                <?php $CryptoSettings = Session::get('crypto_settings'); ?>
                <h2 class="text-bold-700 text-danger mt-1">{{ _number_format($total_used, $CryptoSettings['ETH']['rate_decimals']) }}&nbsp;(ETH)</h2>
                <p class="mb-0">{{ trans('statistics.gas_usage.total_used') }}</p>
            </div>
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="usage-list" class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('statistics.gas_usage.no') }}</th>
                                <th>{{ trans('statistics.gas_usage.currency') }}</th>
                                <th>{{ trans('statistics.gas_usage.tx_id') }}</th>
                                <th>{{ trans('statistics.gas_usage.to_address') }}</th>
                                <th>{{ trans('statistics.gas_usage.gas_sent') }}</th>
                                <th>{{ trans('statistics.gas_usage.gas_used') }}</th>
                                <th>{{ trans('statistics.gas_usage.remark') }}</th>
                                <th>{{ trans('statistics.gas_usage.used_at') }}</th>
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
    <script src="{{ cAsset("js/gas_usage-list.js") }}"></script>

    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <script>
        function initTable() {
            listTable = $('#usage-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/statistics/gas_usage/search',
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
                columns: [
                    {data: 'id'},
                    {data: 'currency'},
                    {data: 'tx_id'},
                    {data: 'to_address'},
                    {data: 'gas_sent', className: 'text-right'},
                    {data: 'gas_used', className: 'text-right'},
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

                    $('td', row).eq(2).html('').append(
                        getTxLink('ETH', data['tx_id'])
                    );
                    $('td', row).eq(3).html('').append(getReducedStr(data['to_address'], 20, 5));
                    $('td', row).eq(4).html('').append(
                        _number_format(data['gas_sent'], CryptoSettings['ETH']['rate_decimals'])
                    );
                    $('td', row).eq(5).html('').append(
                        _number_format(data['gas_used'], CryptoSettings['ETH']['rate_decimals'])
                    );
                },
            });
        }
    </script>
@endsection
