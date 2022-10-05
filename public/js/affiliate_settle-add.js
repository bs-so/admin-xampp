var isRtl;
var csvList;
var csvData;
var formatError = "";
var csvStatus = true;
var salesCheckSelected;
var salesCheckNext;
var salesCheckCancelled;
var commissionList;
var balanceList;
var filterDates = [];
var g_vmSummaryList;

$(function () {
    isRtl = $('body').attr('dir') === 'rtl' || $('html').attr('dir') === 'rtl';

    initVue();

    $('#end_date').pickadate({
        format: 'yyyy-mm-dd'
    });

    setEndDate();

    $(".select2").select2({
        // the following code is used to disable x-scrollbar when click in select input and
        // take 100% width in responsive also
        dropdownAutoWidth: true,
        width: '100%'
    });

    $('#csv_file').on('change', function() {
        readCSV(this);
    });

    forwardSteps();
});

$(document).ajaxStart(function(){
    showOverlay($('#form-wizard'), true, waitCaption);
});

$(document).ajaxStop(function(){
    showOverlay($('#form-wizard'), false);
});

function updateSettleStatus(status) {
    $.ajax({
        url: BASE_URL + 'ajax/affiliate/settle/updateStatus',
        type: 'POST',
        data: {
            status: status + 1,
        },
        success: function() {
        },
        error: function(err) {
            console.log(err);
        }
    });
}

function saveSettleData() {
    let ret = false;

    $.ajax({
        url: BASE_URL + 'ajax/affiliate/settle/saveCsvSettleData',
        type: 'POST',
        success: function() {
            ret = calcCommission(1);
        },
        error: function(err) {
            console.log(err);
        }
    });

    return ret;
}

function calcCommission(step) {
    let ret = false;

    $.ajax({
        url: BASE_URL + 'ajax/affiliate/settle/calcCommission',
        type: 'POST',
        data: {
            step: step,
        },
        success: function(result) {
            if (result['finished'] != 0) {
                // Not finished
                return calcCommission(step + 1);
            }

            showOverlay($('#form-wizard'), false);
            showToast(result['msg'], result['title'], result['type']);
            g_vmSummaryList.list = result['summary'];
            if (result['type'] == 'success') {
                ret = saveCommission();
            }
        },
        error: function(err) {
            console.log(err);
        },
    });
    return ret;
}

function saveCommission() {
    let ret = false;
    $.ajax({
        url: BASE_URL + 'ajax/affiliate/settle/saveCommission',
        type: 'POST',
        success: function() {
            ret = loadCommission();
        },
        error: function(err) {
            console.log(err);
        }
    });
    return ret;
}

function saveBalances() {
    let ret = false;
    $.ajax({
        url: BASE_URL + 'ajax/affiliate/settle/saveBalances',
        type: 'POST',
        success: function() {
            ret = loadBalances();
        },
        error: function(err) {
            console.log(err);
        }
    });
    return ret;
}

var handlePrevTree = function() {
    $('#prev-tree-list').jstree({
        "core": {
            "themes": {
                "responsive": false
            }
        },
        "types": {
            "default": {
                "icon": "fa fa-handshake-o text-success fa-lg"
            },
            "file": {
                "icon": "fa fa-handshake-o text-inverse fa-lg"
            }
        },
        "plugins": ["types"]
    });
};

var handleNextTree = function() {
    $('#next-tree-list').jstree({
        "core": {
            "themes": {
                "responsive": false
            }
        },
        "types": {
            "default": {
                "icon": "fa fa-handshake-o text-success fa-lg"
            },
            "file": {
                "icon": "fa fa-handshake-o text-inverse fa-lg"
            }
        },
        "plugins": ["types"]
    });
};
