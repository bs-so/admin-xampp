@extends('layouts.afterlogin')

@section('title', trans('transfer.title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ cAsset('app-assets/css/plugins/forms/wizard.css') }}">
    <style>
        .users-list-wrapper a i, .users-list-wrapper span i {
            cursor: pointer;
            font-size: 1.7rem;
        }
    </style>
@endsection

@section('contents')
    <!-- users list start -->
    <section class="users-list-wrapper">
        <ul class="nav nav-tabs nav-fill" role="tablist">
            @foreach (g_enum('CryptoSettingsData') as $currency => $data)
                <li class="nav-item">
                    <a class="nav-link {{ $currency == $sel_currency ? 'active' : '' }}" href="{{ route('transfer') }}?currency={{ $currency }}" aria-controls="{{ $currency }}">{{ $currency }}</a>
                </li>
            @endforeach
        </ul>

        <?php $WalletTypeData = g_enum('WalletTypeData'); ?>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 id="step-title" class="card-title">{{ trans('transfer.steps.title1') }}({{ $sel_currency }})</h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <form id="form-wizard" action="#" class="icons-tab-steps wizard-circle">
                                <!-- Step 1(Input Information) -->
                                <h6><i class="step-icon feather icon-info"></i> {{ trans('transfer.steps.icon_title1') }}</h6>
                                <fieldset>
                                    <div class="row">
                                        <div class="col-sm-5">
                                            <div class="form-group">
                                                <label for="send_from">{{ trans('transfer.table.send_from') }}</label>
                                                <select class="form-control" id="send_from">
                                                    @foreach ($wallets as $index => $wallet)
                                                        <option value="{{ $index  }}">{{ $WalletTypeData[$wallet->type][2] }}{{ $wallet->id }}&nbsp;:&nbsp;{{ $wallet->wallet_address }}</option>
                                                    @endforeach
                                                </select>
                                                <small id="send_from-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="mt-2 text-center">
                                                <i class="fa fa-arrow-circle-right font-medium-4 text-danger"></i>
                                            </div>
                                        </div>
                                        <div class="col-sm-5">
                                            <div class="form-group">
                                                <label for="send_to">{{ trans('transfer.table.send_to') }}</label>
                                                <select class="form-control" id="send_to">
                                                    @foreach ($wallets as $index => $wallet)
                                                        <option value="{{ $index }}">{{ $WalletTypeData[$wallet->type][2] }}{{ $wallet->id }}&nbsp;:&nbsp;{{ $wallet->wallet_address }}</option>
                                                    @endforeach
                                                </select>
                                                <small id="send_to-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-2">
                                            <div class="form-group">
                                                <label for="send_to">{{ trans('transfer.table.curr_balance') }}</label>
                                                <label class="form-control height-50" id="curr_balance">0</label>
                                                <small id="send_to-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                            </div>
                                        </div>
                                        <div class="col-sm-1">
                                            <div class="mt-2 text-center">
                                                <i class="fa fa-angle-double-right font-medium-4 text-danger"></i>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="form-group">
                                                <label for="send_to">{{ trans('transfer.table.next_balance') }}</label>
                                                <label class="form-control height-50" id="next_balance">0</label>
                                                <small id="send_to-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="mt-2 text-center">
                                                <i class="fa fa-question-circle-o font-medium-4 text-danger"></i>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label for="send_to">{{ trans('transfer.table.amount') }}</label>
                                                <input type="text" class="form-control" id="amount" value="0.05">
                                                <small id="send_to-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                            </div>
                                        </div>
                                        <div class="col-sm-2" style="margin-top: 18px;">
                                            <button type="button" class="btn btn-primary" onclick="javascript:inputMax();">{{ trans('ui.button.max') }}</button>
                                        </div>
                                    </div>
                                </fieldset>

                                <!-- Step 2(Sign Transaction) -->
                                <h6><i class="step-icon feather icon-slack"></i> {{ trans('transfer.steps.icon_title2') }}</h6>
                                <fieldset>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="transaction">{{ trans('transfer.table.transaction') }}</label>
                                                <textarea id="transaction" class="form-control" rows="5" readonly></textarea>
                                                <small id="transaction-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="nonce">{{ trans('transfer.table.nonce') }}</label>
                                                <input type="text" id="nonce" class="form-control" value="0" readonly>
                                                <small id="nonce-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label for="qr_code">{{ trans('transfer.table.qr_code') }}</label>
                                                <div class="form-row align-items-center" id="qr-divs">
                                                    <div id="qr-div-1" class="p-1">
                                                        <img id="qr-code-1" src="{{ cAsset('app-assets/images/get-ready.png') }}" width="320" height="320" alt="">
                                                    </div>
                                                </div>
                                                <small id="qr_code-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label for="signed_tx">{{ trans('transfer.table.signed_tx') }}</label>
                                                <textarea id="signed_tx" class="form-control" rows="5"></textarea>
                                                <small id="signed_tx-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                <!-- Step 3(Finish) -->
                                <h6><i class="step-icon feather icon-zap"></i> {{ trans('transfer.steps.icon_title3') }}</h6>
                                <fieldset>
                                    <div class="row">
                                        <div class="col-sm-5">
                                            <div class="form-group">
                                                <label for="from_address">{{ trans('transfer.table.send_from') }}</label>
                                                <label class="form-control" id="confirm_from"></label>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="mt-2 text-center">
                                                <i class="fa fa-arrow-circle-right font-medium-4 text-danger"></i>
                                            </div>
                                        </div>
                                        <div class="col-sm-5">
                                            <div class="form-group">
                                                <label for="from_address">{{ trans('transfer.table.send_to') }}</label>
                                                <label class="form-control" id="confirm_to"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-5">
                                            <div class="form-group">
                                                <label for="from_address">{{ trans('transfer.table.amount') }}</label>
                                                <label class="form-control" id="confirm_amount"></label>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="mt-2 text-center">
                                                <i class="fa fa-arrow-circle-right font-medium-4 text-danger"></i>
                                            </div>
                                        </div>
                                        <div class="col-sm-5">
                                            <div class="form-group">
                                                <label for="from_address">{{ trans('transfer.table.next_balance') }}</label>
                                                <label class="form-control" id="confirm_next"></label>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
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
    <script src="{{ cAsset('app-assets/vendors/js/extensions/jquery.steps.min.js') }}"></script>
    <script src="{{ cAsset('vendor/vue/vue.js') }}"></script>
    <script src="{{ cAsset("js/transfer-index.js") }}"></script>

    <?php echo('<script>let WalletTypeData = ' . json_encode(g_enum('WalletTypeData')) . ';</script>') ?>
    <?php echo('<script>let Wallets = ' . json_encode($wallets) . ';</script>') ?>
    <?php echo('<script>let CryptoSettings = ' . json_encode(Session::get('crypto_settings')) . ';</script>') ?>
    <script>
        let selCurrency = '{{ $sel_currency }}';
        let rateDecimals = CryptoSettings[selCurrency]['rate_decimals'];
        let waitCaption = '{{ trans('transfer.message.just_wait') }}';
        let suffixStr = '(' + '{{ $sel_currency }}' + ')';
        let StepTitles = [
            '{{ trans('transfer.steps.title1') }}' + suffixStr,
            '{{ trans('transfer.steps.title2') }}' + suffixStr,
            '{{ trans('transfer.steps.title3') }}' + suffixStr,
        ];

        let wizard = $(".icons-tab-steps").steps({
            headerTag: "h6",
            bodyTag: "fieldset",
            transitionEffect: "fade",
            autoFocus: true,
            saveState: true,
            titleTemplate: '<span class="step">#index#</span> #title#',
            labels: {
                previous: '{{ trans('ui.button.prev') }}',
                next: '{{ trans('ui.button.next') }}',
                finish: '{{ trans('ui.button.submit') }}'
            },
            onStepChanging: function(event, currentIndex, newIndex) {
                let ret = true;
                switch (currentIndex) {
                    case 0:
                        ret = doCheckInfo();
                        break;
                    case 1:
                        ret = doCheckSign();
                        break;
                    case 2:
                        break;
                }
                return ret;
            },
            onStepChanged: function(event, currentIndex, priorIndex) {
                $('#step-title').html(StepTitles[currentIndex]);
                let ret = true;
                switch (currentIndex) {
                    case 1: // Check Info
                        ret = makeTransaction();
                        break;
                    case 2: // Sign
                        ret = confirmInfo();
                        break;
                }
                return ret;
            },
            onFinished: function (event, currentIndex) {
                doFinish();
            },
        });

        function setCurrBalance() {
            let fee = BigNumber(CryptoSettings[selCurrency]['transfer_fee']);
            if (selCurrency == 'ETH' || selCurrency == 'USDT') {
                // no fee because of use gas
                fee = BigNumber(0);
            }

            let send_from = $('#send_from').val();
            let temp = BigNumber(Wallets[send_from]['balance']);
            let curr_balance = temp.toNumber();
            let amount = $('#amount').val();
            if (amount == "") amount = 0;
            temp = temp.minus(BigNumber(amount));
            temp = temp.minus(fee);
            if (temp.isNegative()) temp = BigNumber(0);
            let next_balance = temp.toNumber();

            $('#curr_balance').html(_number_format(curr_balance, rateDecimals));
            $('#next_balance').html(_number_format(next_balance, rateDecimals));
        }

        function inputMax() {
            let fee = BigNumber(CryptoSettings[selCurrency]['transfer_fee']);
            if (selCurrency == 'ETH' || selCurrency == 'USDT') {
                // no fee because of use gas
                fee = BigNumber(0);
            }

            let send_from = $('#send_from').val();
            let balance = BigNumber(Wallets[send_from]['balance']);
            balance = balance.minus(fee);
            $('#amount').val(balance.toNumber());
            setCurrBalance();
        }

        function doCheckInfo() {
            let send_from = $('#send_from').val();
            let send_to = $('#send_to').val();
            if (send_from == send_to) {
                showToast('{{ trans('ui.alert.info') }}', '{{ trans('transfer.message.invalid_wallets') }}', "warning");
                return false;
            }

            let fee = BigNumber(CryptoSettings[selCurrency]['transfer_fee']);
            if (selCurrency == 'ETH' || selCurrency == 'USDT') {
                // no fee because of use gas
                fee = BigNumber(0);
            }
            let curr_balance = BigNumber($('#curr_balance').html());
            let amount = $('#amount').val();
            amount = BigNumber(amount == "" ? 0 : amount);
            curr_balance = curr_balance.minus(amount);
            curr_balance = curr_balance.minus(fee);
            console.log(curr_balance.toString());

            if (amount.isZero() || amount.isNegative() || curr_balance.isNegative()) {
                showToast('{{ trans('ui.alert.info') }}', '{{ trans('transfer.message.invalid_amount') }}', "warning");
                return false;
            }

            return true;
        }

        function makeTransaction() {
            let send_from = $('#send_from').val();
            let send_to = $('#send_to').val();
            let amount = $('#amount').val();
            let fee = CryptoSettings[selCurrency]['transfer_fee'];

            $.ajax({
                url: BASE_URL + 'ajax/transfer/makeTransaction',
                type: 'POST',
                data: {
                    currency: selCurrency,
                    from_address: Wallets[send_from]['wallet_address'],
                    to_address: Wallets[send_to]['wallet_address'],
                    from_wallet: Wallets[send_from]['id'],
                    to_wallet: Wallets[send_to]['id'],
                    amount: amount,
                    fee: fee,
                },
                success: function(result) {
                    if (result['status'] == '{{ TRANSFER_STATUS_FAILED }}') {
                        showToast('{{ trans('ui.alert.info') }}', '{{ trans('transfer.message.make_tx_failed') }}', "warning");
                        return;
                    }
                    else {
                        let data = JSON.stringify(result);
                        signedResult = result;
                        $('#transaction').val(data);
                        $('#nonce').val(result['nonce']);
                        showToast('{{ trans('ui.alert.info') }}', '{{ trans('transfer.message.make_tx_success') }}', "success");
                        generateQrCodes(data);
                    }
                },
                error: function(err) {
                    console.log(err);
                }
            });

            return true;
        }

        function doCheckSign() {
            let signed_tx = $('#signed_tx').val();
            if (signed_tx == '') {
                showToast('{{ trans('ui.alert.info') }}', '{{ trans('transfer.message.make_tx_failed') }}', "warning");
                return false;
            }

            return true;
        }

        function confirmInfo() {
            $('#confirm_from').html($('#send_from').find(':selected').text());
            $('#confirm_to').html($('#send_to').find(':selected').text());
            $('#confirm_amount').html($('#amount').val());
            $('#confirm_next').html($('#next_balance').html());
        }

        function doFinish() {
            let signed_tx = $('#signed_tx').val();
            let from_address = $('#send_from').find(':selected').text();
            let to_address = $('#send_to').find(':selected').text();
            let from = from_address.substring(0, 2);
            let to = to_address.substring(0, 2);
            let remark = from + ' -> ' + to;

            $.ajax({
                url: BASE_URL + 'ajax/transfer/doFinish',
                type: 'POST',
                data: {
                    signed_tx: signed_tx,
                    remark: remark,
                },
                success: function(result) {
                    showToast('{{ trans('ui.alert.info') }}', '{{ trans('transfer.message.sign_tx_success') }}', "success");
                    document.location.href = BASE_URL + 'transactions';
                },
                error: function(err) {
                    showToast('{{ trans('ui.alert.info') }}', '{{ trans('transfer.message.sign_tx_failed') }}', "warning");
                    console.log(err);
                    return;
                }
            });
        }
    </script>
@endsection
