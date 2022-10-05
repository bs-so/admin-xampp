$(function () {
    $(document).ready(function () {
        $('.summernote').summernote({
            height: 180,
        });
    });

    isRtl = $('body').attr('dir') === 'rtl' || $('html').attr('dir') === 'rtl';

    initTable();

    setInterval(function () {
        listTable.ajax.reload(null, false);
    }, 5000);
});

function doSearch() {
    var title = $('#filter-title').val();
    var content = $('#filter-content').val();

    listTable.column(1).search(title, false, false);
    listTable.column(2).search(content, false, false).draw();
}
