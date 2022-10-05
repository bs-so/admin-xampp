var v_UserDepositTable = null;
var v_UserWithdrawTable = null;
var v_UserTransferTable = null;
var v_SystemTransferTable = null;
var v_TransferFees = null;
var $primary = '#7367F0';
var $danger = '#EA5455';
var $warning = '#FF9F43';
var $info = '#0DCCE1';
var $primary_light = '#8F80F9';
var $warning_light = '#FFC085';
var $danger_light = '#f29292';
var $info_light = '#1edec5';
var $strok_color = '#b9c3cd';
var $label_color = '#e7eef7';
var $white = '#fff';
var registerChart = null;

$(function () {
    initVue();

    getData();
    loadChartData();
    setInterval(getData, 5000);
    setInterval(getServerInfo, 5000);

    getTransferFees();
    setInterval(getTransferFees, 5000);
});


function getData() {
    $.ajax({
        url: BASE_URL + 'ajax/home/getUserTransferData',
        type: 'post',
        success: function(data, status, xhr) {
            let currency = data['currency'];
            let userDeposit = data['user_deposit'];
            let userWithdraw = data['user_withdraw'];
            let userTransfer = data['user_transfer'];
            let systemTransfer = data['system_transfer'];

            v_UserDepositTable.lists = userDeposit;
            v_UserWithdrawTable.lists = userWithdraw;
            v_UserTransferTable.lists = userTransfer;
            v_SystemTransferTable.lists = systemTransfer;
        },
        error: function(error) {

        }
    });
}

function getTransferFees() {
    $.ajax({
        url: BASE_URL + 'ajax/home/getTransferFees',
        type: 'post',
        success: function(data, status, xhr) {
            v_TransferFees.lists = data;
        },
        error: function(error) {

        }
    });
}

function getServerInfo() {
    $.ajax({
        url: BASE_URL + 'ajax/home/getServerInfo',
        type: 'POST',
        success: function(result) {
            $('#total-ram').html(_number_format(result['total'], 0));
            $('#free-ram').html(_number_format(result['free'], 0));
            $('#free-percent').html(_number_format(result['percent'], 2) + '%');
        },
        error: function(err) {
            console.log(err);
        }
    });
}
