<?php

namespace App\Http\Controllers;

use Auth;
use Session;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Litipk\BigNumbers\Decimal;
use App\Modules\CryptoCurrencyAPI;
use App\Models\Master;
use App\Models\Transactions;
use App\Models\ColdWallets;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    protected $marker = '/public/app-assets/images/qrmarker.png';

    public function index(Request $request) {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN) {
            return redirect()->back();
        }

        $sel_currency = $request->get('currency', 'BTC');

        $tbl = new ColdWallets();
        $wallets = $tbl->getAll($sel_currency);

        return view('transfer.index', [
            'sel_currency'  => $sel_currency,
            'wallets'       => $wallets,
        ]);
    }

    public function ajax_makeTransaction(Request $request) {
        $data = $request->all();

        $tbl = new ColdWallets();
        $nonce = $tbl->getNonce($data['from_wallet']);
        $nonce_from_api = CryptoCurrencyAPI::GetNonce($data['from_address']);

        if ($nonce_from_api > $nonce) {
            $nonce = $nonce_from_api;
        }

        $currency = $data['currency'];
        $data['nonce'] = $nonce;
        $data['status'] = TRANSFER_STATUS_REQUESTED;

        $cryptoSettings = Session::get('crypto_settings');
        $amount = Decimal::create($data['amount']);
        $unit = $cryptoSettings[$currency]['unit'];
        $mul = Decimal::create(10);
        $mul = $mul->pow(Decimal::create($unit));
        $amount = $amount->mul($mul)->__toString();
        $fee = Decimal::create($data['fee'])->mul($mul)->__toString();

        // Get gas price & gas limit
        $gas_price = '';
        $gas_limit = '';
        if ($currency == 'ETH' || $currency == 'USDT') {
            $ret = ColdWallets::getGasValues($currency, $gas_price, $gas_limit);
        }

        $gasPriceMode = Master::getValue('GAS_PRICE_MODE');
        $unit = 0;
        if ($gasPriceMode != GASPRICE_MANUAL) {
            if ($currency == 'BTC') {
                $unit = g_getBTCFees()[$gasPriceMode];
            }
        }
        $transaction = CryptoCurrencyAPI::MakeTransaction($currency, COIN_NET, $data['from_address'], $data['to_address'], $amount, $nonce, $fee, $gas_price, $gas_limit, $unit);
        if ($transaction === false) {
            $data['status'] = TRANSFER_STATUS_FAILED;
        }
        else {
            $data['transaction'] = $transaction;
            $data['gas_price'] = $gas_price;
            $data['gas_limit'] = $gas_limit;
            Session::put('transaction_data', $data);
        }

        return response()->json($data);
    }

    public function ajax_generateQrCodes(Request $request) {
        $params = $request->all();

        // Split QR Code
        $data = $params['data'];
        $len = strlen($data);
        $size = Master::getValue('QR_CODE_SPLIT_SIZE');

        $index = 0;
        $result = array();
        for ($i = 0; $i < $len; $i += $size) {
            $minsize = min($len - $i, $size);
            $index ++;
            $result['qr_code' . $index] = base64_encode(QrCode::format('png')->size(400)->merge($this->marker, .1)->encoding('UTF-8')->errorCorrection('M')->generate(substr($data, $i, $minsize)));
        }
        $result['count'] = $index;

        return response()->json($result);
    }

    public function ajax_doFinish(Request $request) {
        $data = Session::get('transaction_data');
        if (!isset($data)) {
            return response()->json(0);
        }

        $data['signed'] = $request->get('signed_tx');
        $data['remark'] = $request->get('remark');

        $tbl = new Transactions();
        $ret = $tbl->insertRecord($data);

        $data['nonce'] = intval($data['nonce']) + 1;
        $tbl = new ColdWallets();
        $ret = $tbl->updateNonce($data);

        return response()->json($ret);
    }
}
