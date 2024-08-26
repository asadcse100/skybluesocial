<?php
// +------------------------------------------------------------------------+
// | Softravine - The Ultimate Social Networking Platform
// | Copyright (c) 2024 Softravine. All rights reserved.
// +------------------------------------------------------------------------+
$response_data = array(
    'api_status' => 400,
);

$limit = (!empty($_POST['limit']) && is_numeric($_POST['limit']) && $_POST['limit'] > 0 && $_POST['limit'] <= 50 ? Wo_Secure($_POST['limit']) : 20);
$offset = (!empty($_POST['offset']) && is_numeric($_POST['offset']) && $_POST['offset'] > 0 ? Wo_Secure($_POST['offset']) : 0);

$pro = Wo_FeaturedUsersAPI($limit,$offset);
foreach ($pro as $key => $value) {
	foreach ($non_allowed as $key => $value) {
       unset($pro[$key][$value]);
    }
}

$response_data = array(
                        'api_status' => 200,
                        'data' => $pro
                    );