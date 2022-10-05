var isRtl;
var signedResult;

$(function () {
    isRtl = $('body').attr('dir') === 'rtl' || $('html').attr('dir') === 'rtl';

    $('#send_from').on('change', function() {
        setCurrBalance();
    });

    $('#amount').on('keyup', function() {
        setCurrBalance();
    });

    setCurrBalance();
});

$(document).ajaxStart(function(){
    showOverlay($('#form-wizard'), true, waitCaption);
});

$(document).ajaxStop(function(){
    showOverlay($('#form-wizard'), false);
});

function generateQrCodes(result) {
    $.ajax({
        url: BASE_URL + 'ajax/transfer/generateQrCodes',
        type: 'POST',
        data: {
            data: result,
        },
        success: function(result) {
            showQrCodes(result);
        },
        error: function(err) {
            console.log(err);
        }
    });
}

function showQrCodes(result) {
    // QR Code Generate
    let count = parseInt(result['count']);
    for (let i = 1; ; i ++) {
        if (document.getElementById('qr-div-' + i) == null) break;
        document.getElementById('qr-div-' + i).remove();
    }
    for (let i = 1; i <= count; i ++) {
        if (document.getElementById('qr-div-' + i) == null) {
            // Create div element
            let newDiv = document.createElement('div');
            newDiv.id = 'qr-div-' + i.toString();
            $(newDiv).attr('class', 'p-1');

            // Create img element
            let newImg = document.createElement('img');
            newImg.id = 'qr-code-' + i;
            newImg.src = 'data:image/png;base64, ' + result['qr_code' + i];
            newImg.width = 320;
            newImg.height = 320;

            document.getElementById('qr-divs').appendChild(newDiv);
            document.getElementById('qr-div-' + i.toString()).appendChild(newImg);
        }
        else {
            $('#qr-code-' + i).attr('src', 'data:image/png;base64, ' + result['qr_code' + i]);
        }
    }
}
