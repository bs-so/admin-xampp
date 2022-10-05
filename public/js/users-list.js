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
        function (start, end, label) {
            var startDate = moment(start).format('YYYY-MM-DD');
            var endDate = moment(end).format('YYYY-MM-DD');
            filterDates = [startDate, endDate];
        }
    );
    $('#filter-date').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' ~ ' + picker.endDate.format('YYYY-MM-DD'));
    });
    $('#filter-date').on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
        filterDates = [];
    });

    initTable();
});

function doSearch() {
    var userid = $('#filter-userid').val();
    var nickname = $('#filter-nickname').val();
    var email = $('#filter-email').val();
    var auth_status = $('#filter-auth-status').val();
    var status = $('#filter-status').val();

    listTable.column(1).search(userid, false, false);
    listTable.column(2).search(nickname, false, false);
    listTable.column(3).search(email, false, false);
    listTable.column(4).search(auth_status, false, false);
    listTable.column(5).search(status, false, false);
    listTable.column(6).search(filterDates.join(':'), false, false).draw();
}
