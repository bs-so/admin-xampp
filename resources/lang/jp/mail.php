<?php

return [

	'title'			=> '一斉メール告知',
	'detailtitle'	=> 'メール本文',
	'add_title'		=> '一斉メールの追加',
	'userlist'		=> 'ユーザーリスト',

	'table'		=> [
		'no'			=> '番号',
		'title'			=> 'タイトル',
		'content'		=> '本文',
		'type'			=> '送信対象',
		'total'			=> '全体数',
		'success'		=> '成功数',
		'actions'		=> '',
		'search_at'		=> '期間',
		'process'		=> '進捗',
		'userlist'		=> 'ユーザーファイル',
		'speciallist'		=> 'ユーザーID',
	],

	'message'	=> [
		'created_at' => 'そのメールは%sに作成されました。',
	],

	'usertype'		=> [
		'all'		=> '全ユーザー',
		'login'		=> '最終ログイン',
		'reg'		=> '登録日',
		'csv'		=> 'CSVユーザー',
		'special'		=> '特定ユーザー',
	],

	'affiliate_settle'	=> [
		'announce'		=> '【%s】謝礼金配当に関したお知らせ',

		'msg1'			=> '%s様',
		'msg2'			=> '【%s】から謝礼金配当が実施されました。',
		'msg3'			=> '以下の情報を確認お願い致します。',

		'no'			=> '番号',
		'currency'		=> '仮想通貨',
		'commission'	=> '配当金',
		'prev_balance'	=> '以前残高',
		'next_balance'	=> '以後残高',

		'msg4'			=> '※本メールは送信専用です。',
	],

	'transfer_sender'	=> [
		'title'			=> '【%s】送金のお知らせ',

		'msg1'			=> '%s様',
		'msg2'			=> '【%s】からユーザー間送金が進み残高が削減されました。',
		'msg3'			=> '以下の情報を確認お願い致します。',

		'sender'		=> '送信者 : ',
		'receiver'		=> '受信者 : ',
		'currency'		=> '仮想通貨 : ',
		'amount'		=> '金額: ',
		'result'		=> '現在の残高 : ',

		'msg4'			=> '※本メールは送信専用です。',
	],

	'transfer_receiver'	=> [
		'title'			=> '【%s】送金のお知らせ',

		'msg1'			=> '%s様',
		'msg2'			=> '【%s】ユーザー間送金が進み残高が増加されました。',
		'msg3'			=> '以下の情報を確認お願い致します。',

		'sender'		=> '送信者 : ',
		'receiver'		=> '受信者 : ',
		'currency'		=> '仮想通貨 : ',
		'amount'		=> '金額: ',
		'result'		=> '現在の残高 : ',

		'msg4'			=> '※本メールは送信専用です。',
	],
];
