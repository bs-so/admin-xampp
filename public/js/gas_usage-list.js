var isRtl;
var listTable;
var filterDates = [];

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
});

function doSearch() {
    var currency = $('#filter-currency').val();
    var tx_id = $('#filter-tx_id').val();
    var to_address = $('#filter-to_address').val();
    var remark = $('#filter-remark').val();

    listTable.column(1).search(currency, false, false);
    listTable.column(2).search(tx_id, false, false);
    listTable.column(3).search(to_address, false, false);
    listTable.column(6).search(remark, false, false);
    listTable.column(7).search(filterDates.join(':'), false, false).draw();
}
