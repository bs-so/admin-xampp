var isRtl;
var gamesList;
var categoryList;
var filterDates = [];

$(function () {
    isRtl = $('body').attr('dir') === 'rtl' || $('html').attr('dir') === 'rtl';

    // Date
    initTable();

    $(".select2").select2({
        // the following code is used to disable x-scrollbar when click in select input and
        // take 100% width in responsive also
        dropdownAutoWidth: true,
        width: '100%'
    });
});

function doSearch() {
    var title = $('#filter-title').val();
    listTable.column(1).search(title, false, false).draw();
}


function readImg(input, img) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
            img.attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]); // convert to base64 string
    }
}

$('#main_img').on('change', function() {
    readImg(this, $('#main_img-img'));
});
$('#mobile_img_jp').on('change', function() {
    readImg(this, $('#mobile_img_jp-img'));
});
$('#mobile_img_en').on('change', function() {
    readImg(this, $('#mobile_img_en-img'));
});
$('#desc_img1_jp').on('change', function() {
    readImg(this, $('#desc_img1_jp-img'));
});
$('#desc_img2_jp').on('change', function() {
    readImg(this, $('#desc_img2_jp-img'));
});
$('#desc_img1_en').on('change', function() {
    readImg(this, $('#desc_img1_en-img'));
});
$('#desc_img2_en').on('change', function() {
    readImg(this, $('#desc_img2_en-img'));
});
$('#video_img').on('change', function() {
    readImg(this, $('#video_img-img'));
});
