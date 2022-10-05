<?php
/**
 * Casino Admin Panel : Master data
 * 2021.04.19 Coded by RedSpider
 *
 * @author
 */
# Master Data(Option, Value, Type, Suffix)
define('MASTER_TYPE_VALUE', 'MASTER_TYPE_VALUE');
define('MASTER_TYPE_JSON', 1);

define('MAINTENANCE_MODE', 'MAINTENANCE_MODE');
define('DEPOSIT_FEE', 'DEPOSIT_FEE');
define('TRANSFER_FEE', 'TRANSFER_FEE');
define('WITHDRAW_FEE', 'WITHDRAW_FEE');
define('QR_CODE_SPLIT_SIZE', 'QR_CODE_SPLIT_SIZE');
define('GAS_PRICE_MODE', 'GAS_PRICE_MODE');
define('AUTO_USER_WITHDRAW', 'AUTO_USER_WITHDRAW');
define('RATE_DOWN_SPEED', 'RATE_DOWN_SPEED');
define('PRNG_PRIME_VALUE', 'PRNG_PRIME_VALUE');
define('USERID_PREFIX', 'USERID_PREFIX');
define('INQUIRY_SUPPORT_EMAIL', 'INQUIRY_SUPPORT_EMAIL');

$MasterData = array(
	MAINTENANCE_MODE        => ['MAINTENANCE_MODE', 0, MASTER_TYPE_VALUE, ''],
	DEPOSIT_FEE             => ['DEPOSIT_FEE', 0, MASTER_TYPE_VALUE, '%'],
    TRANSFER_FEE            => ['TRANSFER_FEE', 5, MASTER_TYPE_VALUE, '%'],
    WITHDRAW_FEE            => ['WITHDRAW_FEE', 4, MASTER_TYPE_VALUE, '%'],
    QR_CODE_SPLIT_SIZE      => ['QR_CODE_SPLIT_SIZE', 750, MASTER_TYPE_VALUE, ''],
    GAS_PRICE_MODE          => ['GAS_PRICE_MODE', 0, MASTER_TYPE_VALUE, ''],
    AUTO_USER_WITHDRAW      => ['AUTO_USER_WITHDRAW', 0, MASTER_TYPE_VALUE, ''],
    RATE_DOWN_SPEED         => ['RATE_DOWN_SPEED', 60, MASTER_TYPE_VALUE, 's'],
    PRNG_PRIME_VALUE        => ['PRNG_PRIME_VALUE', '[0, 1, 1000]', MASTER_TYPE_VALUE, ''],
    USERID_PREFIX           => ['USERID_PREFIX', 'CS', MASTER_TYPE_VALUE, ''],
    INQUIRY_SUPPORT_EMAIL   => ['INQUIRY_SUPPORT_EMAIL', 'support@bicorn.world', MASTER_TYPE_VALUE, ''],
);

# Status
define('STATUS_INVALID', 0);
define('STATUS_VALID', 1);
$StatusName = array(
    STATUS_INVALID  => ['Disabled', 'danger'],
    STATUS_VALID    => ['Enabled', 'success'],
);

# Status Data
define('STATUS_BANNED', 0);
define('STATUS_ACTIVE', 1);
define('STATUS_REGISTER_FAILED', 2);
$StatusData = array(
    STATUS_BANNED           => ['無効', 'danger'],
    STATUS_ACTIVE           => ['有効', 'success'],
);
$UserStatusData = array(
    STATUS_BANNED           => ['無効', 'danger'],
    STATUS_ACTIVE           => ['有効', 'success'],
    STATUS_REGISTER_FAILED  => ['登録失敗', 'warning'],
);

# Staff Deposit Status
define('STATUS_REQUESTED', 0);
define('STATUS_ACCEPTED', 1);
define('STATUS_PENDING', 2);
define('STATUS_FAILED', 3);
define('STATUS_CANCELLED', 4);
define('STATUS_NO_BALANCE', 5);
$StaffDepositStatus = array(
	STATUS_REQUESTED => ['申請済み', 'danger'],
	STATUS_ACCEPTED  => ['完了', 'success'],
	STATUS_PENDING   => ['処理中', 'primary']
);

$StaffWithdrawStatus = array(
	STATUS_REQUESTED => ['申請済み', 'primary'],
	STATUS_ACCEPTED  => ['完了', 'success'],
	STATUS_PENDING   => ['処理中', 'info'],
	STATUS_FAILED    => ['失敗', 'danger'],
	STATUS_CANCELLED => ['キャンセル', 'warning']
);

# Deposit Queue Status
define('DEPOSIT_QUEUE_STATUS_INIT', 0);
define('DEPOSIT_QUEUE_STATUS_CONFIRMED', 1);

$UsersDepositStatus = array(
	STATUS_REQUESTED => ['申請済み', 'primary'],
	STATUS_ACCEPTED  => ['完了', 'success'],
	STATUS_PENDING   => ['処理中', 'info']
);

$UsersExchangeStatus = array(
    STATUS_REQUESTED => ['申請済み', 'primary'],
    STATUS_ACCEPTED  => ['完了', 'success'],
    STATUS_PENDING   => ['処理中', 'info']
);

$UsersWithdrawStatus = array(
	STATUS_REQUESTED    => ['申請済み', 'primary'],
	STATUS_ACCEPTED     => ['完了', 'success'],
	STATUS_PENDING      => ['処理中', 'info'],
	STATUS_FAILED       => ['失敗', 'danger'],
	STATUS_CANCELLED    => ['キャンセル', 'secondary'],
    STATUS_NO_BALANCE   => ['残高不足', 'warning'],
);

$UsersWithdrawCashStatus = array(
    STATUS_REQUESTED => ['申請済み', 'primary'],
    STATUS_ACCEPTED  => ['完了', 'success'],
    STATUS_PENDING   => ['処理中', 'info'],
    STATUS_FAILED    => ['失敗', 'danger'],
    STATUS_CANCELLED => ['キャンセル', 'warning']
);

# User Gender
define('USER_GENDER_MALE', 0);
define('USER_GENDER_FEMALE', 1);
$UserGenderData = array(
    USER_GENDER_MALE     =>  ['男性', 'primary'],
    USER_GENDER_FEMALE   =>  ['女性', 'info'],
);

# User Role Data
define('USER_ROLE_ADMIN', 1);
define('USER_ROLE_CASINO', 2);
define('USER_ROLE_AFFILIATE', 3);
$UserRoleData = array(
    USER_ROLE_ADMIN         => ['最高管理者', 'danger'],
    USER_ROLE_CASINO        => ['カジノ管理者', 'info'],
    USER_ROLE_AFFILIATE     => ['アフィ管理者', 'primary'],
);

# Crypto Type
define('CRYPTO_TYPE_COIN', 1);
define('CRYPTO_TYPE_TOKEN', 2);
define('CRYPTO_TYPE_CASH', 3);
$CryptoTypeData = array(
    CRYPTO_TYPE_COIN    => ['Coin', 'primary'],
    CRYPTO_TYPE_TOKEN   => ['Token', 'success'],
    CRYPTO_TYPE_CASH    => ['Cash', 'danger'],
);

# Crypto Settings
$CryptoSettingsData = array(
    'BTC'       => ['Bitcoin', CRYPTO_TYPE_COIN, 8, 8, '0.007', '0.007', '0.00003', '0', '0', '0'],
    'ETH'       => ['Ethereum', CRYPTO_TYPE_COIN, 0, 18, '0.035', '0.035', '0', '40000000000', '42000', '0.00168'],
    /*'USDT'      => ['Tether(ERC20)', CRYPTO_TYPE_TOKEN, 0, 6, '10', '20', '0', '40000000000', '100000', '0.04'],
    'BCH'       => ['Bitcoin Cash', CRYPTO_TYPE_COIN, 8, 8, '0.01', '0.01', '0.0001', '0', '0', '0'],*/
);

# Initial Maintenance
$InitialMaintenance = array(
    '1'       => [1, '<div style="letter-spacing: 0.14px;"><span style="font-weight: bolder; color: rgb(44, 44, 44); font-family: inherit; font-size: 2rem; letter-spacing: 0.01rem;"><br></span></div><div style="letter-spacing: 0.14px;"><span style="font-weight: bolder; color: rgb(44, 44, 44); font-family: inherit; font-size: 2rem; letter-spacing: 0.01rem;"><span style="background-color: rgb(255, 0, 255);">Maintenance</span>!</span><br></div><h4 style="font-family: &quot;�q���M�m�p�S Pro&quot;, &quot;Hiragino Kaku Gothic Pro&quot;, ���C���I, Meiryo, Osaka, &quot;�l�r �o�S�V�b�N&quot;, &quot;MS PGothic&quot;, sans-serif; letter-spacing: 0.14px;">We will come back soon!</h4>', 'jp'],
    '2'       => [2, '<div style="letter-spacing: 0.14px;"><div style="letter-spacing: 0.14px;"><span style="font-weight: bolder; color: rgb(44, 44, 44); font-family: inherit; font-size: 2rem; letter-spacing: 0.01rem;"><br></span></div><div style="letter-spacing: 0.14px;"><span style="font-weight: bolder; color: rgb(44, 44, 44); font-family: inherit; font-size: 2rem; letter-spacing: 0.01rem;">Maintenance!</span><br></div><h4 style="font-family: &quot;�q���M�m�p�S Pro&quot;, &quot;Hiragino Kaku Gothic Pro&quot;, ���C���I, Meiryo, Osaka, &quot;�l�r �o�S�V�b�N&quot;, &quot;MS PGothic&quot;, sans-serif; letter-spacing: 0.14px;">We will come back soon!</h4></div>', 'en'],
);

# Languages
define('LANG_EN', 'en');
define('LANG_JP', 'jp');
$Languages = array(
    LANG_EN     => ['ui.lang.en', 'en', 'primary', 'English'],
    LANG_JP     => ['ui.lang.jp', 'jp', 'success', '日本語'],
);

# Wallet Type
define('WALLET_TYPE_COLD', 1);
define('WALLET_TYPE_DEPOSIT', 2);
define('WALLET_TYPE_WITHDRAW', 3);
define('WALLET_TYPE_GASTANK', 4);
$WalletTypeData = array(
    WALLET_TYPE_COLD        => ['コールド', 'info', 'CT-'],
    WALLET_TYPE_DEPOSIT     => ['入金用', 'primary', 'DT-'],
    WALLET_TYPE_WITHDRAW    => ['出金用', 'danger', 'WT-'],
    WALLET_TYPE_GASTANK     => ['ガスタンク', 'warning', 'GT-'],
);

# Wallet Specified
define('WALLET_SPECIFIED_NONE', 0);
define('WALLET_SPECIFIED_DEPOSIT', 1);
define('WALLET_SPECIFIED_WITHDRAW', 2);
define('WALLET_SPECIFIED_GASTANK', 3);
$WalletSpecifiedData = array(
    WALLET_SPECIFIED_NONE       => ['', 'primary'],
    WALLET_SPECIFIED_DEPOSIT    => ['入金', 'primary'],
    WALLET_SPECIFIED_WITHDRAW   => ['出金', 'danger'],
    WALLET_SPECIFIED_GASTANK    => ['ガスタンク', 'warning'],
);

# Transfer Status
define('TRANSFER_STATUS_REQUESTED', 0);
define('TRANSFER_STATUS_FINISHED', 1);
define('TRANSFER_STATUS_SENT', 2);
define('TRANSFER_STATUS_PENDING', 3);
define('TRANSFER_STATUS_FAILED', 4);
define('TRANSFER_STATUS_CANCELLED', 5);
$TransferStatusData = array(
    TRANSFER_STATUS_REQUESTED       => ['申請', 'primary'],
    TRANSFER_STATUS_PENDING         => ['発送中', 'primary'],
    TRANSFER_STATUS_SENT            => ['処理中', 'info'],
    TRANSFER_STATUS_FINISHED        => ['完了', 'success'],
    TRANSFER_STATUS_FAILED          => ['失敗', 'danger'],
    TRANSFER_STATUS_CANCELLED       => ['キャンセル', 'warning'],
);

# Gas Price Mode
# Use Gas Price Mode
define('GASPRICE_MANUAL', 0);
define('GASPRICE_FAST', 1);
define('GASPRICE_STANDARD', 2);
define('GASPRICE_SAFELOW', 3);
$GasPriceModes = array(
    GASPRICE_MANUAL     => ['手動方式'],
    GASPRICE_FAST       => ['快速方式'],
    GASPRICE_STANDARD   => ['標準方式'],
    GASPRICE_SAFELOW    => ['低廉方式'],
);

# Bank Type
define('BANK_TYPE_NORMAL', 1);
define('BANK_TYPE_CURRENT', 2);
$BankTypeData = array(
    BANK_TYPE_NORMAL    => ['普通', 'primary'],
    BANK_TYPE_CURRENT   => ['当座', 'danger'],
);

# Kyc Status Data
define('KYC_STATUS_NONE', 0);
define('KYC_STATUS_ACTIVE', 1);
define('KYC_STATUS_REQUESTED', 2);
define('KYC_STATUS_BANNED', 3);
$KycStatusData = array(
    KYC_STATUS_NONE         => ['認証要', 'primary'],
    KYC_STATUS_REQUESTED    => ['認証申請', 'info'],
    KYC_STATUS_ACTIVE       => ['認証完了', 'success'],
    KYC_STATUS_BANNED       => ['認証保留', 'danger'],
);

# Profit Type Data
define('PROFIT_TYPE_DEPOSIT', 1);
define('PROFIT_TYPE_TRANSFER', 2);
define('PROFIT_TYPE_WITHDRAW', 3);
$ProfitTypeData = array(
    PROFIT_TYPE_DEPOSIT     => ['入金手数料', 'primary'],
    PROFIT_TYPE_TRANSFER    => ['送金手数料', 'info'],
    PROFIT_TYPE_WITHDRAW    => ['出金手数料', 'danger'],
);

# Inquiry Status
define('INQUIRY_STATUS_REQUESTED', 0);
define('INQUIRY_STATUS_ANSWERED', 1);
$InquiryStatusData = array(
    INQUIRY_STATUS_REQUESTED    => ['要請', 'primary'],
    INQUIRY_STATUS_ANSWERED     => ['回答済み', 'success'],
);

# System Balance Type
define('SYSTEM_BALANCE_TYPE_AFFILIATE', 1);
define('SYSTEM_BALANCE_TYPE_WALLET', 2);
define('SYSTEM_BALANCE_TYPE_CASINO_MANUAL', 3);
define('SYSTEM_BALANCE_TYPE_CASINO_AUTO', 4);
$SystemBalanceTypeData = array(
    SYSTEM_BALANCE_TYPE_AFFILIATE       => ['アフィ側', 'primary'],
    SYSTEM_BALANCE_TYPE_WALLET          => ['ウォレット側', 'success'],
    SYSTEM_BALANCE_TYPE_CASINO_MANUAL   => ['カジノ側(手動)', 'warning'],
    SYSTEM_BALANCE_TYPE_CASINO_AUTO     => ['カジノ側(自動)', 'info'],
);

# System Profit Type
define('SYSTEM_PROFIT_TYPE_CASINO', 1);
define('SYSTEM_PROFIT_TYPE_WALLET', 2);
$SystemProfitTypeData = array(
    SYSTEM_PROFIT_TYPE_CASINO           => ['カジノ側', 'primary'],
    SYSTEM_PROFIT_TYPE_WALLET           => ['ウォレット側', 'danger'],
);

# User Deposit Type
define('USER_DEPOSIT_TYPE_NORMAL', 1);
define('USER_DEPOSIT_TYPE_AFFILIATE_SETTLE', 2);
define('USER_DEPOSIT_TYPE_SYSTEM_TRANSFER', 3);
define('USER_DEPOSIT_TYPE_USER_TRANSFER', 4);
$UserDepositTypeData = array(
    USER_DEPOSIT_TYPE_NORMAL            => ['一般入金', 'primary'],
    USER_DEPOSIT_TYPE_AFFILIATE_SETTLE  => ['アフィ精算による入金', 'info'],
    USER_DEPOSIT_TYPE_SYSTEM_TRANSFER   => ['管理者送金', 'danger'],
    USER_DEPOSIT_TYPE_USER_TRANSFER     => ['ユーザー間送金', 'success'],
);
# User Withdraw Type
define('USER_WITHDRAW_TYPE_NORMAL', 1);
define('USER_WITHDRAW_TYPE_USER_TRANSFER', 2);
$UserWithdrawTypeData = array(
    USER_WITHDRAW_TYPE_NORMAL           => ['一般出金', 'primary'],
    USER_WITHDRAW_TYPE_USER_TRANSFER    => ['ユーザー間送金', 'danger'],
);

# Transfer Direction
define('TRANSFER_DIRECTION_IN', 1);
define('TRANSFER_DIRECTION_OUT', 2);
define('TRANSFER_DIRECTION_SET', 3);
$TransferDirectionData = array(
    TRANSFER_DIRECTION_IN           => ['transfer.direction.in', 'primary'],
    TRANSFER_DIRECTION_OUT          => ['transfer.direction.out', 'danger'],
    TRANSFER_DIRECTION_SET          => ['transfer.direction.set', 'warning'],
);

# イベント表示
define('EVENT_SHOW', 1);
define('EVENT_HIDDEN', 0);
$EventShowTypeData = array(
    EVENT_SHOW    => ['表示', 'primary'],
    EVENT_HIDDEN   => ['非表示', 'danger']
);

# メールタイプ表示
define('MAIL_ALL_USER', 1);
define('MAIL_LOGIN_USER', 2);
define('MAIL_REG_USER', 3);
define('MAIL_CSV_USER', 4);
define('MAIL_SPEC_USER', 5);
$MailSendTypeData = array(
    MAIL_ALL_USER    => ['mail.usertype.all', 'primary'],
    MAIL_LOGIN_USER   => ['mail.usertype.login', 'success'],
    MAIL_REG_USER   => ['mail.usertype.reg', 'danger'],
    MAIL_CSV_USER   => ['mail.usertype.csv', 'info'],
    MAIL_SPEC_USER   => ['mail.usertype.special', 'warning'],
);

# Default Add Balances
$DefaultBalances = array(
    'BTC'   => 10,
    'ETH'   => 100,
);

# Affiliate Mail Announce
define('MAIL_ANNOUNCE_NO', 0);
define('MAIL_ANNOUNCE_YES', 1);
$MailAnnounceData = array(
    MAIL_ANNOUNCE_NO        => ['未通知', 'primary'],
    MAIL_ANNOUNCE_YES       => ['通知', 'danger'],
);

# Announce Status
define('ANNOUNCE_STATUS_INIT', 0);
define('ANNOUNCE_STATUS_SENT', 1);
define('ANNOUNCE_STATUS_FAILED', 2);
$AnnounceStatusData = array(
    ANNOUNCE_STATUS_INIT    => ['待機', 'primary'],
    ANNOUNCE_STATUS_SENT    => ['発送成功', 'success'],
    ANNOUNCE_STATUS_FAILED  => ['発送失敗', 'danger'],
);

# Affiliate Settle Status
define('AFFILIATE_SETTLE_STATUS_INIT', 0);
define('AFFILIATE_SETTLE_STATUS_FINISHED', 1);
define('AFFILIATE_SETTLE_STATUS_BASIC', 2);
define('AFFILIATE_SETTLE_STATUS_LOAD_CSV', 3);
define('AFFILIATE_SETTLE_STATUS_COMMISSION', 4);
define('AFFILIATE_SETTLE_STATUS_CHECK_BALANCE', 5);
define('AFFILIATE_SETTLE_STATUS_CHECK_FINISH', 6);
$AffiliateSettleStatusData = array(
    AFFILIATE_SETTLE_STATUS_INIT                => ['初期', 'primary'],
    AFFILIATE_SETTLE_STATUS_FINISHED            => ['完了', 'success'],
    AFFILIATE_SETTLE_STATUS_BASIC                => ['基礎設定', 'info'],
    AFFILIATE_SETTLE_STATUS_LOAD_CSV            => ['CSVファイル選択', 'primary'],
    AFFILIATE_SETTLE_STATUS_COMMISSION          => ['コミッション', 'danger'],
    AFFILIATE_SETTLE_STATUS_CHECK_BALANCE       => ['残高確認', 'danger'],
    AFFILIATE_SETTLE_STATUS_CHECK_FINISH        => ['最終確認', 'warning'],
);

# Order Select Status
define('ENTRY_SELECT_STATUS_NONE', 0);
define('ENTRY_SELECT_STATUS_SELECTED', 1);
define('ENTRY_SELECT_STATUS_NEXT', 2);
define('ENTRY_SELECT_STATUS_CANCELLED', 3);
$EntrySelectStatusData = array(
    ENTRY_SELECT_STATUS_SELECTED        => ['精算する', 'info'],
    ENTRY_SELECT_STATUS_NEXT            => ['次の', 'warning'],
    ENTRY_SELECT_STATUS_CANCELLED       => ['キャンセル', 'danger'],
);

# Order Settle Status
define('ENTRY_SETTLE_STATUS_NONE', 0);
define('ENTRY_SETTLE_STATUS_FINISHED', 1);
$EntrySettleStatusData = array(
    ENTRY_SETTLE_STATUS_NONE        => ['精算前', 'primary'],
    ENTRY_SETTLE_STATUS_FINISHED    => ['精算済み', 'success'],
);

// Closing
define('CLOSING_STATUS_NOT_APPLIED', 0);
define('CLOSING_STATUS_APPLIED', 1);

$g_masterData = array(
    'StatusName'            => $StatusName,
    'StatusData'            => $StatusData,
    'UserStatusData'        => $UserStatusData,
    'UserGenderData'        => $UserGenderData,
    'UserRoleData'          => $UserRoleData,
    'CryptoTypeData'        => $CryptoTypeData,
    'CryptoSettingsData'    => $CryptoSettingsData,
    'Languages'             => $Languages,
	'MasterData'            => $MasterData,

	'StaffDepositStatus'	=> $StaffDepositStatus,
    'StaffWithdrawStatus'   => $StaffWithdrawStatus,
    'WalletTypeData'        => $WalletTypeData,
    'WalletSpecifiedData'   => $WalletSpecifiedData,
	'UsersDepositStatus'    => $UsersDepositStatus,
	'UsersExchangeStatus'	=> $UsersExchangeStatus,
    'UsersWithdrawStatus'	=> $UsersWithdrawStatus,
    'UsersWithdrawCashStatus'	=> $UsersWithdrawCashStatus,

    'TransferStatusData'    => $TransferStatusData,
    'GasPriceModes'         => $GasPriceModes,
    'BankTypeData'          => $BankTypeData,
    'KycStatusData'         => $KycStatusData,
    'ProfitTypeData'        => $ProfitTypeData,

    'InquiryStatusData'     => $InquiryStatusData,
    'UserDepositTypeData'   => $UserDepositTypeData,
    'UserWithdrawTypeData'  => $UserWithdrawTypeData,
    'SystemBalanceTypeData' => $SystemBalanceTypeData,
    'SystemProfitTypeData'  => $SystemProfitTypeData,
    'TransferDirectionData' => $TransferDirectionData,
    'EventShowTypeData'     => $EventShowTypeData,
    'MailSendTypeData'      => $MailSendTypeData,
    'DefaultBalances'       => $DefaultBalances,

    'MailAnnounceData'      => $MailAnnounceData,
    'AnnounceStatusData'    => $AnnounceStatusData,
    'AffiliateSettleStatusData' => $AffiliateSettleStatusData,
    'EntrySelectStatusData' => $EntrySelectStatusData,
    'EntrySettleStatusData' => $EntrySettleStatusData,

    'InitialMaintenance'    => $InitialMaintenance,
);
