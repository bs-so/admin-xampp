@extends('layouts.afterlogin')

@section('title', trans('system.balance.title'))

@section('styles')
    <link href="{{ cAsset('vendor/datatables/datatables.css') }}" rel="stylesheet">
    <link href="{{ cAsset('vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
@endsection

@section('contents')
    <section class="users-list-wrapper">
        <?php
            $cryptoSettings = Session::get('crypto_settings');
        ?>
        @foreach ($types as $type_index => $type)
            @if ($type != SYSTEM_BALANCE_TYPE_AFFILIATE)
            <div class="row match-height">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-danger">{{ trans('system.balance.subtitle' . $type) }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-dark">
                                <th style="width: 10%;">{{ trans('system.balance.no') }}</th>
                                <th style="width: 30%;">{{ trans('system.balance.currency') }}</th>
                                @if ($type == SYSTEM_BALANCE_TYPE_CASINO_MANUAL)
                                    <th>{{ trans('system.balance.balance2') }}</th>
                                    <th>{{ trans('system.balance.balance1') }}</th>
                                @else
                                    <th>{{ trans('system.balance.balance') }}</th>
                                @endif
                                </thead>
                                <tbody>
                                    <?php $index = 1; ?>
                                    @foreach ($balances as $rec_index => $record)
                                        @if ($record->type == $type)
                                        <tr>
                                            <td>{{ $index ++ }}</td>
                                            <td>{{ $record->currency }}</td>
                                            <td>{{ _number_format($record->balance,
                                                        min(MINIMUM_BALANCE_DECIMALS, $cryptoSettings[$record->currency]['rate_decimals'])) }}</td>
                                            @if ($type == SYSTEM_BALANCE_TYPE_CASINO_MANUAL)
                                                <td>{{ _number_format(isset($auto_balances[$record->currency]) ? $auto_balances[$record->currency] : 0,
                                                            min(MINIMUM_BALANCE_DECIMALS, $cryptoSettings[$record->currency]['rate_decimals'])) }}
                                                </td>
                                            @endif
                                        </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            @endif
        @endforeach
    </section>
@endsection

@section('scripts')
    <script src="{{ cAsset('vendor/moment/moment.js') }}"></script>
    <script src="{{ cAsset('vendor/datatables/datatables.js') }}"></script>
    <script src="{{ cAsset('vendor/daterangepicker/daterangepicker.min.js') }}"></script>
@endsection
