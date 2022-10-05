<?php

/////////////////////////////////////////////////////////////////////////////
// API Constants
/////////////////////////////////////////////////////////////////////////////
define('MAIN_CURRENCY', 'USD');
define('MAIN_CURRENCY_DECIMALS', 2);
define('EX_RATE_DECIMALS', 8);
define('MINIMUM_BALANCE_DECIMALS', 12);
define('JPY_CURRENCY', 'JPY');
define('EX_RATE_SERVER', '');

# Realtime Logs
define('LOGS_TYPE_USER_DEPOSIT', 1);
define('LOGS_TYPE_USER_WITHDRAW', 2);
define('LOGS_TYPE_MANAGER_DEPOSIT', 3);
define('LOGS_TYPE_MANAGER_WITHDRAW', 4);

# Disposable Wallets
define('DISPOSABLE_STATUS_NEED', 0);
define('DISPOSABLE_STATUS_CONFIRMED', 1);

# Charts
define('PROFIT_CHART_BAR_COUNT', 12);

# Crypto Currency API
define('API_HOST', 'https://admin.bicorn.world/api/?1_0_0');
define('API_HOST2', 'https://admin.bicorn.world/api/?2_0_0');
define('API_HOST3', 'https://admin.bicorn.world/api/?3_0_0');
define('API_RETRY_COUNT', 5);

define('GAS_UNIT', '1000000000');

define('COIN_TEST_NET', 'TESTNET');
define('COIN_REAL_NET', 'REALNET');
define('COIN_NET', (env('APP_ENV') == 'local' ? COIN_TEST_NET : COIN_REAL_NET));

define('HTTP_METHOD_GET', 'GET');
define('HTTP_METHOD_POST', 'POST');

define('BTC_CONFIRM_URL', (COIN_NET == COIN_REAL_NET ? 'https://live.blockcypher.com/btc/tx/' : 'https://live.blockcypher.com/btc-testnet/tx/'));
define('ETH_CONFIRM_URL', (COIN_NET == COIN_REAL_NET ? 'https://etherscan.io/tx/' : 'https://ropsten.etherscan.io/tx/'));
define('BCH_CONFIRM_URL', (COIN_NET == COIN_REAL_NET ? 'https://explorer.bitcoin.com/bch/tx/' : 'https://explorer.bitcoin.com/bch/tx/'));

define('WALLET_STATUS_NEW', 1);
define('WALLET_STATUS_OLD', 0);

define('WITHDRAW_QUEUE_STATUS_REQUESTED', 0);
define('WITHDRAW_QUEUE_STATUS_FINISHED', 1);
define('WITHDRAW_QUEUE_STATUS_PROCESSING', 2);
define('WITHDRAW_QUEUE_STATUS_FAILED', 3);

///////// CASINO API /////////
define('CASINO_SERVER_ADDRESS', env('APP_ENV') == 'local' ? 'https://bicorn.systems' : 'https://bicorn.systems');
define('CASINO_GAMEINFO_URL', CASINO_SERVER_ADDRESS . '/api/gameinfo');
define('CASINO_SESSION_URL', CASINO_SERVER_ADDRESS . '/api/session');
define('CASINO_USERINFO_URL', CASINO_SERVER_ADDRESS . '/api/userinfo');
define('CASINO_USERINFO_CHECK_URL', CASINO_SERVER_ADDRESS . '/api/userinfo/check');
define('CASINO_RESET_PASS_URL', CASINO_SERVER_ADDRESS . '/api/userinfo/password/reset');
define('CASINO_BALANCE_URL', CASINO_SERVER_ADDRESS . '/api/balance');
define('CASINO_LAUNCH_REAL_URL', CASINO_SERVER_ADDRESS . '/api/real');
define('CASINO_LAUNCH_DEMO_URL', CASINO_SERVER_ADDRESS . '/api/demo');
define('CASINO_CURRENCY_URL', CASINO_SERVER_ADDRESS . '/api/currency');
define('CASINO_NOTICE_URL', CASINO_SERVER_ADDRESS . '/api/notification');
define('CASINO_ACCOUNTING_URL', CASINO_SERVER_ADDRESS . '/api/accounting');

define('CASINO_REGISTER_SUCCESS', 0);
define('CASINO_REGISTER_EXIST', 1);
define('CASINO_REGISTER_FAILED', 2);

///////// AFFILIATE SETTLE /////////
define('PROJECT_BEGIN_DATE', '2021-04-01');

define('SERVER_INFO_TOTAL_RAM', 'TOTAL_RAM');
define('SERVER_INFO_FREE_RAM', 'FREE_RAM');

// Transaction fees
define('BTC_FEE_URL', 'https://bitcoinfees.earn.com/api/v1/fees/recommended');
define('ETH_FEE_URL', 'https://ethgasstation.info/json/ethgasAPI.json');

define('USER_SITE_URL', env('APP_ENV') == 'local' ? 'http://192.168.5.84:8000' : 'https://bicorn.world');
