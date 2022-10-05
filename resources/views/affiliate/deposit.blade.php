@extends('layouts.afterlogin')

@section('title', trans('affiliate.deposit.title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
@endsection

@section('contents')
    <section class="users-list-wrapper">
        <?php
            $cryptoSettings = Session::get('crypto_settings');
        ?>
        @if ($message = Session::get('flash_message'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                {{ trans($message) }}
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                {{ trans('affiliate.message.deposit_failed') }}
            </div>
        @endif
            <div class="row match-height">
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-dark">
                                <th>{{ trans('affiliate.deposit.no') }}</th>
                                <th>{{ trans('affiliate.deposit.currency') }}</th>
                                <th>{{ trans('affiliate.deposit.balance') }}</th>
                                </thead>
                                <tbody>
                                    <?php $index = 1; ?>
                                    @foreach ($balances as $currency => $balance)
                                        <tr>
                                            <td>{{ $index ++ }}</td>
                                            <td>{{ $currency }}</td>
                                            <td>{{ _number_format($balance, min(MINIMUM_BALANCE_DECIMALS, $cryptoSettings[$currency]['rate_decimals'])) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6" id="info-div">
                <div class="card">
                    <div class="card-header">
                        <h3>{{ trans('affiliate.deposit.subtitle') }}</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('affiliate.deposit.submit') }}" method="POST">
                            @csrf
                            <div class="form-row">
                                <div class="form-group col">
                                    <label class="form-label">{{ trans('affiliate.deposit.balance') }}</label>
                                    <input type="text" id="balance" class="form-control mr-sm-2 mb-2 mb-sm-0" readonly>
                                    <small id="bank-name-error" class="invalid-feedback">{{ trans('auth.required') }}</small>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col">
                                    <label class="form-label">{{ trans('affiliate.deposit.currency') }}</label>
                                    <select id="currency" name="currency"
                                            class="form-control mr-sm-2 mb-2 mb-sm-0 {{ $errors->has('currency') ? 'is-invalid' : '' }}">
                                        <option value=""></option>
                                        @foreach (g_enum('CryptoSettingsData') as $currency => $data)
                                            <option value="{{ $currency }}" {{ old('currency') == $currency ? 'selected' : '' }}>{{ $currency }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('currency'))
                                        <small class="invalid-feedback">{{ $errors->first('currency') }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col">
                                    <label class="form-label">{{ trans('affiliate.deposit.amount') }}</label>
                                    <input type="text" id="amount" name="amount" value="{{ old('amount') }}"
                                           class="form-control mr-sm-2 mb-2 mb-sm-0 {{ $errors->has('amount') ? 'is-invalid' : '' }}">
                                    @if ($errors->has('amount'))
                                        <small class="invalid-feedback">{{ $errors->first('amount') }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col">
                                    <label class="form-label">{{ trans('affiliate.deposit.remark') }}</label>
                                    <textarea rows="5" type="text" name="remark" class="form-control mr-sm-2 mb-2 mb-sm-0">{{ old('remark') }}</textarea>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-success">{{ trans('ui.button.update') }}</button>
                            </div>
                        </form>
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

    <script>
        $('#currency').on('change', function() {
            let currency = $('#currency').val();
            showOverlay($('#info-div'), true, '{{ trans('ui.alert.updating') }}');

            $.ajax({
                url: BASE_URL + 'ajax/wallets/getTotalBalance',
                type: 'POST',
                data: {
                    currency: currency,
                },
                success: function(result) {
                    showOverlay($('#info-div'), false);
                    $('#balance').val(result);
                },
                error: function(err) {
                    showOverlay($('#info-div'), false);
                    console.log(err);
                }
            });
        });
    </script>
@endsection
