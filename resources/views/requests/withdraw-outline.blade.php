@extends('layouts.afterlogin')

@section('title', trans('requests.withdraw.title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
@endsection

@section('contents')
    <!-- users list start -->
    <section class="users-list-wrapper">
        <div class="card" id="request-outline">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="request-outline-list" class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('requests.withdraw.currency') }}</th>
                                <th>{{ trans('requests.withdraw.request_count') }}</th>
                                <th>{{ trans('requests.withdraw.request_amount') }}</th>
                                <th>{{ trans('requests.withdraw.queue_count') }}</th>
                                <th>{{ trans('requests.withdraw.queue_amount') }}</th>
                                <th>{{ trans('requests.withdraw.failed_count') }}</th>
                                <th>{{ trans('requests.withdraw.failed_amount') }}</th>
                                <th>{{ trans('requests.withdraw.action') }}</th>
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
    <script src="{{ cAsset("js/requests-withdraw-outline.js") }}"></script>
    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <script>
        function initTable() {
            listTable = $('#request-outline-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/traderWithdraw/request-outline',
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
                columnDefs: [{
                    targets: [7],
                    orderable: false,
                    searchable: false
                }],
                columns: [
                    {data: 'currency'},
                    {data: 'withdraw_count'},
                    {data: 'withdraw_sum'},
                    {data: 'processing_count'},
                    {data: 'processing_sum'},
                    {data: 'failed_count'},
                    {data: 'failed_sum'},
                    {data: null},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = listTable.page.info();

                    // *********************************************************************
                    $('td', row).eq(2).html('').append(
                        _number_format(data['withdraw_sum'], CryptoSettings[data['currency']]['rate_decimals'])
                    );

                    $('td', row).eq(4).html('').append(
                        _number_format(data['processing_sum'], CryptoSettings[data['currency']]['rate_decimals'])
                    );

                    $('td', row).eq(6).html('').append(
                        _number_format(data['failed_sum'], CryptoSettings[data['currency']]['rate_decimals'])
                    );

                    $('td', row).eq(7).html('').append('<a class="btn btn-icon btn-icon-rounded-circle text-info btn-flat-info user-tooltip" href="' + BASE_URL + 'traderWithdraw/req-list?id=' +  data["currency"] + '" title="Detail">'
                        + '<i class="fa fa-edit"></i></a>'
                    );
                },
            });
        }
    </script>
@endsection
