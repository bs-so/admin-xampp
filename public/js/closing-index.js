var isRtl;

$(function () {
    isRtl = $('body').attr('dir') === 'rtl' || $('html').attr('dir') === 'rtl';

    // Date
    $('#start_at_date').datepicker({
        orientation: isRtl ? 'auto right' : 'auto left',
        format: 'yyyy-mm-dd',
    });
    $('#finish_at_date').datepicker({
        orientation: isRtl ? 'auto right' : 'auto left',
        format: 'yyyy-mm-dd',
    });
});
