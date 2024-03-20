<?php 
$site_url_login = $config['site_url'];
if(substr($site_url_login, -1) == '/') {
    $site_url_login = substr($site_url_login, 0, -1);
}
$callback = $site_url_login . '/login-with.php?provider=' . $provider;
if ($provider == 'Vkontakte') {
	$callback = $site_url_login . '/vkontakte_callback';
}
$LoginWithConfig = array(
    'callback' => $callback,

    'providers' => array(
        "Google" => array(
			"enabled" => true,
			"keys" => array("id" => $config['googleAppId'], "secret" => $config['googleAppKey']),
		),
		"Facebook" => array(
			"enabled" => true,
			"keys" => array("id" => $config['facebookAppId'], "secret" => $config['facebookAppKey']),
			"scope" => "email",
			"trustForwarded" => false
		),
		"Twitter" => array(
			"enabled" => true,
			"keys" => array("key" => $config['twitterAppId'], "secret" => $config['twitterAppKey']),
			"includeEmail" => true
		),
		"LinkedIn" => array(
			"enabled" => true,
			"keys" => array("key" => $config['linkedinAppId'], "secret" => $config['linkedinAppKey'])
		),
		"Vkontakte" => array(
			"enabled" => true,
			"keys" => array("id" => $config['VkontakteAppId'], "secret" => $config['VkontakteAppKey'])
		),
		"Instagram" => array(
			"enabled" => true,
			"keys" => array("id" => $config['instagramAppId'], "secret" => $config['instagramAppkey'])
		),
		"QQ" => array(
			"enabled" => true,
			"keys" => array("id" => $config['qqAppId'], "secret" => $config['qqAppkey'])
		),
		"WeChat" => array(
			"enabled" => true,
			"keys" => array("id" => $config['WeChatAppId'], "secret" => $config['WeChatAppkey'])
		),
		"Discord" => array(
			"enabled" => true,
			"keys" => array("id" => $config['DiscordAppId'], "secret" => $config['DiscordAppkey'])
		),
		"Mailru" => array(
			"enabled" => true,
			"keys" => array("id" => $config['MailruAppId'], "secret" => $config['MailruAppkey'])
		),
        "WordPress" => array(
            "enabled" => true,
            "keys" => array("id" => $config['WordPressAppId'], "secret" => $config['WordPressAppkey'])
        ),
    ),
);
?>