<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});
Route::get('/logout', function() {
    Auth::logout();
    return redirect()->route('login');
});
Route::get('/lang', [\App\Http\Controllers\LanguageController::class, 'setLocale'])->name('lang');

Auth::routes();

Route::group(['middleware' => 'auth'], function () {
    # Main
    ## Home
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/profile', [\App\Http\Controllers\CommonController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [\App\Http\Controllers\CommonController::class, 'updateProfile'])->name('profile.update');
    Route::post('ajax/home/getRegisterData', [\App\Http\Controllers\HomeController::class, 'ajax_getRegisterData']);
    Route::post('ajax/home/getUserTransferData', [\App\Http\Controllers\HomeController::class, 'ajax_getUserTransferData'])->name('home.transfer');
    Route::post('ajax/home/getServerInfo', [\App\Http\Controllers\HomeController::class, 'ajax_getServerInfo']);
    Route::post('ajax/home/getTransferFees', [\App\Http\Controllers\HomeController::class, 'ajax_getTransferFees']);

    ## Setting
    Route::get('/setting', [App\Http\Controllers\SettingController::class, 'index'])->name('setting');
    Route::post('/setting/update/master', [App\Http\Controllers\SettingController::class, 'post_updateMaster'])->name('setting.update.master');
    Route::post('/setting/update/crypto', [\App\Http\Controllers\SettingController::class, 'post_updateCrypto'])->name('setting.update.crypto');
    Route::post('/setting/update/maintenance', [\App\Http\Controllers\SettingController::class, 'post_updateMaintenance'])->name('setting.update.maintenance');

    ## Closing
    Route::get('/closing', [\App\Http\Controllers\ClosingController::class, 'index'])->name('closing');
    Route::post('/closing/update', [\App\Http\Controllers\ClosingController::class, 'post_update'])->name('closing.update');

    ## Games
    Route::get('/games', [\App\Http\Controllers\GameController::class, 'index'])->name('games');
    Route::post('ajax/games/search', [\App\Http\Controllers\GameController::class, 'ajax_search']);
    Route::post('ajax/games/getInfo', [\App\Http\Controllers\GameController::class, 'ajax_getInfo']);
    Route::post('ajax/games/add', [\App\Http\Controllers\GameController::class, 'ajax_add']);
    Route::post('ajax/games/edit', [\App\Http\Controllers\GameController::class, 'ajax_edit']);
    Route::post('ajax/games/delete', [\App\Http\Controllers\GameController::class, 'ajax_delete']);

    ## Games Category
    Route::post('ajax/games/category/search', [\App\Http\Controllers\GameCategoryController::class, 'ajax_search']);
    Route::post('ajax/games/category/getInfo', [\App\Http\Controllers\GameCategoryController::class, 'ajax_getInfo']);
    Route::post('ajax/games/category/add', [\App\Http\Controllers\GameCategoryController::class, 'ajax_add']);
    Route::post('ajax/games/category/edit', [\App\Http\Controllers\GameCategoryController::class, 'ajax_edit']);
    Route::post('ajax/games/category/delete', [\App\Http\Controllers\GameCategoryController::class, 'ajax_delete']);

    ### Banks
    Route::post('ajax/bank/search', [\App\Http\Controllers\BankController::class, 'ajax_search']);
    Route::post('ajax/bank/getInfo', [\App\Http\Controllers\BankController::class, 'ajax_getInfo']);
    Route::post('ajax/bank/add', [\App\Http\Controllers\BankController::class, 'ajax_add']);
    Route::post('ajax/bank/edit', [\App\Http\Controllers\BankController::class, 'ajax_edit']);
    Route::post('ajax/bank/delete', [\App\Http\Controllers\BankController::class, 'ajax_delete']);

    # Staff - Manage
    ## Staff
    Route::get('/staff', [App\Http\Controllers\StaffController::class, 'index'])->name('staff');
    Route::get('/staff/add', [\App\Http\Controllers\StaffController::class, 'add'])->name('staff.add');
    Route::get('/staff/edit', [\App\Http\Controllers\StaffController::class, 'edit'])->name('staff.edit');
    Route::get('/staff/download', [\App\Http\Controllers\StaffController::class, 'download'])->name('staff.download');
    Route::post('/staff/add', [\App\Http\Controllers\StaffController::class, 'post_add'])->name('staff.post.add');
    Route::post('/staff/edit', [\App\Http\Controllers\StaffController::class, 'post_edit'])->name('staff.post.edit');
    Route::post('ajax/staff/search', [\App\Http\Controllers\StaffController::class, 'ajax_search']);
    Route::post('ajax/staff/delete', [\App\Http\Controllers\StaffController::class, 'ajax_delete']);

    # System Manage
    ## System Balances
    Route::get('/system/balances', [\App\Http\Controllers\SystemBalanceController::class, 'index'])->name('system.balance');
    Route::post('ajax/system/getBalance', [\App\Http\Controllers\SystemBalanceController::class, 'ajax_getBalance']);

    ## System Transfer
    Route::get('/system/transfer', [\App\Http\Controllers\SystemTransferController::class, 'index'])->name('system.transfer');
    Route::post('/system/transfer/casino', [\App\Http\Controllers\SystemTransferController::class, 'post_casino'])->name('system.transfer.casino');
    Route::post('/system/transfer/affiliate', [\App\Http\Controllers\SystemTransferController::class, 'post_affiliate'])->name('system.transfer.affiliate');
    Route::post('ajax/system/transfer/search', [\App\Http\Controllers\SystemTransferController::class, 'ajax_search']);
    Route::post('ajax/system/getAffiliateBalance', [\App\Http\Controllers\SystemTransferController::class, 'ajax_getAffiliateBalance']);

    ## User Transfer
    Route::get('/users/transfer', [\App\Http\Controllers\UsersTransferController::class, 'index'])->name('users.transfer');
    Route::get('/users/transfer/download', [\App\Http\Controllers\UsersTransferController::class, 'download'])->name('users.transfer.download');
    Route::post('/users/transfer', [\App\Http\Controllers\UsersTransferController::class, 'post_request'])->name('users.transfer.request');
    Route::post('ajax/users/transfer/search', [\App\Http\Controllers\UsersTransferController::class, 'ajax_search']);

    ## Affiliate Settle
    Route::get('/affiliate/settle', [\App\Http\Controllers\AffiliateSettleController::class, 'index'])->name('affiliate.settle');
    Route::get('/affiliate/settle/add', [\App\Http\Controllers\AffiliateSettleController::class, 'add'])->name('affiliate.settle.add');
    Route::get('/affiliate/settle/detail', [\App\Http\Controllers\AffiliateSettleController::class, 'detail'])->name('affiliate.settle.detail');
    Route::post('ajax/affiliate/settle/search', [\App\Http\Controllers\AffiliateSettleController::class, 'ajax_search']);
    Route::post('ajax/affiliate/settle/updateBasic', [\App\Http\Controllers\AffiliateSettleController::class, 'ajax_updateBasic']);
    Route::post('ajax/affiliate/settle/updateStatus', [\App\Http\Controllers\AffiliateSettleController::class, 'ajax_updateStatus']);
    Route::post('ajax/affiliate/settle/uploadCsvFile', [\App\Http\Controllers\AffiliateSettleController::class, 'ajax_uploadCsvFile']);
    Route::post('ajax/affiliate/settle/checkCsvData', [\App\Http\Controllers\AffiliateSettleController::class, 'ajax_checkCsvData']);
    Route::post('ajax/affiliate/settle/saveCsvData', [\App\Http\Controllers\AffiliateSettleController::class, 'ajax_saveCsvData']);
    Route::post('ajax/affiliate/settle/saveCsvSettleData', [\App\Http\Controllers\AffiliateSettleController::class, 'ajax_saveCsvSettleData']);
    Route::post('ajax/affiliate/settle/saveSettleData', [\App\Http\Controllers\AffiliateSettleController::class, 'ajax_saveSettleData']);
    Route::post('ajax/affiliate/settle/calcCommission', [\App\Http\Controllers\AffiliateSettleController::class, 'ajax_calcCommission']);
    Route::post('ajax/affiliate/settle/saveCommission', [\App\Http\Controllers\AffiliateSettleController::class, 'ajax_saveCommission']);
    Route::post('ajax/affiliate/settle/loadCommission', [\App\Http\Controllers\AffiliateSettleController::class, 'ajax_loadCommission']);
    Route::post('ajax/affiliate/settle/saveBalances', [\App\Http\Controllers\AffiliateSettleController::class, 'ajax_saveBalances']);
    Route::post('ajax/affiliate/settle/loadBalances', [\App\Http\Controllers\AffiliateSettleController::class, 'ajax_loadBalances']);
    Route::post('ajax/affiliate/settle/finishSettle', [\App\Http\Controllers\AffiliateSettleController::class, 'ajax_finishSettle']);
    Route::post('ajax/affiliate/settle/loadSettleData', [\App\Http\Controllers\AffiliateSettleController::class, 'ajax_loadSettleData']);
    Route::post('ajax/affiliate/settle/loadAnnounces', [\App\Http\Controllers\AffiliateSettleController::class, 'ajax_loadAnnounces']);

    ## Users
    Route::get('/users', [App\Http\Controllers\UsersController::class, 'index'])->name('users');
    Route::get('/users/detail', [App\Http\Controllers\UsersController::class, 'detail'])->name('users.detail');
    Route::post('ajax/users/search', [App\Http\Controllers\UsersController::class, 'ajax_search']);
    Route::post('ajax/users/getBalance', [\App\Http\Controllers\UsersController::class, 'ajax_getBalance']);
    Route::post('ajax/users/updateStatus', [\App\Http\Controllers\UsersController::class, 'ajax_updateStatus']);
    Route::post('ajax/users/delete', [\App\Http\Controllers\UsersController::class, 'ajax_delete']);
    Route::post('ajax/users/register', [\App\Http\Controllers\UsersController::class, 'ajax_register']);

    ### Users History
    Route::get('/users/history/deposit', [App\Http\Controllers\UsersController::class, 'deposit_list'])->name('history-users-deposit');
    Route::get('/users/history/withdraw', [App\Http\Controllers\UsersController::class, 'withdraw_list'])->name('history-users-withdraw');
    Route::get('/users/history/transfer', [App\Http\Controllers\UsersController::class, 'transfer_list'])->name('history-users-transfer');
    Route::get('/users/history/download', [\App\Http\Controllers\UsersController::class, 'download'])->name('users.history.download');
    Route::post('ajax/users/history/deposit/search', [App\Http\Controllers\UsersController::class, 'ajax_deposit_search']);
    Route::post('ajax/users/history/withdraw/search', [App\Http\Controllers\UsersController::class, 'ajax_withdraw_search']);

    # Requests
    ## Kyc Requests
    Route::get('/requests/kyc', [\App\Http\Controllers\KycController::class, 'index'])->name('requests.kyc');
    Route::get('/requests/kyc/csv', [\App\Http\Controllers\KycController::class, 'csv'])->name('requests.kyc.csv');
    Route::get('/requests/kyc/download', [\App\Http\Controllers\KycController::class, 'download'])->name('requests.kyc.download');
    Route::post('ajax/requests/kyc/search', [\App\Http\Controllers\KycController::class, 'ajax_search']);
    Route::post('ajax/requests/kyc/updateStatus', [\App\Http\Controllers\KycController::class, 'ajax_updateStatus']);
    Route::post('ajax/requests/kyc/getIdentityList', [\App\Http\Controllers\KycController::class, 'ajax_getIdentityList']);

    ## Withdraw Requests
    Route::get('traderWithdraw/req-outline', [\App\Http\Controllers\TraderWithdrawController::class, 'index'])->name('traderwithdraw.request-outline');
    Route::post('ajax/traderWithdraw/request-outline', [\App\Http\Controllers\TraderWithdrawController::class, 'withdrawRequestOutline']);

    Route::get('traderWithdraw/req-list', [\App\Http\Controllers\TraderWithdrawController::class, 'withdrawRequestList'])->name('crypto.withdraw.request-list');
    Route::post('ajax/traderWithdraw/request-list', [\App\Http\Controllers\TraderWithdrawController::class, 'withdrawRequestListData']);
    Route::post('ajax/traderWithdraw/withdraw-complete', [\App\Http\Controllers\TraderWithdrawController::class, 'withdrawComplete']);
    Route::post('ajax/traderWithdraw/withdraw-cancel', [\App\Http\Controllers\TraderWithdrawController::class, 'withdrawCancel']);

    # Statistics
    ## Profits
    Route::get('/statistics/profits/casino', [\App\Http\Controllers\ProfitsController::class, 'casino'])->name('statistics.profits.casino');
    Route::get('/statistics/profits/wallet', [\App\Http\Controllers\ProfitsController::class, 'wallet'])->name('statistics.profits.wallet');
    Route::get('/statistics/profits/detail', [\App\Http\Controllers\ProfitsController::class, 'detail'])->name('statistics.profits.detail');
    Route::get('/statistics/profits/all', [\App\Http\Controllers\ProfitsController::class, 'all'])->name('statistics.profits.all');
    Route::post('ajax/statistics/profits/search', [\App\Http\Controllers\ProfitsController::class, 'ajax_search']);

    ## GasUsage
    Route::get('/statistics/gas_usage', [\App\Http\Controllers\GasUsageController::class, 'index'])->name('statistics.gas_usage');
    Route::get('/statistics/gas_usage/download', [\App\Http\Controllers\GasUsageController::class, 'download'])->name('statistics.gas_usage.download');
    Route::post('ajax/statistics/gas_usage/search', [\App\Http\Controllers\GasUsageController::class, 'ajax_search']);

    # CMS
    # 一括通知
    Route::get('/cms/notify', [\App\Http\Controllers\CMSController::class, 'notify'])->name('cms.notify');
    Route::post('/cms/notify/modify', [\App\Http\Controllers\CMSController::class, 'notify_modify'])->name('cms.notify_modify');
    Route::post('/cms/notify/modifycolor', [\App\Http\Controllers\CMSController::class, 'notify_modifycolor'])->name('cms.notify_modifycolor');

    ## イベント
    Route::get('/cms/mail', [\App\Http\Controllers\MailController::class, 'mail'])->name('cms.mail');
    Route::get('/cms/mail/detail', [\App\Http\Controllers\MailController::class, 'detail'])->name('cms.mail.detail');
    Route::post('ajax/cms/mail/search', [\App\Http\Controllers\MailController::class, 'ajax_mailSearch']);
    Route::post('ajax/cms/mail/add', [\App\Http\Controllers\MailController::class, 'ajax_addMails']);

    ## イベント
    Route::get('/cms/event', [\App\Http\Controllers\EventController::class, 'event'])->name('cms.event');
    Route::post('ajax/cms/event/search', [\App\Http\Controllers\EventController::class, 'ajax_eventSearch']);
    Route::post('ajax/cms/event/getInfo', [\App\Http\Controllers\EventController::class, 'ajax_getEventInfo']);
    Route::post('ajax/cms/event/add', [\App\Http\Controllers\EventController::class, 'ajax_addEvent']);
    Route::post('ajax/cms/event/edit', [\App\Http\Controllers\EventController::class, 'ajax_editEvent']);
    Route::post('ajax/cms/event/delete', [\App\Http\Controllers\EventController::class, 'ajax_deleteEvent']);

    ## FAQ
    Route::get('/cms/faq', [\App\Http\Controllers\CMSController::class, 'faq'])->name('cms.faq');
    Route::post('ajax/cms/faq/search', [\App\Http\Controllers\CMSController::class, 'ajax_faqSearch']);
    Route::post('ajax/cms/faq/getInfo', [\App\Http\Controllers\CMSController::class, 'ajax_getFAQInfo']);
    Route::post('ajax/cms/faq/add', [\App\Http\Controllers\CMSController::class, 'ajax_addFAQ']);
    Route::post('ajax/cms/faq/edit', [\App\Http\Controllers\CMSController::class, 'ajax_editFAQ']);
    Route::post('ajax/cms/faq/delete', [\App\Http\Controllers\CMSController::class, 'ajax_deleteFAQ']);

    ## FAQ Categories
    Route::get('/cms/faq_categories', [\App\Http\Controllers\CMSController::class, 'faq_categories'])->name('cms.faq_categories');
    Route::post('ajax/cms/faq_categories/search', [\App\Http\Controllers\CMSController::class, 'ajax_faqCategorySearch']);
    Route::post('ajax/cms/faq_categories/getInfo', [\App\Http\Controllers\CMSController::class, 'ajax_getFAQCategoryInfo']);
	Route::post('ajax/cms/faq_categories/getAll', [\App\Http\Controllers\CMSController::class, 'ajax_getAllCategories']);
    Route::post('ajax/cms/faq_categories/add', [\App\Http\Controllers\CMSController::class, 'ajax_addFAQCategory']);
    Route::post('ajax/cms/faq_categories/edit', [\App\Http\Controllers\CMSController::class, 'ajax_editFAQCategory']);
    Route::post('ajax/cms/faq_categories/delete', [\App\Http\Controllers\CMSController::class, 'ajax_deleteFAQCategory']);

    ## Inquiry
    Route::get('/cms/inquiry', [\App\Http\Controllers\CMSController::class, 'inquiry'])->name('cms.inquiry');
    Route::post('ajax/cms/inquiry/search', [\App\Http\Controllers\CMSController::class, 'ajax_inquirySearch']);
    Route::post('ajax/cms/inquiry/getInfo', [\App\Http\Controllers\CMSController::class, 'ajax_getInquiryInfo']);
    Route::post('ajax/cms/inquiry/edit', [\App\Http\Controllers\CMSController::class, 'ajax_editInquiry']);

    # Blockchain
    ## Balances
    Route::get('/wallets/balances', [\App\Http\Controllers\WalletsController::class, 'balance'])->name('wallets.balance');
    Route::get('/wallets/balances/detail', [\App\Http\Controllers\WalletsController::class, 'balance_detail'])->name('wallets.balance.detail');
    Route::post('ajax/wallets/getBalanceSummary', [\App\Http\Controllers\WalletsController::class, 'ajax_getBalanceSummary']);
    Route::post('ajax/wallets/refreshBalance', [\App\Http\Controllers\WalletsController::class, 'ajax_refreshBalance']);
    Route::post('ajax/wallets/getTotalBalance', [\App\Http\Controllers\WalletsController::class, 'ajax_getTotalBalance']);
    Route::post('ajax/withdraw/getWalletBalances', [\App\Http\Controllers\WithdrawController::class, 'ajax_getWalletBalances']);

    ## Wallets
    Route::get('/wallets/lists', [\App\Http\Controllers\WalletsController::class, 'index'])->name('wallets.list');
    Route::post('ajax/wallets/search', [\App\Http\Controllers\WalletsController::class, 'ajax_search']);
    Route::post('ajax/wallets/checkAddress', [\App\Http\Controllers\WalletsController::class, 'ajax_checkAddress']);
    Route::post('ajax/wallets/addWallet', [\App\Http\Controllers\WalletsController::class, 'ajax_addWallet']);
    Route::post('ajax/wallets/setPrivateKey', [\App\Http\Controllers\WalletsController::class, 'ajax_setPrivateKey']);
    Route::post('ajax/wallets/delete', [\App\Http\Controllers\WalletsController::class, 'ajax_delete']);
    Route::post('ajax/wallets/getWalletList', [\App\Http\Controllers\WalletsController::class, 'ajax_getWalletList']);
    Route::post('ajax/wallet/specify', [\App\Http\Controllers\WalletsController::class, 'ajax_specify']);

    ## Transactions
    Route::get('/transactions', [\App\Http\Controllers\TransactionController::class, 'index'])->name('transactions');
    Route::get('/transactions/download', [\App\Http\Controllers\TransactionController::class, 'download'])->name('transactions.download');
    Route::post('ajax/transactions/search', [\App\Http\Controllers\TransactionController::class, 'ajax_search']);

    ## Operations
    ### Transfer
    Route::get('/transfer', [\App\Http\Controllers\TransferController::class, 'index'])->name('transfer');
    Route::post('ajax/transfer/makeTransaction', [\App\Http\Controllers\TransferController::class, 'ajax_makeTransaction']);
    Route::post('ajax/transfer/generateQrCodes', [\App\Http\Controllers\TransferController::class, 'ajax_generateQrCodes']);
    Route::post('ajax/transfer/doFinish', [\App\Http\Controllers\TransferController::class, 'ajax_doFinish']);
});


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
