<?php

namespace App;

use Log;
use Mail;
use Session;
use App\User;
use App\Models\CryptoSettings;

class MailManager
{
    public static function sendMailWithInfo($trader, $mail) {
        $mailData = [
            'receiver'      => $trader->nickname,
            'title'         => $mail->title,
            'content'       => $mail->content,
        ];

        try {
            Mail::send('emails.mail', $mailData, function($message) use ($trader, $mail)
            {
                $message->to($trader->email)->subject(sprintf($mail->title, env('APP_NAME')));
            });

            return true;
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    public static function send_affiliate_settle_announce($user, $commissions, $balances) {
        $crypto_settings = CryptoSettings::getAll();
        $mailData = [
            'name'              => $user->nickname,
            'commissions'       => $commissions,
            'balances'          => $balances,
            'crypto_settings'   => $crypto_settings,
        ];

        try {
            Mail::send('emails.affiliate_settle_announce', $mailData, function($message) use ($user)
            {
                $message->to($user->email)->subject(sprintf(trans('mail.affiliate_settle.announce'), env('APP_NAME')));
            });

            return true;
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    public static function send_user_transfer_sender($user, $params, $result) {
        $mailData = [
            'name'      => $user->nickname,
            'sender'    => $params['sender'],
            'receiver'  => $params['receiver'],
            'currency'  => $params['currency'],
            'amount'    => $params['amount'],
            'result'    => $result,
        ];

        try {
            Mail::send('emails.transfer_sender', $mailData, function($message) use ($user)
            {
                $message->to($user->email)->subject(sprintf(trans('mail.transfer_sender.title'), env('APP_NAME')));
            });

            return true;
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    public static function send_user_transfer_receiver($user, $params, $result) {
        $mailData = [
            'name'      => $user->nickname,
            'sender'    => isset($params['sender']) ? $params['sender'] : trans('transfer.users.from_system'),
            'receiver'  => $params['receiver'],
            'currency'  => $params['currency'],
            'amount'    => $params['amount'],
            'result'    => $result,
        ];

        try {
            Mail::send('emails.transfer_receiver', $mailData, function($message) use ($user)
            {
                $message->to($user->email)->subject(sprintf(trans('mail.transfer_receiver.title'), env('APP_NAME')));
            });

            return true;
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    public static function send_verifymail($user) {
        $mailData = [
            'name'      => $user['nickname'],
            'url'       => route('activation.account') . '/' . $user['token'],
            'email'     => $user['email'],
            'userid'    => $user['userid'],
        ];

        try {
            Mail::send('emails.activate', $mailData, function($message) use ($user)
            {
                $message->to($user['email'])->subject(sprintf(trans('register.mail.title'), env('APP_NAME')));
            });

            return true;
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    public static function send_registermail($user) {
        $mailData = [
            'name'      => $user['nickname'],
            'url'       => route('register') . '?aid=' . $user['referrer'],
            'email'     => $user['email'],
        ];

        try {
            Mail::send('emails.register', $mailData, function($message) use ($user)
            {
                $message->to($user['email'])->subject(sprintf(trans('register.mail.title'), env('APP_NAME')));
            });

            return true;
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    public static function send_replymail($user) {
        $mailData = [
            'name'      => $user['name'],
            'email'     => $user['email'],
            'subject'   => $user['subject'],
            'msg'       => $user['message'],
            'reply'     => $user['reply'],
        ];

        try {
            Mail::send('emails.reply', $mailData, function($message) use ($user)
            {
                $message->to($user['email'])->subject(trans('message.mail.reply'));
            });

            return true;
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    //=======================================
    public static function send_forgotmail($user) {

        $mailData = [
            'name' => $user['name'],
            'url' => cUrl('password/reset') . '/' . $user['remember_token'],
            'email' => $user->email,
        ];

        try {
            Mail::send('emails.forgot', $mailData, function($message) use ($user) {
                $message->to($user['email'])->subject(trans('message.mail.forgot'));
            });

            return true;
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    public static function send_contactmail($user_id, $title, $content, $email, $support_mail) {
        $mailData = [
            'title' => $title,
            'content' => $content,
            'email' => $email,
        ];

        try {
            Mail::send('emails.contact', $mailData, function($message) use($support_mail) {
                $message->to($support_mail)->subject(trans('message.mail.contact'));
            });

            return true;
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }
}
