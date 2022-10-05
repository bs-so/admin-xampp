<?php

return [

    'title'			=> 'ユーザー一覧',
	'detail_title'	=> 'ユーザー詳細(%s)',
	
	'table'			=>	[
        'no'            => 'No',
		'userid'		=> 'アカウントID',
        'email'         => 'メール',
		'firstname'		=> '名',
		'lastname'		=> '姓',
        'nickname'		=> 'ニックネーム',
		'referrer'		=> '紹介者',
        'password'      => 'パスワード',
        'birthday'      => '生年月日',
        'gender'        => '性別',
        'country'       => '国コード',
        'mobile'        => '携帯番号',
		'city'			=> '都市',
        'postal_code'   => '郵便番号',
        'address'       => '住所',
        'kyc_status'    => '認証状態',
        'lang'          => '言語',
        'status'        => '状態',
        'avatar'        => 'アバター',

        'pass_conf'		=> 'パスワード確認',
        'reged_at'		=> '登録日時',
        'actions'		=> '',
		'currency'		=> '仮想通貨',
		'balance'		=> '残高',
	],

	'nav'			=> [
		'balance'		=> '残高',
		'deposit'		=> '入金履歴',
		'withdraw'		=> '出金履歴',
		'send'			=> '送金履歴',
		'receive'		=> '着金履歴',
	],

	'section'		=> [
		'profile'		=> 'プロファイル',
	],

	'message'		=>	[
		'edit_success'	=> 'ユーザー編集が成功されました。',

		'register_success'	=> '登録に成功されました。',
		'register_exist'	=> '登録済み状態です。',
		'register_failed'	=> '登録に失敗されました。',
		'delete_confirm'	=> '本当に削除したいですか？<br>この操作は元に戻すことができません。'
	],

	'delete_title'		=> 'トレード削除',

];
