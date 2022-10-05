<?php

return [

	'title'		=> 'ホーム',

	'kyc'	=> [
		'title'				=> 'KYC申請一覧',
		'download_title'	=> 'ダウンロード',
		'no'				=> '番号',
        'email'				=> 'メール',
		'userid'			=> 'アカウントID',
        'nickname'			=> 'ニックネーム',
        'gender'			=> '性別',
        'mobile'			=> '携帯番号',
        'status'			=> '認証状態',
        'reged_at'			=> '登録日時',
		'actions'			=> '',

		'identity'			=> '認証ファイル',
		'filesize'			=> 'ファイルサイズ',
		
		'op_success'		=> 'KYC認証状態の更新が成功されました。',
	],

	'withdraw'	=> [
		'title'		    	=> '出金申請(仮想通貨)',

        'currency'			=> '仮想通貨',
        'request_count'		=> '申請件数',
        'request_amount'    => '申請金額',
        'queue_count'		=> 'キューの件数',
        'queue_amount'		=> 'キューの総金額',
        'failed_count'		=> '失敗件数',
        'failed_amount'		=> '失敗金額',
        'action'			=> '',

        'detail_title'      => '出金申請リスト(%s)',
        'no'                => 'No',
        'email'             => 'メール',
		'userid'			=> 'アカウントID',
        'nickname'			=> 'ニックネーム',
        'recv_address'      => '受信アドレス',
        'withdraw_amount'   => '出金金額',
        'requested_time'    => '申請時間',
        'apply'             => '出金',

		'wt_balance'		=> '出金ウォレットの残高',
		'gt_balance'		=> 'ガスタンクの残高',
		'selected_balance'	=> '選択されたの金額',
		'need_gas'			=> '必要なガス',

		'cancel_title'		=> 'キャンセル理由を入力ください。',
		'remark'			=> '備考',

		'no_wallet'				=> 'ウォレットがありません。',
		'get_balance_failed'	=> '残高の取得に失敗されました。',
		'ask_approve'			=> '本当に承認しますか？',
		'ask_cancel'			=> '本当にキャンセルしますか？',
		'op_success'			=> '操作が成功されました。',
		'op_failed'				=> '操作が失敗されました。',
	],

	'withdraw_cash'	=> [
		'title'				=> '出金申請(現金)',
		'approve_title'		=> '送金手数料を入力ください。',
		'cancel_title'		=> 'キャンセル理由を入力ください。',
		
		'no'				=> '番号',
		'userid'			=> 'アカウントID',
		'nickname'			=> 'ニックネーム',
		'bank_name'			=> '銀行名',
		'branch_name'		=> '支店名',
		'type'				=> '普通/当座',
		'account_number'	=> '口座番号',
		'account_name'		=> '口座名義',
		'amount'			=> '金額',
		'transfer_fee'		=> '送金手数料',
		'status'			=> '状態',
		'requested_at'		=> '申請日時',
		'remark'			=> '備考',
		'action'			=> '',

		'ask_approve'			=> '本当に処理完了しますか？',
		'ask_cancel'			=> '本当にキャンセルしますか？',
		'op_success'			=> '操作が成功されました。',
		'op_failed'				=> '操作が失敗されました。',
	],
];
