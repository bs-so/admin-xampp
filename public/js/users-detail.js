var isRtl;
var depositTable;
var withdrawTable;
var sendTable;
var receiveTable;

$(function () {
    isRtl = $('body').attr('dir') === 'rtl' || $('html').attr('dir') === 'rtl';

    initTable();
});

function doSearch() {
    var login_id = $('#filter-login_id').val();
    var name = $('#filter-name').val();
    var role = $('#filter-role').val();
    var status = $('#filter-status').val();

    listTable.column(1).search(login_id, false, false);
    listTable.column(2).search(name, false, false);
    listTable.column(4).search(role, false, false);
    listTable.column(5).search(status, false, false);
    listTable.column(6).search(filterDates.join(':'), false, false).draw();
}
