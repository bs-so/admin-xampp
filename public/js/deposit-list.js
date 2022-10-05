var isRtl;
var listTable;
var filterDates = [];

$(function () {
    isRtl = $('body').attr('dir') === 'rtl' || $('html').attr('dir') === 'rtl';

    initTable();
});

function showQrCode(currency) {
    $.ajax({
        url: BASE_URL + 'ajax/deposit/qrcode',
        type: 'POST',
        data: {
            currency: currency,
        },
        success: function(result) {
            $('#qr-code').attr('src', 'data:image/png;base64, ' + result);
            $('#modal-qrcode').modal('show');
        },
        error: function(err) {
            console.log(err);
        }
    });
}