<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
{{ sprintf(trans('mail.transfer_sender.msg1'), $name) }}<br>
<br>
{{ sprintf(trans('mail.transfer_sender.msg2'), env('APP_SHORT_NAME')) }}<br>
{{ trans('mail.transfer_sender.msg3') }}<br>
<br>
-----------------------------------------------------<br>
{{ trans('mail.transfer_sender.sender') }}<span class="font-weight-bold" style="color: blue;">{{ $sender }}</span><br>
{{ trans('mail.transfer_sender.receiver') }}<span class="font-weight-bold" style="color: blue;">{{ $receiver }}</span><br>
{{ trans('mail.transfer_sender.currency') }}<span class="font-weight-bold" style="color: blue;">{{ $currency }}</span><br>
{{ trans('mail.transfer_sender.amount') }}<span class="font-weight-bold" style="color: blue;">{{ $amount }}</span><br>
{{ trans('mail.transfer_sender.result') }}<span class="font-weight-bold" style="color: red;">{{ $result }}</span><br>
-----------------------------------------------------<br>
<br>
{{ trans('mail.transfer_sender.msg4') }}<br>
<br>
</body>
</html>
