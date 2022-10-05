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
    var type = $('#filter-type').val();
    var direction = $('#filter-direction').val();
    var user = $('#filter-user').val();
    var currency = $('#filter-currency').val();
    var status = $('#filter-status').val();

    listTable.column(2).search(type, false, false);
    listTable.column(3).search(direction, false, false);
    listTable.column(4).search(user, false, false);
    listTable.column(5).search(currency, false, false);
    listTable.column(8).search(status, false, false);
    listTable.column(9).search(filterDates.join(':'), false, false).draw();
}
