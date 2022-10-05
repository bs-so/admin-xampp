var isRtl;
var settleDataList;
var commissionList;
var balanceList;
var announceList;
var filterDates = [];

$(function () {
    isRtl = $('body').attr('dir') === 'rtl' || $('html').attr('dir') === 'rtl';

    loadSettleData();
    loadCommission();
    loadBalances();
    loadAnnounces();
});
