@extends('layouts.afterlogin')

@section('title', trans('coldwallet.deposit.title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
@endsection

@section('contents')
    <section class="users-list-wrapper">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="deposit-wallet-list" class="table">
                            <thead>
                            <tr>
                                <th>{{ trans('coldwallet.deposit.no') }}</th>
                                <th>{{ trans('coldwallet.deposit.currency') }}</th>
                                <th>{{ trans('coldwallet.deposit.wallet_address') }}</th>
                                <th>{{ trans('coldwallet.deposit.actions') }}</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal template -->
    <div class="modal fade" id="modal-qrcode">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm">
            <form id="frm-modal" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-header bg-info">
                    <h5 class="modal-title">
                        <span class="text-white">{{ trans('coldwallet.deposit.title') }}</span>
                        <br>
                        <small id="currency" class="text-white"></small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-row text-center">
                        <div class="form-group col">
                            <img id="qr-code">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-info" data-dismiss="modal"><i class="fa fa-remove"></i>&nbsp;{{ trans('ui.button.cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection


@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ cAsset('js/__common.js') }}"></script>
    <script src="{{ cAsset("js/deposit-list.js") }}"></script>
    <script>
        function initTable() {
            listTable = $('#deposit-wallet-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/deposit/wallets',
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
                },
                lengthMenu: [[10, 25, 50, 100, 1000, 2500, -1], [10, 25, 50, 100, 1000, 2500, "{{ trans('ui.table.all') }}"]],
                columnDefs: [{
                    targets: [3],
                    orderable: false,
                    searchable: false
                }],
                columns: [
                    {data: 'id'},
                    {data: 'currency'},
                    {data: 'wallet_address'},
                    {data: null},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = listTable.page.info();

                    // Index
                    $('td', row).eq(0).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(3).html('').append(
                        _number_format(data['balance'], data['currency'])
                    );

                    $('td', row).eq(3).html('').append(
                        '<a class="btn btn-icon btn-icon-rounded-circle text-primary btn-flat-primary user-tooltip" href="javascript:showDetail(' + data['id'] + ');" title="Detail">'
                        + '<i class="fa fa-edit"></i></a>'
                    );
                },
            });
        }

        function showDetail(id) {
            $.ajax({
                url: BASE_URL + 'ajax/deposit/getQRCode',
                type: 'POST',
                data: {
                    id: id,
                },
                success: function(result) {
                    $('#currency').html(result['currency']);
                    $('#qr-code').attr('src', 'data:image/png;base64, ' + result['qr_code']);
                    $('#modal-qrcode').modal('show');
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }
    </script>
@endsection
