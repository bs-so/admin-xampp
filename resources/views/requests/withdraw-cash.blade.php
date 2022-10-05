@extends('layouts.afterlogin')

@section('title', trans('requests.withdraw_cash.title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
@endsection

@section('contents')
    <!-- users list start -->
    <section class="users-list-wrapper" id="main-obj">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ trans('ui.search.filters') }}</h4>
                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                <div class="heading-elements">
                    <ul class="list-inline mb-0">
                        <li><a data-action="collapse"><i class="feather icon-chevron-down"></i></a></li>
                        <li><a data-action=""><i class="feather icon-rotate-cw users-data-filter"></i></a></li>
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
                                    <label class="form-label">{{ trans('requests.withdraw_cash.userid') }}</label>
                                    <input type="text" id="filter-userid" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('requests.withdraw_cash.nickname') }}</label>
                                    <input type="text" id="filter-nickname" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('requests.withdraw_cash.bank_name') }}</label>
                                    <input type="text" id="filter-bank_name" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('requests.withdraw_cash.type') }}</label>
                                    <select id="filter-type" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('BankTypeData') as $type => $data)
                                            <option value="{{ $type }}">{{ $data[0] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('requests.withdraw_cash.account_number') }}</label>
                                    <input type="text" id="filter-account_number" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('requests.withdraw_cash.status') }}</label>
                                    <select id="filter-status" class="form-control">
                                        <option value="">{{ trans('ui.search.any') }}</option>
                                        @foreach (g_enum('UsersWithdrawCashStatus') as $status => $data)
                                            @if ($status == STATUS_REQUESTED || $status == STATUS_PENDING)
                                            <option value="{{ $status }}">{{ $data[0] }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('requests.withdraw_cash.requested_at') }}</label>
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
        @if ($message = Session::get('flash_message'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                {{ trans($message) }}
            </div>
        @endif
        <div class="row">
            <div class="col-lg-12">
                <div class="form-group">
                    <button type="button" class="btn btn-success text-white" id="btn-withdraw" disabled>
                        <i class="fa fa-check"></i>&nbsp;{{ trans('ui.button.process_complete') }}
                    </button>
                    <button type="button" class="btn btn-warning text-white" id="btn-cancel" disabled>
                        <i class="fa fa-close"></i>&nbsp;{{ trans('ui.button.cancel') }}
                    </button>
                </div>
            </div>
        </div>
        <div class="card" id="request-outline">
            <div class="card-header">
                <div class="col-sm-3">
                    <span class="ml-2 form-label font-weight-bold">{{ trans('requests.withdraw.selected_balance') }}</span>
                    <span id="selected-balance" class="ml-2 text-primary">0</span>
                </div>
            </div>
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="request-list" class="table">
                            <thead>
                            <tr>
                                <th><input id="checkPageItemAll" name="checkPageItemAll" type="checkbox" ></th>
                                <th>{{ trans('requests.withdraw_cash.no') }}</th>
                                <th>{{ trans('requests.withdraw_cash.userid') }}</th>
                                <th>{{ trans('requests.withdraw_cash.nickname') }}</th>
                                <th>{{ trans('requests.withdraw_cash.bank_name') }}</th>
                                <th>{{ trans('requests.withdraw_cash.branch_name') }}</th>
                                <th>{{ trans('requests.withdraw_cash.type') }}</th>
                                <th>{{ trans('requests.withdraw_cash.account_number') }}</th>
                                <th>{{ trans('requests.withdraw_cash.account_name') }}</th>
                                <th>{{ trans('requests.withdraw_cash.amount') }}</th>
                                <th>{{ trans('requests.withdraw_cash.status') }}</th>
                                <th>{{ trans('requests.withdraw_cash.requested_at') }}</th>
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

    <!-- Approve modal -->
    <div class="modal fade" id="modal-approve">
        <div class="modal-dialog">
            <form id="frm-approve" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ trans('requests.withdraw_cash.title') }}
                        <br>
                        <small id="modal-title" class="text-muted">{{ trans('requests.withdraw_cash.approve_title') }}</small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('requests.withdraw_cash.transfer_fee') }}</label>
                            <input type="text" id="transfer_fee" class="form-control mr-sm-2 mb-2 mb-sm-0">
                            <small id="transfer_fee-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i class="fa fa-remove"></i>&nbsp;{{ trans('ui.button.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="btn-submit-approve"><i class="fa fa-check"></i>&nbsp;{{ trans('ui.button.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- / Approve modal -->

    <!-- Cancel modal -->
    <div class="modal fade" id="modal-cancel">
        <div class="modal-dialog">
            <form id="frm-cancel" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ trans('requests.withdraw_cash.title') }}
                        <br>
                        <small id="modal-title" class="text-muted">{{ trans('requests.withdraw_cash.cancel_title') }}</small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('requests.withdraw_cash.remark') }}</label>
                            <textarea rows="5" id="remark" class="form-control mr-sm-2 mb-2 mb-sm-0"></textarea>
                            <small id="remark-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i class="fa fa-remove"></i>&nbsp;{{ trans('ui.button.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="btn-submit-cancel"><i class="fa fa-check"></i>&nbsp;{{ trans('ui.button.submit') }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- / Cancel modal -->
@endsection


@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ cAsset("js/requests-withdraw-cash.js") }}"></script>

    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <?php echo('<script>let WithdrawStatusData = ' . json_encode(g_enum('UsersWithdrawCashStatus')) . ';</script>') ?>
    <?php echo('<script>let BankTypeData = ' . json_encode(g_enum('BankTypeData')) . ';</script>') ?>
    <script>
        let mainCurrencyDecimals = '{{ MAIN_CURRENCY_DECIMALS }}';
        $('#btn-cancel').on('click', function() {
            $('#remark').html('');

            $('#modal-cancel').modal('show');
        });

        $('#btn-withdraw').on('click', function() {
            $('#withdraw-fee').val('');

            //$('#modal-approve').modal('show');
			doApprove();
        });

        $('#btn-submit-cancel').on('click', function() {
            $('#modal-cancel').modal('hide');
            var selected = [];
            var checkboxes = document.getElementsByName('checkItem');
            for (var i=0; i<checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    selected.push(checkboxes[i].value);
                }
            }

            if (!confirm('{{ trans('requests.withdraw_cash.ask_cancel') }}')) {
                return;
            }

            showOverlay($('#main-obj'), true, '{{ trans('ui.alert.just_wait') }}');
            $.ajax({
                url: BASE_URL + 'ajax/requests/withdraw_cash/cancel',
                type: 'POST',
                data: {
                    'selected': selected,
                    'remark': $('#remark').val(),
                },
                success: function(result) {
                    showOverlay($('#main-obj'), false);
                    showToast('{{ trans('requests.withdraw_cash.op_success') }}', '{{ trans('ui.alert.info') }}', "success");
                    listTable.ajax.reload();
                },
                error: function(err) {
                    showOverlay($('#main-obj'), false);
                    showToast('{{ trans('requests.withdraw_cash.op_failed') }}', '{{ trans('ui.alert.info') }}', "warning");
                }
            });
        });

        $('#btn-submit-approve').on('click', function() {
			doApprove();
		});

		function doApprove() {
            var selected = [];
            var checkboxes = document.getElementsByName('checkItem');
            for (var i=0; i<checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    selected.push(checkboxes[i].value);
                }
            }

            if (!confirm('{{ trans('requests.withdraw_cash.ask_approve') }}')) {
                return;
            }

            //$('#modal-approve').modal('hide');
            showOverlay($('#main-obj'), true, '{{ trans('ui.alert.just_wait') }}');
            $.ajax({
                url: BASE_URL + 'ajax/requests/withdraw_cash/approve',
                type: 'POST',
                data: {
                    'selected': selected,
                    'transfer_fee': 0,//$('#transfer_fee').val(),
                },
                success: function(result) {
                    showOverlay($('#main-obj'), false);
                    showToast('{{ trans('requests.withdraw_cash.op_success') }}', '{{ trans('ui.alert.info') }}', "success");
                    listTable.ajax.reload();
                },
                error: function(err) {
                    showOverlay($('#main-obj'), false);
                    showToast('{{ trans('requests.withdraw_cash.op_failed') }}', '{{ trans('ui.alert.info') }}', "warning");
                }
            });
        }

        function initTable() {
            listTable = $('#request-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/requests/withdraw_cash/search',
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
                order: [1, 'asc'],
                columnDefs: [{
                    targets: [0],
                    orderable: false,
                    searchable: false
                }],
                columns: [
                    {data: null},
                    {data: 'id'},
                    {data: 'userid'},
                    {data: 'user_name'},
                    {data: 'bank_name'},
                    {data: 'branch_name'},
                    {data: 'type'},
                    {data: 'account_number'},
                    {data: 'account_name'},
                    {data: 'amount', className: "text-right"},
                    {data: 'status'},
                    {data: 'created_at'},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = listTable.page.info();

                    $('td', row).eq(0).html('').append(
                        '<input name="checkItem" type="checkbox" value="' + data['id'] + '">'
                    );

                    $('td', row).eq(1).html('').append(
                        '<span>' + (pageInfo.page * pageInfo.length + index + 1) + '</span>'
                    );

                    $('td', row).eq(6).html('').append(
                        '<span class="text-white badge badge-glow badge-' + BankTypeData[data['type']][1] + '">' + BankTypeData[data['type']][0] + '</span>'
                    );

                    $('td', row).eq(9).html('').append(
                        _number_format(BigNumber(data['amount']).multipliedBy(BigNumber((100 - '{{ $withdrawFee }}') / 100)).toNumber(), '{{ MAIN_CURRENCY_DECIMALS }}')
                    );
                    $('td', row).eq(9).attr('id', "amount_" + data['id']);

                    $('td', row).eq(10).html('').append(
                        '<span class="text-white badge badge-glow badge-' + WithdrawStatusData[data['status']][1] + '">' + WithdrawStatusData[data['status']][0] + '</span>'
                    );
                },
            });
        }
    </script>
@endsection
