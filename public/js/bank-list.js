var isRtl;
var _bankId = 0;
var listTable;

$(function () {
    isRtl = $('body').attr('dir') === 'rtl' || $('html').attr('dir') === 'rtl';

    initTable();

    summernote = $('.summernote').summernote({
        height: 180,
        lang: "ja-JP",
        fontNames: ["MS Mincho", "Yu Gothic", "Hiragino Kaku Gothic Pro", "Meiryo", "sans-serif", "Arial", "Arial Black", "Comic Sans MS", "Courier New", "Helvetica Neue", "Helvetica", "Impact", "Lucida Grande", "Tahoma", "Times New Roman", "Verdana"]
    });
});
