<script>
    let g_vmRatePanel = null;
    //let socket = io('{{ EX_RATE_SERVER }}', {secure: true, path: '/2.0.0'});

    /*$(function() {
        setInterval(function() {
            socket.emit('Request:CC:Rate:All');
        }, 3 * 1000);

        socket.on('Response:CC:Rate:All', function(data) {
            let tickRates = JSON.parse(data);
            showRatePanel(tickRates);
        });

        g_vmRatePanel = new Vue({
            el: '#rate-panel',
            data: {
                rates: [],
            },
        });
    });

    function showRatePanel(rates) {
        if (g_vmRatePanel == null) return;

        for (let currency in rates.DETAIL) {
            let data = rates.DETAIL[currency];
            let price = _number_format(data.price, 10);
            let length = currency.length;
            currency = currency.substring(0, length - 3) + '/' + currency.substring(length - 3, length);

            let found = false;
            for (let index = 0; index < g_vmRatePanel.rates.length; index ++) {
                if (g_vmRatePanel.rates[index].currency == currency) {
                    g_vmRatePanel.rates[index].price = price;
                    found = true;
                    break;
                }
            }
            if (!found) {
                g_vmRatePanel.rates.push({
                    currency: currency,
                    price: price,
                });
            }
        }
    }*/

    function getReducedStr(origin, prefix_len, suffix_len) {
        if (origin == undefined || origin == '') return origin;
        let result = origin;
        if (result.length <= prefix_len + 1 + suffix_len) {
            return origin;
        }

        result = result.substring(0, prefix_len) + '...' + result.substring(result.length - suffix_len, result.length);

        let copyStr = '<a class="btn btn-icon btn-icon-rounded-circle text-info btn-flat-info user-tooltip" href="javascript:copyStringToClipboard(' + "'" + origin + "'" + ');" title="' + '{{ trans('ui.button.copy') }}' + '">'
            + '<i class="fa fa-copy"></i></a>';
        result += copyStr;
        return result;
    }

    function getTxLink(currency, tx_id) {
        if (tx_id == undefined || tx_id == '') return tx_id;
        tx_id = (tx_id == null || tx_id == undefined) ? '' : tx_id;
        let txStr = tx_id;
        if (txStr.length >= 20) {
            txStr = txStr.substring(0, 20) + '...' + txStr.substring(txStr.length - 5, txStr.length);
        }

        let url = '';
        if (currency == 'BTC') {
            url = '{{ BTC_CONFIRM_URL }}';
        }
        else if (currency == 'ETH' || currency == 'USDT') {
            url = '{{ ETH_CONFIRM_URL }}';
        }
        else if (currency == 'BCH') {
            url = '{{ BCH_CONFIRM_URL }}';
        }

        return '<a target="_blank" class="text-primary" style="text-decoration: underline;" href="' + url + tx_id + '">' + txStr + '</a>';
    }
</script>
