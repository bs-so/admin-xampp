@extends('layouts.afterlogin')

@section('title', sprintf(trans('requests.withdraw.detail_title'), $currency))

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
            <input type="hidden" id="currency" value="<?php echo $currency ?>">
            <div class="card-content collapse show">
                <div class="card-body">
                    <div class="users-list-filter">
                        <form>
                            <div class="row">
                                <div class="col-md">
                                    <label class="form-label">{{ trans('requests.withdraw.userid') }}</label>
                                    <input type="text" id="filter-userid" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('requests.withdraw.email') }}</label>
                                    <input type="text" id="filter-email" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('requests.withdraw.recv_address') }}</label>
                                    <input type="text" id="filter-address" class="form-control" placeholder="{{ trans('ui.search.any') }}">
                                </div>
                                <div class="col-md">
                                    <label class="form-label">{{ trans('requests.withdraw.requested_time') }}</label>
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
                        <i class="fa fa-check"></i>&nbsp;{{ trans('ui.button.approve') }}
                    </button>
                    <button type="button" class="btn btn-warning text-white" id="btn-cancel" disabled>
                        <i class="fa fa-close"></i>&nbsp;{{ trans('ui.button.cancel') }}
                    </button>
                    <a class="btn btn-light" href="{{ route('traderwithdraw.request-outline') }}">
                        <span class="fa fa-arrow-left"></span>&nbsp;{{ trans('ui.button.back') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="card" id="request-outline">
            <div class="card-header">
                <div class="col-sm-3">
                    <span class="ml-2 form-label font-weight-bold">{{ trans('requests.withdraw.wt_balance') }}</span>
                    <span id="wt-balance" class="ml-2 text-danger">0</span>
                </div>
                @if ($currency == 'ETH' || $currency == 'USDT')
                    <div class="col-sm-3">
                        <span class="ml-2 form-label font-weight-bold">{{ trans('requests.withdraw.gt_balance') }}</span>
                        <span id="gastank-balance" class="ml-2 text-danger">0</span>
                    </div>
                @endif
                <div class="col-sm-3">
                <span class="ml-2 form-label font-weight-bold">{{ trans('requests.withdraw.selected_balance') }}</span>
                <span id="selected-balance" class="ml-2 text-primary">0</span>
                </div>
                @if ($currency == 'ETH' || $currency == 'USDT')
                    <div class="col-sm-3">
                        <span class="ml-2 form-label font-weight-bold">{{ trans('requests.withdraw.need_gas') }}</span>
                        <span id="need-gas" class="ml-2 text-primary">0</span>
                    </div>
                @endif
            </div>
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="request-list" class="table">
                            <thead>
                            <tr>
                                <th><input id="checkPageItemAll" name="checkPageItemAll" type="checkbox" ></th>
                                <th>{{ trans('requests.withdraw.no') }}</th>
                                <th>{{ trans('requests.withdraw.userid') }}</th>
                                <th>{{ trans('requests.withdraw.nickname') }}</th>
                                <th>{{ trans('requests.withdraw.email') }}</th>
                                <th>{{ trans('requests.withdraw.recv_address') }}</th>
                                <th>{{ trans('requests.withdraw.withdraw_amount') }}</th>
                                <th>{{ trans('requests.withdraw.requested_time') }}</th>
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

    <!-- Cancel modal -->
    <div class="modal fade" id="modal-cancel">
        <div class="modal-dialog">
            <form id="frm-cancel" class="modal-content">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ trans('requests.withdraw_cash.title') }}
                        <br>
                        <small id="modal-title" class="text-muted">{{ trans('requests.withdraw.cancel_title') }}</small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col">
                            <label class="form-label">{{ trans('requests.withdraw.remark') }}</label>
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
    <script src="{{ cAsset("js/requests-withdraw-list.js") }}"></script>
    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <script>
        let currency = '{{ $currency }}';
        function getBalances() {
            showOverlay($('#main-obj'), true, "{{ trans('ui.alert.just_wait') }}");

            $.ajax({
                url: BASE_URL + 'ajax/withdraw/getWalletBalances',
                type: 'POST',
                data: {
                    currency: '{{ $currency }}',
                },
                success: function(result) {
                    showOverlay($('#main-obj'), false);
                    if (result == 1) {
                        showToast("{{ trans('requests.withdraw.no_wallet') }}", '{{ trans('ui.alert.info') }}', "warning");
                    }
                    else if (result >= 2) {
                        showToast("{{ trans('requests.withdraw.get_balance_failed') }}", '{{ trans('ui.alert.info') }}', "warning");
                    }
                    else {
                        $('#wt-balance').html(_number_format(result[currency], CryptoSettings[currency]['rate_decimals']).replace(',', ''));
                        if (document.getElementById('wt-balance') != null) {
                            $('#gastank-balance').html(_number_format(result['GAS'], CryptoSettings['ETH']['rate_decimals']).replace(',', ''));
                        }
                    }
                },
                error: function(err) {
                    showOverlay($('#main-obj'), false);
                    showToast("{{ trans('requests.withdraw.get_balance_failed') }}", '{{ trans('ui.alert.info') }}', "warning");
                    console.log(err);
                }
            })
        }

        $('#btn-cancel').on('click', function() {
            $('#remark').html('');

            $('#modal-cancel').modal('show');
        });

        $('#btn-submit-cancel').on('click', function() {
            if (!confirm('{{ trans('requests.withdraw.ask_cancel') }}')) {
                return;
            }

            var selected = [];
            var checkboxes = document.getElementsByName('checkItem');
            for (var i=0; i<checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    selected.push(checkboxes[i].value);
                }
            }

            $('#modal-cancel').modal('hide');
            $.ajax({
                url: BASE_URL + 'ajax/traderWithdraw/withdraw-cancel',
                type: 'POST',
                data: {
                    'currency': currency,
                    'selected': selected,
                    'remark': $('#remark').val(),
                },
                success: function(result) {
                    if (result == 0) {
                        // Success
                        showToast('{{ trans('requests.withdraw.op_success') }}', '{{ trans('ui.alert.info') }}', "success");
                        listTable.ajax.reload();
                    }
                    else if (result == 1) {
                        // Failed
                        showToast('{{ trans('requests.withdraw.op_failed') }}', '{{ trans('ui.alert.info') }}', "warning");
                    }
                },
                error: function(err) {
                    showToast('{{ trans('requests.withdraw.op_failed') }}', '{{ trans('ui.alert.info') }}', "warning");
                }
            });
        });

        $('#btn-withdraw').on('click', function() {
            var selected = [];
            var checkboxes = document.getElementsByName('checkItem');
            for (var i=0; i<checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    selected.push(checkboxes[i].value);
                }
            }

            if (!confirm('{{ trans('requests.withdraw.ask_approve') }}')) {
                return;
            }

            $.ajax({
                url: (PUBLIC_URL ? PUBLIC_URL : '') + 'ajax/traderWithdraw/withdraw-complete',
                type: 'POST',
                data: {
                    'selected': selected,
                    'currency': currency,
                },
                success: function(result) {
                    if (result == 0) {
                        // Success
                        showToast('{{ trans('requests.withdraw.op_success') }}', '{{ trans('ui.alert.info') }}', "success");
                        listTable.ajax.reload();
                    }
                    else {
                        // Failed
                        showToast('{{ trans('requests.withdraw.op_failed') }}', '{{ trans('ui.alert.info') }}', "warning");
                    }
                },
                error: function(err) {
                    showToast('{{ trans('requests.withdraw.op_failed') }}', '{{ trans('ui.alert.info') }}', "warning");
                }
            });
        });

        function initTable() {
            listTable = $('#request-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/traderWithdraw/request-list',
                    type: 'POST',
                    data: {
                        'currency': '{{ $currency }}',
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
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
                order: [1, 'desc'],
                columnDefs: [{
                    targets: [0, 1, 2, 3],
                    orderable: false,
                    searchable: false
                }],
                columns: [
                    {data: null},
                    {data: 'id'},
                    {data: 'userid'},
                    {data: 'nickname'},
                    {data: 'email'},
                    {data: 'destination'},
                    {data: 'amount'},
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

                    $('td', row).eq(3).css('min-width', '100px');

                    $('td', row).eq(5).html('').append(getReducedStr(data['destination'], 10, 5));

                    $('td', row).eq(6).html('').append(
                        _number_format(data['amount'], CryptoSettings['{{$currency}}']['rate_decimals'])
                    );
                    $('td', row).eq(6).attr('id', "amount_" + data['id']);
                },
            });
        }
    </script>
@endsection
