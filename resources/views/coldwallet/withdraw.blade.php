@extends('layouts.afterlogin')

@section('title', trans('coldwallet.withdraw.title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
@endsection

@section('contents')
    <?php
        echo '<script>';
        echo 'var statuses = ' . json_encode(g_enum('StaffWithdrawStatus')) . ';';
        echo '</script>';
    ?>
    <section class="users-list-wrapper" id="section">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#nav-commission" aria-controls="commission" role="tab" aria-selected="false">{{ trans('coldwallet.withdraw.withdraw_title') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#nav-account" aria-controls="account" role="tab" aria-selected="false">{{ trans('coldwallet.withdraw.queue_list') }}</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="nav-commission" aria-labelledby="nav-commission" role="tabpanel">
                <div class="row">
                    <div class="col-lg-12">
                        <form id="frm-withdraw" method="post" action="{{ route('staff.post.edit') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="card">
                                <div class="card-body pb-2">
                                    <input type="hidden" name="id" value="$staff->id">
                                    <div class="form-group row">
                                        <label class="col-form-label col-sm-2 text-sm-right">{{ trans('coldwallet.withdraw.currency') }}</label>
                                        <div class="col-sm-8">
                                            <select id="currency" class="form-control">
                                                @foreach (g_enum('CryptoSettingsData') as $currency => $data)
                                                    <option value="{{ $currency }}">{{ $currency }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
									<!--
                                    <div class="form-group row">
                                        <label class="col-form-label col-sm-2 text-sm-right">{{ trans('coldwallet.withdraw.withdraw_wallet_balance') }}</label>
                                        <div class="col-sm-8">
                                            <label id="wt-balance" class="col-form-label col-sm-2 text-sm-left">1000.0</label>
                                        </div>
                                    </div>
									-->
                                    <div class="form-group row d-none" id="div-gastank">
                                        <label class="col-form-label col-sm-2 text-sm-right">{{ trans('coldwallet.withdraw.gastank_balance') }}</label>
                                        <div class="col-sm-8">
                                            <label id="gt-balance" class="col-form-label col-sm-2 text-sm-left">1000.0</label>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-sm-2 text-sm-right"><span class="text-danger">*</span>{{ trans('coldwallet.withdraw.amount') }}</label>
                                        <div class="col-sm-8">
                                            <input id="amount" type="text" class="form-control" name="name" value="">
                                            <small id="amount-error" class="invalid-feedback">This field is required.</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-sm-2 text-sm-right"><span class="text-danger">*</span>{{ trans('coldwallet.withdraw.destination_address') }}</label>
                                        <div class="col-sm-8">
                                            <input id="address" type="text" class="form-control" name="name" value="">
                                            <small id="address-error" class="invalid-feedback">This field is required.</small>
                                        </div>

                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-sm-2 text-sm-right"><span class="text-danger">*</span>{{ trans('coldwallet.withdraw.remark') }}</label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="remark" rows="5"></textarea>
                                            <small id="remark-error" class="invalid-feedback">This field is required.</small>
                                        </div>
                                    </div>
                                    <hr class="m-0">
                                    <div class="card-body">
                                        <div class="text-center mt-3">
                                            <button type="button" class="btn btn-primary" onclick="javascript:doRequest();">
                                                <span id="btn-OK" class="fa fa-check">&nbsp;{{ trans('ui.button.submit') }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="nav-account" aria-labelledby="nav-account" role="tabpanel">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="queue-list" class="table">
                                    <thead>
                                    <tr>
                                        <th>{{ trans('coldwallet.withdraw.id') }}</th>
                                        <th>{{ trans('coldwallet.withdraw.currency') }}</th>
                                        <th class="text-right">{{ trans('coldwallet.withdraw.amount') }}</th>
                                        <th>{{ trans('coldwallet.withdraw.to_address') }}</th>
                                        <th>{{ trans('coldwallet.withdraw.tx_id') }}</th>
                                        <th>{{ trans('coldwallet.withdraw.remark') }}</th>
                                        <th>{{ trans('coldwallet.withdraw.status') }}</th>
                                        <th>{{ trans('coldwallet.withdraw.date') }}</th>
                                    </tr>
                                    </thead>
                                </table>
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
    <script src="{{ cAsset('js/__common.js') }}"></script>
    <script src="{{ cAsset("js/withdraw-list.js") }}"></script>

    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <script>
        function initTable() {
            listTable = $('#queue-list').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: BASE_URL + 'ajax/withdraw/queue',
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
                columns: [
                    {data: 'id'},
                    {data: 'currency'},
                    {data: 'amount', class: 'text-right'},
                    {data: 'to_address'},
                    {data: 'tx_id'},
                    {data: 'remark'},
                    {data: 'status'},
                    {data: 'created_at'},
                ],
                createdRow: function (row, data, index) {
                    var pageInfo = listTable.page.info();

                    $('td', row).eq(2).html('').append(
                        _number_format(data['amount'], CryptoSettings[data['currency']]['rate_decimals'])
                    );

                    $('td', row).eq(6).html('').append(
                        '<span class="text-white badge-glow badge badge-' + statuses[data['status']][1] + '">' + statuses[data['status']][0] + '</span>'
                    );
                },
            });
            getWalletBalances();
        }

        function doRequest() {
            let currency = $('#currency').val();
            let amount = $('#amount').val();
            let address = $('#address').val();
            let remark = $('#remark').val();

            showOverlay($('#section'), true, "{{ trans('ui.alert.just_wait') }}");
            $.ajax({
                url: BASE_URL + 'ajax/withdraw/request',
                type: 'POST',
                data: {
                    currency: currency,
                    amount: amount,
                    address: address,
                    remark: remark,
                },
                success: function(result) {
                    showOverlay($('#section'), false);
                    $('#frm-withdraw').find('select').removeClass('is-invalid');
                    $('#frm-withdraw').find('input').removeClass('is-invalid');
                    $('#frm-withdraw').find('textarea').removeClass('is-invalid');

                    if (result == 0) {
                        showToast("{{ trans('coldwallet.message.msg_success') }}", "{{ trans('coldwallet.message.title_success') }}", "success");
                        setTimeout(function() {
                            document.location.reload();
                        }, 2000);
                        return;
                    }
                    else if (result == 1)
                    {
                        showToast("{{ trans('coldwallet.message.msg_not_enough_balance') }}", "{{ trans('coldwallet.message.title_error') }}", "warning");
                        return;
                    }
                    else if (result == 2)
                    {
                        showToast("{{ trans('coldwallet.message.msg_get_balance_error') }}", "{{ trans('coldwallet.message.title_error') }}", "warning");
                        return;
                    }
                },
                error: function(err) {
                    showOverlay($('#section'), false);
                    var errorMsg = err['responseJSON']['errors'];

                    $('#frm-withdraw').find('select').removeClass('is-invalid');
                    $('#frm-withdraw').find('input').removeClass('is-invalid');
                    $('#frm-withdraw').find('textarea').removeClass('is-invalid');

                    if (errorMsg['currency'] != null) {
                        $('#currency').addClass('is-invalid');
                        document.getElementById('currency-error').innerHTML = errorMsg['currency'];
                    }
                    if (errorMsg['amount'] != null) {
                        $('#amount').addClass('is-invalid');
                        document.getElementById('amount-error').innerHTML = errorMsg['amount'];
                    }
                    if (errorMsg['address'] != null) {
                        $('#address').addClass('is-invalid');
                        document.getElementById('address-error').innerHTML = errorMsg['address'];
                    }
                    if (errorMsg['remark'] != null) {
                        $('#remark').addClass('is-invalid');
                        document.getElementById('remark-error').innerHTML = errorMsg['remark'];
                    }
                }
            });
        }

        $('#currency').on('change', function() {
            let currency = $('#currency').val();
            if (currency == 'ETH' || currency == 'USDT') {
                $('#div-gastank').removeClass('d-none');
            }
            else {
                $('#div-gastank').addClass('d-none');
            }
            getWalletBalances();
        });

        function getWalletBalances()
        {
            let currency = $('#currency').val();
            showOverlay($('#section'), true, "{{ trans('ui.alert.just_wait') }}");

            $.ajax({
                url: BASE_URL + 'ajax/withdraw/getWalletBalances',
                type: 'POST',
                data: {
                    currency: currency,
                },
                success: function(result) {
                    showOverlay($('#section'), false);
                    if (result == 1) {
                        showToast("{{ trans('requests.withdraw.no_wallet') }}", "{{ trans('ui.alert.info') }}", "warning");
                    }
                    else if (result >= 2) {
                        showToast("{{ trans('requests.withdraw.get_balance_failed') }}!", "{{ trans('ui.alert.info') }}", "warning");
                    }
                    else {
                        $('#wt-balance').html(_number_format(result[currency], CryptoSettings[currency]['rate_decimals']));
                        $('#gt-balance').html(_number_format(result['GAS'], CryptoSettings['ETH']['rate_decimals']));
                    }
                },
                error: function(err) {
                    showOverlay(false);
                    showToast("{{ trans('requests.withdraw.get_balance_failed') }}!", "{{ trans('ui.alert.info') }}", "warning");
                    console.log(err);
                }
            })
        }
    </script>
@endsection
