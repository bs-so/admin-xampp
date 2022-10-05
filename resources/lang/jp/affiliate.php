<?php

return [

	'settle'		=> [
		'list_title'		=> 'アフィ清算一覧',
		'add_title'			=> 'アフィ清算の追加',
		'detail_title'		=> 'アフィ清算の詳細',

		'no'				=> '番号',
		'operator'			=> '清算者',
		'begin_date'		=> '開始日付',
		'end_date'			=> '終了日付',
		'remark'			=> '備考',
		'use_announce'		=> 'メール通知',
		'announce_status'	=> 'メール通知状態(成功/失敗)',
		'status'			=> '状態',
		'settled_at'		=> '清算日時',
		'actions'			=> '',

		'csv_file'			=> 'CSVファイル',
		'summary'			=> 'コミッション合計',
		'userid'			=> '送金先ユーザーID',
		'email'				=> 'メール',
		'nickname'			=> 'ニックネーム',
		'currency'			=> '通貨の種類',
		'amount'			=> '金額',
		'select_status'		=> '選択状態',
		'information'		=> '清算情報',

		'steps_title1'		=> 'CSVファイルの選択',
		'steps_title2'		=> 'エントリーの確認',
		'steps_title3'		=> 'コミッション一覧',
		'steps_title4'		=> '残高確認',
		'steps_title5'		=> '最終確認',

		'icon_title1'		=> 'CSVファイル選択',
		'icon_title2'		=> 'エントリーの確認',
		'icon_title3'		=> 'コミッション',
		'icon_title4'		=> '残高確認',
		'icon_title5'		=> '最終確認',

		'navs_main'			=> '基本情報',
		'navs_settle_data'	=> '清算資料一覧',
		'navs_commission'	=> 'コミッション一覧',
		'navs_balances'		=> '残高一覧',
		'navs_mails'		=> 'メール通知一覧',

		'navs_chk_balance'	=> '残高の確認',
		'navs_chk_user'		=> 'ユーザーの確認',

		'no_item'			=> 'なし',
		'format_error1'		=> 'フォーマットエラー: 【',
		'format_error2'		=> '行】',
		'no_format_error'	=> 'フォーマットエラーがありません。',

		'no_error'			=> '確認OK!',
		'error_balance'		=> '不足',
		'csv_balance_sum'	=> 'CSV金額合計: ',
		'current_balance'	=> '現在の残高: ',
		'error_user'		=> ': 登録なし',
		'csv_file_invalid'	=> 'CSVファイルは正確ないです。',
		'load_failed'		=> '資料積載に失敗しました。',
		'calc_commission'	=> 'コミッション計算に成功されました。',
	],

	'commission'	=> [
		'no'				=> '番号',
		'userid'			=> 'アカウントID',
		'nickname'			=> 'ニックネーム',
		'balance'			=> '現在の残高',
		'total_user'		=> 'ユーザー数',
		'total_commission'	=> 'コミッション合計',
		'percent'			=> 'パーセント',
		'commission_curr'	=> 'コミッション(本回清算)',
		'commission_prev'	=> 'コミッション(以前清算)',
		'currency'			=> '通貨',
		'created_at'		=> '実施日時',
	],

	'balance'		=> [
		'no'				=> '番号',
		'userid'			=> 'アカウントID',
		'nickname'			=> 'ニックネーム',
		'currency'			=> '仮想通貨',
		'prev_balance'		=> '以前残高',
		'next_balance'		=> '以後残高',
		'created_at'		=> '実施日時',
	],

	'announce'		=> [
		'no'				=> '番号',
		'userid'			=> 'アカウントID',
		'nickname'			=> 'ニックネーム',
		'is_sent'			=> '発送状態',
		'sent_at'			=> '発送日時',
	],

	'message'	=> [
	],
];
