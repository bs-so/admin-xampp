var isRtl;
var listTable;
var filterDates = [];

$(function () {
    isRtl = $('body').attr('dir') === 'rtl' || $('html').attr('dir') === 'rtl';

    // Date
    initTable();

    $("#img-main").change(function (data) {
        var imageFile = data.target.files[0];
        var reader = new FileReader();
        reader.readAsDataURL(imageFile);

        reader.onload = function (evt) {
            $('#mainimagePreview').attr('src', evt.target.result);
            $('#mainimagePreview').hide();
            $('#mainimagePreview').fadeIn(650);
        }
    });

    $("#img-slide").change(function (data) {
        var imageFile = data.target.files[0];
        var reader = new FileReader();
        reader.readAsDataURL(imageFile);

        reader.onload = function (evt) {
            $('#slideimagePreview').attr('src', evt.target.result);
            $('#slideimagePreview').hide();
            $('#slideimagePreview').fadeIn(650);
        }
    });


});

function doSearch() {
    var title = $('#filter-title').val();
    listTable.column(1).search(title, false, false).draw();
}
