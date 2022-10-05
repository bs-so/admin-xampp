<?php

return [

    'title'			=> '送金',
	
	'table'			=>	[
        'no'            => 'No',
		'currency'		=> '仮想通貨',
		'send_from'		=> '送信アドレス',
		'send_to'		=> '受信アドレス',
		'curr_balance'	=> '現在の残高',
		'amount'		=> '送金数量',
		'next_balance'	=> '送金後残高',

		'transaction'	=> 'トランザクション',
		'nonce'			=> 'ノンス',
		'qr_code'		=> 'QRコード',
		'signed_tx'		=> 'サイン結果',
	],

	'direction'		=> [
		'in'			=> '入金',
		'out'			=> '出金',
		'set'			=> '設定',
	],

	'system'		=> [
		'title'			=> '管理者間送金',

		'navs_history'	=> '送金一覧',
		'navs_casino'	=> 'カジノ管理者の残高設定',
		'navs_affiliate'=> 'アフィ管理者の送金要請',

		'no'			=> '番号',
		'operator'		=> '操作者',
		'type'			=> '形態',
		'direction'		=> '方向',
		'user'			=> 'アフィユーザー',
		'currency'		=> '仮想通貨',
		'amount'		=> '送金金額',
		'remark'		=> '備考',
		'status'		=> '状態',
		'created_at'	=> '送金日時',

		'balance'		=> 'アフィ側残高',
		'select_currency'	=> '通貨を選択ください。',
		'casino_balance'	=> 'カジノ側2次残高',
		'add_amount'	=> '追加金額',
		'op_success'	=> '送金に成功されました。',
		'op_failed'		=> '送金に失敗されました。',
		'not_enough_balance'	=> '残高が十分ではありません。',
	],

	'users'			=> [
		'title'			=> 'ユーザー間送金管理',
		'title_history'	=> 'ユーザー間送金履歴',

		'navs_history'	=> '送金一覧',
		'navs_request'	=> '送金要請',

		'no'			=> '番号',
		'operator'		=> '操作者',
		'sender'		=> '送金者',
		'receiver'		=> '送金先',
		'currency'		=> '仮想通貨',
		'amount'		=> '金額',
		'fee'			=> '手数料',
		'remark'		=> '備考',
		'status'		=> '状態',
		'created_at'	=> '送信日時',

		'from_system'	=> '管理者からの送金',
		'from_user'		=> 'ユーザーからの送金',
		'casino_balance'	=> 'カジノの残高',
		'sender_balance'	=> '送信者の残高',

		'invalid_sender'	=> '無効な送信者です。',
		'invalid_receiver'	=> '無効な受信者です。',
		'op_success'	=> '送金に成功されました。',
		'op_failed'		=> '送金に失敗されました。',
		'not_enough_balance'	=> '残高が十分ではありません。',
	],

	'steps'			=> [
		'title1'		=> '送金情報入力',
		'title2'		=> 'トランザクションのサイン',
		'title3'		=> '送金の完了',

		'icon_title1'	=> '送金情報',
		'icon_title2'	=> 'サイン',
		'icon_title3'	=> '完了',
	],

	'message'		=>	[
		'just_wait'			=> '処理中。。。',
		'invalid_wallets'	=> 'ウォレットを正確に選択してください。',
		'invalid_amount'	=> '送金数量を正確に入力してください。',
		'invalid_signed'	=> 'サイン結果を正確に入力してください。',
		'make_tx_failed'	=> 'トランザクション生成に失敗されました。',
		'make_tx_success'	=> 'トランザクション生成に成功されました。',
		'sign_tx_failed'	=> 'トランザクションサインに失敗されました。',
		'sign_tx_success'	=> 'トランザクションサインに成功されました。',
	],
];
