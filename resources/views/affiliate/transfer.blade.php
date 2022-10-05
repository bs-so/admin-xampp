@extends('layouts.afterlogin')

@section('title', trans('affiliate.transfer.title'))

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
            </div>
            <div class="card-content collapse show">
                <div class="card-body">
                    <div class="users-list-filter">
                        <form>
                            <div class="row">
                                <div class="col-md">
                                    <label class="form-label">{{ trans('affiliate.transfer.staff') }}</label>
                                    <input type="text" id="filter-staff" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('affiliate.transfer.type') }}</label>
                                    <select id="filter-type" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('TransferTypeData') as $type => $data)
                                            <option value="{{ $type }}">{{ trans($data[0]) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('affiliate.transfer.currency') }}</label>
                                    <select id="filter-currency" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('CryptoSettingsData') as $currency => $data)
                                            <option value="{{ $currency }}">{{ $currency }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('affiliate.transfer.transfered_at') }}</label>
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
        echo 'var StaffDepositStatus = ' . json_encode(g_enum('StaffDepositStatus')) . ';';
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
                    <a type="button" class="text-white btn btn-success" href="{{ route('affiliate.transfer.download') }}">
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
                            <tr>
                                <th>{{ trans('affiliate.transfer.no') }}</th>
                                <th>{{ trans('affiliate.transfer.staff') }}</th>
                                <th>{{ trans('affiliate.transfer.type') }}</th>
                                <th>{{ trans('affiliate.transfer.currency') }}</th>
                                <th>{{ trans('affiliate.transfer.amount') }}</th>
                                <th>{{ trans('affiliate.transfer.remark') }}</th>
                                <th>{{ trans('affiliate.transfer.transfered_at') }}</th>
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
    <script src="{{ cAsset("js/affiliate-transfer.js") }}"></script>

    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <?php echo('<script>let TransferTypeData = ' . json_encode(g_enum('TransferTypeData')) . ';</script>') ?>
    <script>
        TransferTypeData['{{ TRANSFER_TYPE_IN }}'][0] = '{{ trans('transfer.type.in') }}';
        TransferTypeData['{{ TRANSFER_TYPE_OUT }}'][0] = '{{ trans('transfer.type.out') }}';

        function initTable() {
            listTable = $('#transfer-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/affiliate/transfer/search',
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
                order: [5, 'desc'],
                columns: [
                    {data: 'id'},
                    {data: 'staff'},
                    {data: 'type'},
                    {data: 'currency'},
                    {data: 'amount', className: 'text-right'},
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
                        '<span class="text-white badge-glow badge badge-' + TransferTypeData[data['type']][1] + '">' + TransferTypeData[data['type']][0] + '</span>'
                    );
                    $('td', row).eq(4).html('').append(
                        _number_format(data['amount'], CryptoSettings[data['currency']]['rate_decimals'])
                    );
                    $('td', row).eq(5).html('').append(
                        '<p>' + data['remark'].replace("\r\n", "<br>") + '</p>'
                    );
                },
            });
        }
    </script>
@endsection
