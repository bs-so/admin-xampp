var isRtl;
var listTable;
var themeColors = [$info, $primary, $danger, $warning, $success];
var pieChartOptions
var pieChart;

$(function () {
    isRtl = $('body').attr('dir') === 'rtl' || $('html').attr('dir') === 'rtl';

    refreshTotal();
});
