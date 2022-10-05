<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
{{ sprintf(trans('mail.transfer_receiver.msg1'), $name) }}<br>
<br>
{{ sprintf(trans('mail.transfer_receiver.msg2'), env('APP_SHORT_NAME')) }}<br>
{{ trans('mail.transfer_receiver.msg3') }}<br>
<br>
-----------------------------------------------------<br>
{{ trans('mail.transfer_receiver.sender') }}<span class="font-weight-bold" style="color: blue;">{{ $sender }}</span><br>
{{ trans('mail.transfer_receiver.receiver') }}<span class="font-weight-bold" style="color: blue;">{{ $receiver }}</span><br>
{{ trans('mail.transfer_receiver.currency') }}<span class="font-weight-bold" style="color: blue;">{{ $currency }}</span><br>
{{ trans('mail.transfer_receiver.amount') }}<span class="font-weight-bold" style="color: blue;">{{ $amount }}</span><br>
{{ trans('mail.transfer_receiver.result') }}<span class="font-weight-bold" style="color: red;">{{ $result }}</span><br>
-----------------------------------------------------<br>
<br>
{{ trans('mail.transfer_receiver.msg4') }}<br>
<br>
</body>
</html>
