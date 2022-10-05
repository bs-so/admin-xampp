<?php

return [

	'deposit'			=>	[
        'title'			=> '管理者入金',
		'no'		    => '番号',
		'currency'		=> '仮想通貨',
		'wallet_address'=> 'ウォレットアドレス',
		'balance'	    => '残高',
        'actions'	=> '',
	],

    'withdraw'			=>	[
		'withdraw_title'=> '出金',
		'queue_list'	=> 'キュー',
        'title'			=> '管理者出金',
		'no'		    => '番号',
		'currency'		=> '仮想通貨',
		'wallet_address'=> 'ウォレットアドレス',
		'balance'	    => '残高',
		'id'	    	=> 'ID',
		'amount'	    => '金額',
		'to_address'    => '送金先アドレス',
		'tx_id'	    	=> 'トランザクションID',
		'remark'	    => '備考',
		'status'		=> '状態',
		'date'	  	  	=> '登録日時',
        'actions'		=> '',
		
		'withdraw_wallet_balance' => '出金ウォレットアドレスの残高',
		'gastank_balance'		  => 'ガスタンクの残高',
		'destination_address'	  => '送金先アドレス',

	],

	'modal'			=> [
		'title'			=> '入金アドレス',
		'information'	=> '%sのQRコード',
		'qr_code'		=> 'QRコード',
	],

	'message'		=> [
		'msg_not_enough_balance'	=> '残高が足りません。',
		'msg_get_balance_error'		=> '残高取得に失敗しました。',
		'msg_success'				=> '処理が成功しました！',
		'msg_getting_balance'		=> '少々お待ちください',
		'title_success'		=> '処理成功！',
		'title_error'		=> '処理失敗！',
	]
];
