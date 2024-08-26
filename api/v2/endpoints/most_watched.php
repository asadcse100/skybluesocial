<?php
// +------------------------------------------------------------------------+
// | Softravine - The Ultimate Social Networking Platform
// | Copyright (c) 2024 Softravine. All rights reserved.
// +------------------------------------------------------------------------+
$response_data = array(
    'api_status' => 400,
);

$limit = (!empty($_POST['limit']) && is_numeric($_POST['limit']) && $_POST['limit'] > 0 && $_POST['limit'] <= 50 ? Wo_Secure($_POST['limit']) : 20);

$most_matched = Wo_GetMtwFilms($limit);
$response_data = array(
    'api_status' => 200,
    'data' => $most_matched,
);
