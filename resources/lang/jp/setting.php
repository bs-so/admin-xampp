<?php

return [

    'title'			=> '基本設定',

	'nav'			=> [
		'master'		=> 'マスター',
		'crypto'		=> '仮想通貨',
		'bank'			=> '銀行管理',
		'maintenance'	=> 'メンテナンス'
	],

	'table'			=> [
		'no'			=> '番号',
		'currency'		=> '仮想通貨',
		'currency_name'	=> '通貨名',
		'unit'			=> '単位',
		'rate_decimals'	=> '小数桁',
		'min_deposit'	=> '最小入金額',
        'min_transfer'	=> '最小送金額',
		'min_withdraw'	=> '最小出金額',
		'transfer_fee'	=> '送金手数料',
		'gas_price'		=> 'ガス価格',
		'gas_limit'		=> 'ガス制限',
		'status'		=> '状態',
	],

	'bank'			=> [
		'no'				=> '番号',
		'bank_name'			=> '銀行名',
		'branch_name'		=> '支店名',
		'type'				=> '普通/当座',
		'account_number'	=> '口座番号',
		'account_name'		=> '口座名義',
		'status'			=> '状態',

		'add_title'			=> '銀行追加',
		'edit_title'		=> '銀行編集',
		'subtitle'			=> '銀行情報を入力してください。',
	],

	'maintenance'		=> [
		'label'			=> 'メンテナンスモード',
		'lang'			=> '言語',
		'content'		=> 'メンテナンス本文',
        'access_url'    => 'アクセスURL',
	],

	'message'		=>	[
		'invalid_unit'			=> '%sの単位が正しくないです。',
		'invalid_rate_decimals'	=> '%sの小数桁が正しくないです。',
		'invalid_min_deposit'	=> '%sの最小入金額が正しくないです。',
		'invalid_min_transfer'	=> '%sの最小送金額が正しくないです。',
		'invalid_min_withdraw'	=> '%sの最小出金額が正しくないです。',
		'invalid_transfer_fee'	=> '%sの送金手数料が正しくないです。',
		'invalid_gas_price'		=> '%sのガス価格が正しくないです。',
		'invalid_gas_limit'		=> '%sのガス制限が正しくないです。',

		'update_success'	=> '更新が成功しました。',
	],
];
