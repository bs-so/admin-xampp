<?php

namespace App\Models;

use DB;
use Log;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Litipk\BigNumbers\Decimal;

class TraderTransfer extends Model
{
    protected $table = 'olc_users_transfer';
    protected $table_staff = 'olc_staff';
    protected $table_users = 'olc_users';

    protected $table_users_balance = 'olc_users_balance';
    protected $table_system_balance = 'olc_system_balances';

    public function makeCsv($filename, $user_id, $type = 1) {
        $csv = '';
        $titles = ['no'];
        if ($type == 1) $titles[] = 'operator';
        $titles = array_merge($titles, ['sender', 'receiver', 'currency', 'amount', 'fee', 'remark', 'status', 'created_at']);
        foreach ($titles as $index => $title) {
            $csv .= trans('transfer.users.' . $title) . ',';
        }
        $csv = substr($csv, 0, strlen($csv) - 1) . "\n";

        $selector = DB::table($this->table)
            ->leftJoin($this->table_staff, $this->table_staff . '.id', '=', $this->table . '.staff_id')
            ->leftJoin($this->table_users . ' as us', 'us.id', '=', $this->table . '.sender_id')
            ->leftJoin($this->table_users . ' as ur', 'ur.id', '=', $this->table . '.receiver_id')
            ->select(
                $this->table . '.*',
                $this->table_staff . '.name as operator',
                'us.userid as sender',
                'ur.userid as receiver'
            );
        if ($type != 1) {
            $selector->where('staff_id', '=', 0);
            if ($type == 2) {
                $selector->where('sender_id', $user_id);
            } else if ($type == 3) {
                $selector->where('receiver_id', $user_id);
            }
        }

        $records = $selector->get();
        foreach ($records as $index => $record) {
            $csv .= ($index + 1) . ',';
            if ($type == 1) $csv .= $record->operator . ',';
            $csv .= $record->sender . ',';
            $csv .= $record->receiver . ',';
            $csv .= $record->currency . ',';
            $csv .= $record->amount . ',';
            $csv .= $record->fee . ',';
            $csv .= $record->remark . ',';
            $csv .= g_enum('StatusData')[$record->status][0] . ',';
            $csv .= $record->created_at . "\n";
        }

        file_put_contents($filename, "\xEF\xBB\xBF". $csv);
    }

    public function getUserTransferData($currencies) {
        $result = array();
        $today = date('Y-m-d');

        // Init
        foreach ($currencies as $index => $currency) {
            $result[$currency->currency] = array(
                'last_updated'  => date('Y-m-d H:i:s'),
                'today_count'   => 0,
                'today_amount'  => 0,
            );
        }

        // Today Result
        $records = self::where('staff_id', 0)
            ->where('created_at', 'like', date('Y-m-d') . '%')
            ->groupBy('currency')
            ->select(
                'currency',
                DB::raw('count(id) as total_count'),
                DB::raw('sum(amount) as total_amount')
            )
            ->get();
        foreach ($records as $index => $record) {
            $currency = $record->currency;
            $result[$currency]['today_count'] = $record->total_count;
            $result[$currency]['today_amount'] = $record->total_amount;
        }

        return $result;
    }

    public function getSystemTransferData($currencies) {
        $result = array();
        $today = date('Y-m-d');

        // Init
        foreach ($currencies as $index => $currency) {
            $result[$currency->currency] = array(
                'last_updated'  => date('Y-m-d H:i:s'),
                'today_count'   => 0,
                'today_amount'  => 0,
            );
        }

        // Today Result
        $records = self::where('staff_id', '>', 0)
            ->where('created_at', 'like', date('Y-m-d') . '%')
            ->groupBy('currency')
            ->select(
                'currency',
                DB::raw('count(id) as total_count'),
                DB::raw('sum(amount) as total_amount')
            )
            ->get();
        foreach ($records as $index => $record) {
            $currency = $record->currency;
            $result[$currency]['today_count'] = $record->total_count;
            $result[$currency]['today_amount'] = $record->total_amount;
        }

        return $result;
    }

    public function doSystemTransfer($params, &$_balance) {
        try {
            DB::beginTransaction();
            $amount = Decimal::create($params['amount']);

            // 1. Decrease system balance
            $record = DB::table($this->table_system_balance)
                ->where('type', SYSTEM_BALANCE_TYPE_CASINO_MANUAL)
                ->where('currency', $params['currency'])
                ->select('id', 'balance')
                ->lockForUpdate()
                ->first();
            $balance = Decimal::create($record->balance);
            $balance = $balance->sub($amount);
            $ret = DB::table($this->table_system_balance)
                ->where('id', $record->id)
                ->update([
                    'balance'   => $balance->__toString(),
                ]);

            // 2. Add user balance
            $record = DB::table($this->table_users_balance)
                ->where('user_id', $params['receiver_id'])
                ->where('currency', $params['currency'])
                ->select('id', 'balance')
                ->lockForUpdate()
                ->first();
            if (!isset($record) || !isset($record->id)) {
                $_balance = $params['amount'];
                $ret = DB::table($this->table_users_balance)
                    ->insert([
                        'user_id'       => $params['receiver_id'],
                        'currency'      => $params['currency'],
                        'balance'       => $params['amount'],
                    ]);
            }
            else {
                $balance = Decimal::create($record->balance);
                $balance = $balance->add($amount);
                $_balance = $balance->__toString();
                $ret = DB::table($this->table_users_balance)
                    ->where('id', $record->id)
                    ->update([
                        'balance'       => $balance->__toString(),
                    ]);
            }

            // 3. Insert history
            $ret = DB::table($this->table)
                ->insert([
                    'staff_id'      => Auth::user()->id,
                    'sender_id'     => $params['sender_id'],
                    'receiver_id'   => $params['receiver_id'],
                    'currency'      => $params['currency'],
                    'amount'        => $params['amount'],
                    'remark'        => isset($params['remark']) ? $params['remark'] : '',
                    'status'        => STATUS_ACTIVE,
                ]);

            DB::commit();
            return true;
        }
        catch (\Exception $ex) {
            DB::rollback();
            Log::error($ex->getMessage());
            return false;
        }
    }

    public function doUserTransfer($params, &$_sender_balance, &$_receiver_balance) {
        try {
            DB::beginTransaction();
            $amount = Decimal::create($params['amount']);

            // 1. Decrease sender balance
            $record = DB::table($this->table_users_balance)
                ->where('user_id', $params['sender_id'])
                ->where('currency', $params['currency'])
                ->select('id', 'balance')
                ->lockForUpdate()
                ->first();
            $balance = Decimal::create($record->balance);
            $balance = $balance->sub($amount);
            $_sender_balance = $balance->__toString();
            $ret = DB::table($this->table_users_balance)
                ->where('id', $record->id)
                ->update([
                    'balance'   => $balance->__toString(),
                ]);

            // 2. Add receiver balance
            $record = DB::table($this->table_users_balance)
                ->where('user_id', $params['receiver_id'])
                ->where('currency', $params['currency'])
                ->select('id', 'balance')
                ->lockForUpdate()
                ->first();
            if (!isset($record) || !isset($record->id)) {
                $_receiver_balance = $params['amount'];
                $ret = DB::table($this->table_users_balance)
                    ->insert([
                        'user_id'       => $params['receiver_id'],
                        'currency'      => $params['currency'],
                        'balance'       => $params['amount'],
                    ]);
            }
            else {
                $balance = Decimal::create($record->balance);
                $balance = $balance->add($amount);
                $_receiver_balance = $balance->__toString();
                $ret = DB::table($this->table_users_balance)
                    ->where('id', $record->id)
                    ->update([
                        'balance'       => $balance->__toString(),
                    ]);
            }

            // 3. Insert history
            $ret = DB::table($this->table)
                ->insert([
                    'staff_id'      => Auth::user()->id,
                    'sender_id'     => $params['sender_id'],
                    'receiver_id'   => $params['receiver_id'],
                    'currency'      => $params['currency'],
                    'amount'        => $params['amount'],
                    'remark'        => isset($params['remark']) ? $params['remark'] : '',
                    'status'        => STATUS_ACTIVE,
                ]);

            DB::commit();
            return true;
        }
        catch (\Exception $ex) {
            DB::rollback();
            Log::error($ex->getMessage());
            return false;
        }
    }

    public function getForDatatable($params) {
        $selector = DB::table($this->table)
            ->leftJoin($this->table_staff, $this->table_staff . '.id', '=', $this->table . '.staff_id')
            ->leftJoin($this->table_users . ' as us', 'us.id', '=', $this->table . '.sender_id')
            ->leftJoin($this->table_users . ' as ur', 'ur.id', '=', $this->table . '.receiver_id')
            ->select(
                $this->table . '.*',
                $this->table_staff . '.name as operator',
                'us.userid as sender',
                'ur.userid as receiver'
            );

        if (isset($params['is_staff']) && $params['is_staff'] == 1) {
            // Staff transfer only
            $selector->where('staff_id', '>', 0);
        }
        if (isset($params['is_user'])) {
            $selector->where('staff_id', '=', 0);
        }
        if (isset($params['sender'])) {
            $selector->where('sender_id', $params['sender']);
        }
        if (isset($params['receiver'])) {
            $selector->where('receiver_id', $params['receiver']);
        }


        $recordsTotal = $selector->count();

        // filtering
        if (isset($params['columns'][1]['search']['value'])
            && $params['columns'][1]['search']['value'] !== ''
        ) {
            $selector->where('us.userid', 'like', '%' . $params['columns'][1]['search']['value'] . '%');
        }
        if (isset($params['columns'][2]['search']['value'])
            && $params['columns'][2]['search']['value'] !== ''
        ) {
            $selector->where('ur.userid', 'like', '%' . $params['columns'][2]['search']['value'] . '%');
        }
        if (isset($params['columns'][3]['search']['value'])
            && $params['columns'][3]['search']['value'] !== ''
        ) {
            $selector->where('currency', $params['columns'][3]['search']['value']);
        }
        if (isset($params['columns'][6]['search']['value'])
            && $params['columns'][6]['search']['value'] !== ''
        ) {
           $selector->where($this->table . '.status', $params['columns'][6]['search']['value']);
        }
        if (isset($params['columns'][7]['search']['value'])
            && $params['columns'][7]['search']['value'] !== ''
        ) {
            $amountRange = preg_replace('/[\$\,]/', '', $params['columns'][7]['search']['value']);
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
