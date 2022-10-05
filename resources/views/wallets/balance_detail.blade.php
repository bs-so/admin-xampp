@extends('layouts.afterlogin')

@section('title', sprintf(trans('balance.detail_title'), $sel_currency))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
@endsection

@section('contents')
    <!-- users list start -->
    <section class="users-list-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="form-group">
                    <a type="button" class="text-white btn btn-primary mb-mobile-2" href="{{ route('wallets.balance') }}?currency={{ $sel_currency }}">
                        <i class="fa fa-arrow-left"></i>&nbsp;{{ trans('ui.button.back') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="balance-list" class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('balance.table.no') }}</th>
                                <th>{{ trans('balance.table.currency') }}</th>
                                <th>{{ trans('balance.table.type') }}</th>
                                <th>{{ trans('balance.table.address') }}</th>
                                <th>{{ trans('balance.table.balance') }}</th>
                                <th>{{ trans('balance.table.remark') }}</th>
                                <th>{{ trans('balance.table.actions') }}</th>
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
    <script src="{{ cAsset("js/wallets-balance_detail.js") }}"></script>

    <?php echo('<script>let WalletTypeData = ' . json_encode(g_enum('WalletTypeData')) . ';</script>') ?>
    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <script>
        function refreshBalance(id) {
            showOverlay($('#balance-list'), true, '{{ trans('ui.search.processing') }}');

            $.ajax({
                url: BASE_URL + 'ajax/wallets/refreshBalance',
                type: 'POST',
                data: {
                    id: id,
                    currency: '{{ $sel_currency }}',
                },
                success: function(result) {
                    showOverlay($('#balance-list'), false, '{{ trans('ui.search.processing') }}');
                    showToast('{{ trans('ui.alert.info') }}', '{{ trans('balance.message.success') }}', "success");
                    listTable.ajax.reload();
                },
                error: function(err) {
                    showOverlay($('#balance-list'), false, '{{ trans('ui.search.processing') }}');
                    showToast('{{ trans('ui.alert.info') }}', '{{ trans('balance.message.failed') }}', "warning");
                    console.log(err);
                }
            });
        }

        function initTable() {
            listTable = $('#balance-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/wallets/search',
                    type: 'POST',
                    data: {
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
                },
                lengthMenu: [[10, 25, 50, 100, 1000, 2500, -1], [10, 25, 50, 100, 1000, 2500, "{{ trans('ui.table.all') }}"]],
                columnDefs: [{
                    targets: [6],
                    orderable: false,
                    searchable: false
                }],
                columns: [
                    {data: 'id'},
                    {data: 'currency'},
                    {data: 'type'},
                    {data: 'wallet_address'},
                    {data: 'balance'},
                    {data: 'remark'},
                    {data: null},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = listTable.page.info();

                    // *********************************************************************
                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(2).html('').append(
                        '<span class="text-white badge-glow badge badge-' + WalletTypeData[data['type']][1] + '">' + WalletTypeData[data['type']][0] + '</span>'
                    );

                    $('td', row).eq(4).html('').append(
                        _number_format(data['balance'], CryptoSettings[data['currency']]['rate_decimals'])
                    );

                    $('td', row).eq(6).html('').append(
                        '<a class="btn btn-icon btn-icon-rounded-circle text-success btn-flat-success user-tooltip" href="javascript:refreshBalance(' + data['id'] + ');" title="' + '{{ trans('ui.button.refresh') }}' +'">'
                        + '<i class="fa fa-refresh"></i></a>'
                    );
                },
            });
        }
    </script>
@endsection
