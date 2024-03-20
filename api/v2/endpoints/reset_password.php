<?php
if (!empty($_POST['new_password']) && !empty($_POST['email']) && !empty($_POST['code'])) {
	$code   = Wo_Secure($_POST['code']);
	$email   = Wo_Secure($_POST['email']);
	$update = true;

	if (Wo_isValidPasswordResetToken($_POST['code']) === false && Wo_isValidPasswordResetToken2($_POST['code']) === false) {
		$update = false;
		$error_code    = 9;
		$error_message = 'email , code wrong';
	}
	if ($update == true) {
		if (strlen($_POST['new_password']) >= 6) {
			$password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
			$getUser = $db->where('email', $email)->getOne(T_USERS);
			$db->where('email', $email)->update(T_USERS, array(
				'password' => $password,
				'email_code' => ''
			));
			$db->where('user_id', $getUser->user_id)->delete(T_APP_SESSIONS);

			cache($getUser->user_id, 'users', 'delete');
			$response_data['api_status'] = 200;
			$response_data['message'] = 'Your password was updated';
		} else {
			$error_code    = 10;
			$error_message = 'short password';
		}
	}
} else {
	$error_code    = 8;
	$error_message = 'new_password , email , code can not be empty';
}
