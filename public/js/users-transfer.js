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
    var sender = $('#filter-sender').val();
    var receiver = $('#filter-receiver').val();
    var currency = $('#filter-currency').val();
    var status = $('#filter-status').val();

    listTable.column(1).search(sender, false, false);
    listTable.column(2).search(receiver, false, false);
    listTable.column(3).search(currency, false, false);
    listTable.column(6).search(status, false, false);
    listTable.column(7).search(filterDates.join(':'), false, false).draw();
}