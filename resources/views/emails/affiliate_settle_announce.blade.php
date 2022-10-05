<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <style>
        .table-bordered {
            border : 1px solid #F8F8F8;
        }
        .table-bordered th, .table-bordered td {
            border : 1px solid #F8F8F8;
        }
        .table-bordered thead th, .table-bordered thead td {
            border-bottom-width : 2px;
        }
    </style>
</head>
<body>
{{ sprintf(trans('mail.affiliate_settle.msg1'), $name) }}<br>
<br>
{{ sprintf(trans('mail.affiliate_settle.msg2'), env('APP_SHORT_NAME')) }}<br>
{{ trans('mail.affiliate_settle.msg3') }}<br>
<br>
-----------------------------------------------------<br>
<div class="col-sm-12">
    <table class="table table-bordered">
        <thead>
        <th>{{ trans('mail.affiliate_settle.no') }}</th>
        <th>{{ trans('mail.affiliate_settle.currency') }}</th>
        <th>{{ trans('mail.affiliate_settle.commission') }}</th>
        <th>{{ trans('mail.affiliate_settle.prev_balance') }}</th>
        <th>{{ trans('mail.affiliate_settle.next_balance') }}</th>
        </thead>
        <tbody>
        @foreach ($commissions as $index => $commission)
            <tr>
                <?php $currency = $commission->currency; ?>
                <td>{{ $index + 1 }}</td>
                <td>{{ $currency }}</td>
                <td>
                    <strong style="color: red;">{{ _number_format($commission->commission, min(MINIMUM_BALANCE_DECIMALS, $crypto_settings[$currency]['rate_decimals'])) }}</strong>
                </td>
                <td>{{ _number_format($balances[$currency]['prev_balance'], min(MINIMUM_BALANCE_DECIMALS, $crypto_settings[$currency]['rate_decimals'])) }}</td>
                <td>
                    <strong style="color: red;">
                        {{ _number_format($balances[$currency]['next_balance'], min(MINIMUM_BALANCE_DECIMALS, $crypto_settings[$currency]['rate_decimals'])) }}
                    </strong>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
-----------------------------------------------------<br>
<br>
{{ trans('mail.affiliate_settle.msg4') }}<br>
<br>
</body>
</html>
