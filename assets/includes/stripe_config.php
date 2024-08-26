<?php
// +------------------------------------------------------------------------+
// | Softravine - The Ultimate Social Networking Platform
// | Copyright (c) 2024 Softravine. All rights reserved.
// +------------------------------------------------------------------------+
require_once 'assets/libraries/stripe/vendor/autoload.php';
$stripe = array(
    "secret_key" => $wo['config']['stripe_secret'],
    "publishable_key" => $wo['config']['stripe_id'],
);

\Stripe\Stripe::setApiKey($stripe['secret_key']);
