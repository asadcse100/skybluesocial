<?php
// +------------------------------------------------------------------------+
// | Softravine - The Ultimate Social Networking Platform
// | Copyright (c) 2024 Softravine. All rights reserved.
// +------------------------------------------------------------------------+
$response_data = array(
    'api_status' => 400,
);

if (!empty($_POST['type']) && $_POST['type'] == 'verify') {

    if (empty($_POST['code'])) {
        $error_code = 7;
        $error_message = 'code can not be empty';
    } elseif (empty($_POST['factor_method']) || !in_array($_POST['factor_method'], array('two_factor', 'google', 'authy'))) {
        $error_code = 8;
        $error_message = 'Select factor method';
    } else {

        if ($_POST['factor_method'] == 'google') {
            require_once 'assets/libraries/google_auth/vendor/autoload.php';
            try {
                $google2fa = new \PragmaRX\Google2FA\Google2FA();
                if ($google2fa->verifyKey($wo['user']['google_secret'], $_POST['code'])) {
                    $db->where('user_id', $wo['user']['user_id'])->update(T_USERS, ['two_factor' => 1,
                        'two_factor_verified' => 1,
                        'two_factor_method' => 'google']);

                    $response_data['api_status'] = 200;
                    $response_data['message'] = $wo['lang']['setting_updated'];
                } else {
                    $response_data['message'] = $wo['lang']['wrong_confirm_code'];
                }
            } catch (Exception $e) {
                $response_data['message'] = $e->getMessage();
            }

        } elseif ($_POST['factor_method'] == 'authy') {
            if (verifyAuthy($_POST['code'], $wo['user']['authy_id'])) {
                $db->where('user_id', $wo['user']['user_id'])->update(T_USERS, ['two_factor' => 1,
                    'two_factor_verified' => 1,
                    'two_factor_method' => 'authy']);
                $response_data['api_status'] = 200;
                $response_data['message'] = $wo['lang']['setting_updated'];
            } else {
                $response_data['api_status'] = 400;
                $response_data['message'] = $wo['lang']['wrong_confirm_code'];
            }
        } else {
            if ($wo['user']['email_code'] == md5($_POST['code'])) {

                if (!empty($wo['user']['new_email']) && filter_var($wo['user']['new_email'], FILTER_VALIDATE_EMAIL)) {
                    $Update_data['email'] = $wo['user']['new_email'];
                    $Update_data['new_email'] = '';
                }
                if (!empty($wo['user']['new_phone'])) {
                    $Update_data['phone_number'] = $wo['user']['new_phone'];
                    $Update_data['new_phone'] = '';
                }
                $Update_data['two_factor_verified'] = 1;
                $Update_data['two_factor'] = 1;
                $Update_data['two_factor_method'] = 'two_factor';
                Wo_UpdateUserData($wo['user']['user_id'], $Update_data);

                $response_data['api_status'] = 200;
                $response_data['message'] = 'two factor on';
            } else {
                $response_data['api_status'] = 400;
                $response_data['message'] = $wo['lang']['wrong_confirm_code'];
            }
        }
    }
} else {
    if ($wo['user']['two_factor']) {
        $Update_data = array(
            'two_factor' => 0,
            'two_factor_verified' => 0,
        );
        Wo_UpdateUserData($wo['user']['user_id'], $Update_data);
        $response_data['api_status'] = 200;
        $response_data['message'] = 'two factor off';
    } else {
        if ($wo['config']['two_factor_type'] == 'phone') {
            if (!empty($_POST['phone_number'])) {
                preg_match_all('/\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|
	                        2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|
	                        4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$/', $_POST['phone_number'], $matches);
                if (!empty($matches[1][0]) && !empty($matches[0][0])) {
                    $code = rand(111111, 999999);
                    $hash_code = md5($code);
                    $message = "Your confirmation code is: $code";
                    $send = Wo_SendSMSMessage($_POST['phone_number'], $message);
                    if ($send) {
                        $Update_data = array(
                            'phone_number' => Wo_Secure($_POST['phone_number']),
                            'email_code' => $hash_code,
                            'two_factor' => 0,
                            'two_factor_verified' => 0,
                        );
                        Wo_UpdateUserData($wo['user']['user_id'], $Update_data);
                        $response_data['api_status'] = 200;
                        $response_data['message'] = 'confirmation code sent';
                    } else {
                        $error_code = 6;
                        $error_message = 'can not send sms';
                    }
                } else {
                    $error_code = 5;
                    $error_message = 'phone_number is wrong';
                }
            } else {
                $error_code = 4;
                $error_message = 'phone_number can not be empty';
            }
        } else {
            $code = rand(111111, 999999);
            $hash_code = md5($code);
            $message = "Your confirmation code is: $code";
            $send_message_data = array(
                'from_email' => $wo['config']['siteEmail'],
                'from_name' => $wo['config']['siteName'],
                'to_email' => $wo['user']['email'],
                'to_name' => $wo['user']['name'],
                'subject' => 'Please verify that it’s you',
                'charSet' => 'utf-8',
                'message_body' => $message,
                'is_html' => true,
            );
            $send = Wo_SendMessage($send_message_data);
            if ($send) {
                $Update_data = array(
                    'email_code' => $hash_code,
                    'two_factor' => 0,
                    'two_factor_verified' => 0,
                );
                Wo_UpdateUserData($wo['user']['user_id'], $Update_data);
                $response_data['api_status'] = 200;
                $response_data['message'] = 'confirmation code sent';
            } else {
                $error_code = 4;
                $error_message = 'phone_number can not be empty';
            }
        }
    }
}
