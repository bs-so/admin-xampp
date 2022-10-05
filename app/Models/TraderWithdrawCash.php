<?php

namespace App\Models;

use DB;
use Litipk\BigNumbers\Decimal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TraderWithdrawCash extends Model
{
    use Notifiable;
    protected $table = 'olc_users_withdraw_cash';
    protected $table_user = 'olc_users';
    protected $table_banks = 'olc_banks';

    public function makeCsv($filename, $trader_id = 0) {
        $csv = '';
        $titles = ['no', 'user_name', 'bank_name', 'branch_name', 'type', 'account_number', 'account_name', 'amount', 'withdraw_fee', 'transfer_fee', 'status', 'remark', 'reged_at'];
        foreach ($titles as $index => $title) {
            $csv .= trans('users-history.withdraw_cash.' . $title) . ',';
        }
        $csv = substr($csv, 0, strlen($csv) - 1) . "\n";

        $selector = DB::table($this->table)
            ->leftJoin($this->table_user, $this->table_user . '.id', '=', $this->table . '.user_id')
            ->where($this->table . '.status', '!=', STATUS_REQUESTED)
            ->select(
                $this->table . '.*',
                $this->table_user . '.nickname as user_name'
            );
        if ($trader_id > 0) {
            $selector->where('user_id', $trader_id);
        }
        $records = $selector->get();
        foreach ($records as $index => $record) {
            $csv .= ($index + 1) . ',';
            $csv .= $record->user_name . ',';
            $csv .= $record->bank_name . ',';
            $csv .= $record->branch_name . ',';
            $csv .= g_enum('BankTypeData')[$record->type][0] . ',';
            $csv .= $record->account_number . ',';
            $csv .= $record->account_name . ',';
            $csv .= $record->amount . ',';
            $csv .= $record->withdraw_fee . ',';
            $csv .= $record->transfer_fee . ',';
            $csv .= g_enum('UsersWithdrawCashStatus')[$record->status][0] . ',';
            $csv .= $record->remark . ',';
            $csv .= $record->created_at . "\n";
        }

        file_put_contents($filename, "\xEF\xBB\xBF". $csv);
    }

    public function approveRequests($params) {
        $records = self::whereIn('id', $params['selected'])
            ->select('*')
            ->get();

        $ret = true;
        $tbl = new Profits();
        $fee = Decimal::create(Master::getValue(WITHDRAW_FEE));
        $transfer_fee = (!isset($params['transfer_fee']) || $params['transfer_fee'] == '') ? 0 : $params['transfer_fee'];
        foreach ($records as $index => $record) {
            $withdraw_fee = Decimal::create($record->amount)->mul($fee)->div(Decimal::create(100));
            $ret = $tbl->insertRecord([
                'currency'      => MAIN_CURRENCY,
                'profit'        => $withdraw_fee->__toString(),
                'user_id'       => $record->user_id,
                'type'          => PROFIT_TYPE_WITHDRAW,
            ]);
            $ret = self::where('id', $record->id)
                ->update([
                    'withdraw_fee'  => $withdraw_fee->__toString(),
                    'transfer_fee'  => $transfer_fee,
                    'status'        => STATUS_ACCEPTED,
                ]);
        }

        return $ret;
    }

    public function cancelRequests($params) {
        $records = self::whereIn('id', $params['selected'])
            ->select('*')
            ->get();

        $ret = true;
        foreach ($records as $index => $record) {
            $amount = $record->amount;
            $ret = TraderBalance::increaseBalance($record->user_id, MAIN_CURRENCY, $amount);
            $ret = self::where('id', $record->id)
                ->update([
                    'status'    => STATUS_CANCELLED,
                    'remark'    => $params['remark'],
                ]);
        }

        return $ret;
    }

    public function getRequestList($params) {
        $selector = DB::table($this->table)
            ->leftJoin($this->table_user, $this->table_user . '.id', '=', $this->table . '.user_id')
            ->whereIn($this->table . '.status', [STATUS_REQUESTED, STATUS_PENDING])
            ->select(
                $this->table . '.*',
                $this->table_user . '.userid as userid',
                $this->table_user . '.nickname as user_name'
            );

        $recordsTotal = $selector->count();

        // filtering
        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $selector->where($this->table_user . '.userid', 'like', '%' . $params['columns'][2]['search']['value'] . '%');
        }
        if (isset($params['columns'][3]['search']['value'])
            && $params['columns'][3]['search']['value'] !== ''
        ) {
            $selector->where($this->table_user . '.nickname', 'like', '%' . $params['columns'][3]['search']['value'] . '%');
        }
        if (isset($params['columns'][4]['search']['value'])
            && $params['columns'][4]['search']['value'] !== ''
        ) {
            $selector->where('bank_name', 'like', '%' . $params['columns'][4]['search']['value'] . '%');
        }
        if (isset($params['columns'][6]['search']['value'])
            && $params['columns'][6]['search']['value'] !== ''
        ) {
            $selector->where('type', $params['columns'][6]['search']['value']);
        }
        if (isset($params['columns'][7]['search']['value'])
            && $params['columns'][7]['search']['value'] !== ''
        ) {
            $selector->where('account_number', 'like', '%' . $params['columns'][7]['search']['value'] . '%');
        }
        if (isset($params['columns'][10]['search']['value'])
            && $params['columns'][10]['search']['value'] !== ''
        ) {
            $selector->where($this->table . '.status', $params['columns'][10]['search']['value']);
        }
        if (isset($params['columns'][11]['search']['value'])
            && $params['columns'][11]['search']['value'] !== ''
        ) {
            $amountRange = preg_replace('/[\$\,]/', '', $params['columns'][11]['search']['value']);
            $elements = explode(':', $amountRange);

            if ($elements[0] != "" || $elements[1] != "") {
                $elements[0] .= ' 00:00:00';
                $elements[1] .= ' 23:59:59';
                $selector->whereBetween($this->table . '.created_at', $elements);
            }
        }

        // number of filtered records
        $recordsFiltered = $selector->count();

        // sort
        foreach ($params['order'] as $order) {
            $field = $params['columns'][$order['column']]['data'];
            $selector->orderBy($field, $order['dir']);
        }

        // offset & limit
        if (!empty($params['start']) && $params['start'] > 0) {
            $selector->skip($params['start']);
        }

        if (!empty($params['length']) && $params['length'] > 0) {
            $selector->take($params['length']);
        }

        // get records
        $records = $selector->get();

        return [
            'draw' => $params['draw']+0,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->leftJoin($this->table_user, $this->table_user . '.id', '=', $this->table . '.user_id')
            ->where($this->table . '.status', '!=', STATUS_REQUESTED)
            ->select(
                $this->table . '.*',
                $this->table_user . '.userid as userid',
                $this->table_user . '.nickname as user_name'
            );

        if (isset($params['user_id'])) {
            $selector->where('user_id', $params['user_id']);
        }
        $recordsTotal = $selector->count();

        // filtering
        if (isset($params['columns'][1]['search']['value'])
            && $params['columns'][1]['search']['value'] !== ''
        ) {
            $selector->where($this->table_user . '.userid', 'like', '%' . $params['columns'][1]['search']['value'] . '%');
        }
        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $selector->where($this->table_user . '.nickname', 'like', '%' . $params['columns'][2]['search']['value'] . '%');
        }
        if (isset($params['columns'][3]['search']['value'])
            && $params['columns'][3]['search']['value'] !== ''
        ) {
            $selector->where('bank_name', 'like', '%' . $params['columns'][3]['search']['value'] . '%');
        }
        if (isset($params['columns'][5]['search']['value'])
            && $params['columns'][5]['search']['value'] !== ''
        ) {
            $selector->where('type', $params['columns'][5]['search']['value']);
        }
        if (isset($params['columns'][6]['search']['value'])
            && $params['columns'][6]['search']['value'] !== ''
        ) {
            $selector->where('account_number', 'like', '%' . $params['columns'][6]['search']['value'] . '%');
        }
        if (isset($params['columns'][11]['search']['value'])
            && $params['columns'][11]['search']['value'] !== ''
        ) {
           $selector->where($this->table . '.status', $params['columns'][11]['search']['value']);
        }
        if (isset($params['columns'][12]['search']['value'])
            && $params['columns'][12]['search']['value'] !== ''
        ) {
            $amountRange = preg_replace('/[\$\,]/', '', $params['columns'][12]['search']['value']);
            $elements = explode(':', $amountRange);

            if ($elements[0] != "" || $elements[1] != "") {
                $elements[0] .= ' 00:00:00';
                $elements[1] .= ' 23:59:59';
                $selector->whereBetween($this->table . '.created_at', $elements);
            }
        }

        // number of filtered records
        $recordsFiltered = $selector->count();

        // sort
        foreach ($params['order'] as $order) {
            $field = $params['columns'][$order['column']]['data'];
            $selector->orderBy($field, $order['dir']);
        }

        // offset & limit
        if (!empty($params['start']) && $params['start'] > 0) {
            $selector->skip($params['start']);
        }

        if (!empty($params['length']) && $params['length'] > 0) {
            $selector->take($params['length']);
        }

        // get records
        $records = $selector->get();

        return [
            'draw' => $params['draw']+0,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'error' => 0,
        ];
    }
}
