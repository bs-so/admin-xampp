<?php

return [

	'title'		=> 'ウォレット管理',

	'table'		=> [
		'no'			=> '番号',
		'wallet_id'		=> 'ウォレットID',
		'type'			=> '形態',
		'specified'		=> '指定状態',
		'currency'		=> '仮想通貨',
		'address'		=> 'アドレス',
		'priv_key'		=> '秘密キー',
		'balance'		=> '残高',
		'status'		=> '状態',
		'remark'		=> '備考',
		'reged_at'		=> '登録日時',
		'actions'		=> '',
	],

	'add'				=> [
		'title'			=> 'ウォレット追加',
		'subtitle'		=> 'ウォレット情報を入力ください。',

		'result'		=> '結果',
		'checking'		=> '確認中。。。',
		'check_result'	=> '確認結果',
		'confirmed'		=> 'アドレスが有効です！',
		'invalid'		=> 'アドレスが無効です！',
	],

	'private'			=> [
		'title'			=> '秘密キー設定',
		'subtitle'		=> '秘密キーを入力ください。',

		'success'		=> '秘密キーを設定されました。',
	],

	'deposit'			=> [
		'title'			=> '入金ウォレット選択',
		'subtitle'		=> '入金ウォレットを選択ください。',

		'success'		=> '入金ウォレットを選択されました。',
	],

	'withdraw'			=> [
		'title'			=> '出金ウォレット選択',
		'subtitle'		=> '出金ウォレットを選択ください。',

		'success'		=> '出金ウォレットを選択されました。',
	],

	'gastank'			=> [
		'title'			=> 'ガスタンク選択',
		'subtitle'		=> 'ガスタンクを選択ください。',

		'success'		=> 'ガスタンクを選択されました。',
	],

	'button'	=> [
		'set_priv_key'		=> '秘密キー設定',
		'refresh_balance'	=> '残高更新',
		'select_deposit'	=> '入金ウォレット選択',
		'select_withdraw'	=> '出金ウォレット選択',
		'select_gastank'	=> 'ガスタンク選択',
	],
];
