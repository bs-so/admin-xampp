var isRtl;
var listTable;
var filterDates = [];

$(function () {
    isRtl = $('body').attr('dir') === 'rtl' || $('html').attr('dir') === 'rtl';

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
});

function doSearch() {
    var userid = $('#filter-userid').val();
    var nickname = $('#filter-nickname').val();
    var bank_name = $('#filter-bank_name').val();
    var type = $('#filter-type').val();
    var account_number = $('#filter-account_number').val();
    var status = $('#filter-status').val();

    listTable.column(2).search(userid, false, false);
    listTable.column(3).search(nickname, false, false);
	listTable.column(4).search(bank_name, false, false);
    listTable.column(6).search(type, false, false);
    listTable.column(7).search(account_number, false, false);
    listTable.column(10).search(status, false, false);
    listTable.column(11).search(filterDates.join(':'), false, false).draw();
}

$('#checkPageItemAll').on('click', function() {
    if ($(this).hasClass('allChecked')) {
        $('input[type="checkbox"]').prop('checked', false);
		$('#btn-withdraw').prop('disabled', true);
		$('#btn-cancel').prop('disabled', true);

    } else {
        $('input[type="checkbox"]').prop('checked', true);
		$('#btn-withdraw').prop('disabled', false);
		$('#btn-cancel').prop('disabled', false);
    }
    $(this).toggleClass('allChecked');

    updateSelectedBalance();
})

$('#request-list tbody').on('change', 'input[type="checkbox"]', function(){
    var data = listTable.$('input[type="checkbox"]').serialize();
    var selCount = data.split('checkItem').length - 1;
    $('#btn-withdraw').prop('disabled', !selCount);
    $('#btn-cancel').prop('disabled', !selCount);

    // If checkbox is not checked
    if(!this.checked){
        var el = $('#withdraw-list-select-all').get(0);

        if(el && el.checked && ('indeterminate' in el)){
            el.indeterminate = true;
        }
    }
    updateSelectedBalance();
});

function updateSelectedBalance() {
    var checkboxes = document.getElementsByName('checkItem');
    var sum = new BigNumber(0);
    var count = 0;

    for (var i=0; i<checkboxes.length; i++) {
        if (checkboxes[i].checked) {
            var id = checkboxes[i].value;
            var value = $('#amount_' + id).html();
            value = value.replace(',', '');
            var amount = new BigNumber(value);
            sum = sum.plus(amount);
            count ++;
        }
    }

    if (count > 0) {
        $('#btnWithdraw').attr('disabled', false);
        $('#btnCancel').attr('disabled', false);
    }
    else {
        $('#btnWithdraw').attr('disabled', true);
        $('#btnCancel').attr('disabled', true);
    }

    $('#selected-balance').html(_number_format(sum.toNumber(), mainCurrencyDecimals));
}
