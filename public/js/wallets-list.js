var isRtl;
var listTable;
var filterDates = [];
var selectedId = 0;
var g_vmDeposit = null;
var g_vmWithdraw = null;
var g_vmGasTank = null;

$(function () {
    isRtl = $('body').attr('dir') === 'rtl' || $('html').attr('dir') === 'rtl';

    // Date
    $('#filter-date').daterangepicker({
            opens: isRtl ? 'right' : 'left',
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD'
            }
        },
        function(start, end, label) {
            var startDate = moment(start).format('YYYY-MM-DD');
            var endDate = moment(end).format('YYYY-MM-DD');
            filterDates = [startDate, endDate];
        }
    );
    $('#filter-date').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' ~ ' + picker.endDate.format('YYYY-MM-DD'));
    });
    $('#filter-date').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        filterDates = [];
    });

    initTable();
    initVue();
});

function doSearch() {
    var type = $('#filter-type').val();
    var address = $('#filter-address').val();
    var status = $('#filter-status').val();

    listTable.column(2).search(type, false, false);
    listTable.column(4).search(address, false, false);
    listTable.column(7).search(status, false, false).draw();
}

$('#add-address').on('keyup', function() {
    let json_data = $('#add-address').val();
    let records = [];

    try {
        records = JSON.parse(json_data);
        $('#add-currency').val(records['currency']);
        $('#add-address').val(records['address']);
    }
    catch (err) {
        return null;
    }
});

function showAddModal() {
    $('#add-address').val('');
    $('#add-remark').val('');
    $('#add-result').html('');

    $('#modal-add-wallet').modal('show');
}

$('#btn-add-submit').on('click', function() {
    $.ajax({
        url: BASE_URL + 'ajax/wallets/addWallet',
        type: 'POST',
        data: {
            currency: $('#add-currency').val(),
            address: $('#add-address').val(),
            type: $('#add-type').val(),
            remark: $('#add-remark').val(),
        },
        success: function(result) {
            $('#modal-add-wallet').modal('hide');
            listTable.ajax.reload();
        },
        error: function(err) {
            console.log(err);
        }
    });
});

function setPrivateKey(id) {
    $('#private-key').val('');
    selectedId = id;

    $('#modal-set-private').modal('show');
}
