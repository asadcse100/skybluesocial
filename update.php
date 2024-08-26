<?php
if (file_exists('assets/init.php')) {
    require 'assets/init.php';
} else {
    die('Please put this file in the home directory !');
}
if (!file_exists('update_langs')) {
    die('Folder ./update_langs is not uploaded and missing, please upload the update_langs folder.');
}

$versionToUpdate = '4.3';
$olderVersion = '4.2.1';

if ($wo['config']['version'] == $versionToUpdate && $wo['config']['filesVersion'] == $wo['config']['version']) {
    die("Your website is already updated to {$versionToUpdate}, nothing to do.");
}
if ($wo['config']['version'] == $versionToUpdate && $wo['config']['filesVersion'] != $wo['config']['version']) {
    die("Your website is database is updated to {$versionToUpdate}, but files are not uploaded, please upload all the files and make sure to use SFTP, all files should be overwritten.");
}
if ($wo['config']['version'] > $olderVersion) {
    die("Please update to {$olderVersion} first version by version, your current version is: " . $wo['config']['version']);
}

ini_set('max_execution_time', 0);
$updated = false;

function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}
function updateLangs($lang) {
    global $sqlConnect;
    if (!file_exists("update_langs/{$lang}.txt")) {
        $filename = "update_langs/unknown.txt";
    } else {
        $filename = "update_langs/{$lang}.txt";
    }
    // Temporary variable, used to store current query
    $templine = '';
    // Read in entire file
    $lines    = file($filename);
    // Loop through each line
    foreach ($lines as $line) {
        // Skip it if it's a comment
        if (substr($line, 0, 2) == '--' || $line == '')
            continue;
        // Add this line to the current segment
        $templine .= $line;
        $query = false;
        // If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1, 1) == ';') {
            // Perform the query
            $templine = str_replace('`{unknown}`', "`{$lang}`", $templine);
            //echo $templine;
            $query    = mysqli_query($sqlConnect, $templine);
            // Reset temp variable to empty
            $templine = '';
        }
    }
}

if (!empty($_GET['updated'])) {
    $updated = true;
}
if (!empty($_POST['query'])) {
    $query = mysqli_query($sqlConnect, base64_decode($_POST['query']));
    if ($query) {
        $data['status'] = 200;
    } else {
        $data['status'] = 400;
        $data['error']  = mysqli_error($sqlConnect);
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
if (!empty($_POST['update_langs'])) {
    $data  = array();
    $query = mysqli_query($sqlConnect, "SHOW COLUMNS FROM `Wo_Langs`");
    while ($fetched_data = mysqli_fetch_assoc($query)) {
        $data[] = $fetched_data['Field'];
    }
    unset($data[0]);
    unset($data[1]);
    unset($data[2]);
    $lang_update_queries = array();
    foreach ($data as $key => $value) {
        updateLangs($value);
    }
    deleteDirectory("update_langs");
    Wo_UploadToS3($wo["userDefaultBlur"],[
        'amazon' => 1,
        'wasabi' => 1,
        'delete' => 1,
    ]);
    $db->where('name', 'version')->update(T_CONFIG, ['value' => $versionToUpdate]);
    $name = md5(microtime()) . '_updated.php';
    rename('update.php', $name);
}
?>
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
      <meta name="viewport" content="width=device-width, initial-scale=1"/>
      <title>Updating WoWonder</title>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
      <style>
         @import url('https://fonts.googleapis.com/css?family=Roboto:400,500');
         @media print {
            .wo_update_changelog {max-height: none !important; min-height: !important}
            .btn, .hide_print, .setting-well h4 {display:none;}
         }
         * {outline: none !important;}
         body {background: #f3f3f3;font-family: 'Roboto', sans-serif;}
         .light {font-weight: 400;}
         .bold {font-weight: 500;}
         .btn {height: 52px;line-height: 1;font-size: 16px;transition: all 0.3s;border-radius: 2em;font-weight: 500;padding: 0 28px;letter-spacing: .5px;}
         .btn svg {margin-left: 10px;margin-top: -2px;transition: all 0.3s;vertical-align: middle;}
         .btn:hover svg {-webkit-transform: translateX(3px);-moz-transform: translateX(3px);-ms-transform: translateX(3px);-o-transform: translateX(3px);transform: translateX(3px);}
         .btn-main {color: #ffffff;background-color: #a84849;border-color: #a84849;}
         .btn-main:disabled, .btn-main:focus {color: #fff;}
         .btn-main:hover {color: #ffffff;background-color: #c45a5b;border-color: #c45a5b;box-shadow: -2px 2px 14px rgba(168, 72, 73, 0.35);}
         svg {vertical-align: middle;}
         .main {color: #a84849;}
         .wo_update_changelog {
          border: 1px solid #eee;
          padding: 10px !important;
         }
         .content-container {display: -webkit-box; width: 100%;display: -moz-box;display: -ms-flexbox;display: -webkit-flex;display: flex;-webkit-flex-direction: column;flex-direction: column;min-height: 100vh;position: relative;}
         .content-container:before, .content-container:after {-webkit-box-flex: 1;box-flex: 1;-webkit-flex-grow: 1;flex-grow: 1;content: '';display: block;height: 50px;}
         .wo_install_wiz {position: relative;background-color: white;box-shadow: 0 1px 15px 2px rgba(0, 0, 0, 0.1);border-radius: 10px;padding: 20px 30px;border-top: 1px solid rgba(0, 0, 0, 0.04);}
         .wo_install_wiz h2 {margin-top: 10px;margin-bottom: 30px;display: flex;align-items: center;}
         .wo_install_wiz h2 span {margin-left: auto;font-size: 15px;}
         .wo_update_changelog {padding:0;list-style-type: none;margin-bottom: 15px;max-height: 440px;overflow-y: auto; min-height: 440px;}
         .wo_update_changelog li {margin-bottom:7px; max-height: 20px; overflow: hidden;}
         .wo_update_changelog li span {padding: 2px 7px;font-size: 12px;margin-right: 4px;border-radius: 2px;}
         .wo_update_changelog li span.added {background-color: #4CAF50;color: white;}
         .wo_update_changelog li span.changed {background-color: #e62117;color: white;}
         .wo_update_changelog li span.improved {background-color: #9C27B0;color: white;}
         .wo_update_changelog li span.compressed {background-color: #795548;color: white;}
         .wo_update_changelog li span.fixed {background-color: #2196F3;color: white;}
         input.form-control {background-color: #f4f4f4;border: 0;border-radius: 2em;height: 40px;padding: 3px 14px;color: #383838;transition: all 0.2s;}
input.form-control:hover {background-color: #e9e9e9;}
input.form-control:focus {background: #fff;box-shadow: 0 0 0 1.5px #a84849;}
         .empty_state {margin-top: 80px;margin-bottom: 80px;font-weight: 500;color: #6d6d6d;display: block;text-align: center;}
         .checkmark__circle {stroke-dasharray: 166;stroke-dashoffset: 166;stroke-width: 2;stroke-miterlimit: 10;stroke: #7ac142;fill: none;animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;}
         .checkmark {width: 80px;height: 80px; border-radius: 50%;display: block;stroke-width: 3;stroke: #fff;stroke-miterlimit: 10;margin: 100px auto 50px;box-shadow: inset 0px 0px 0px #7ac142;animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;}
         .checkmark__check {transform-origin: 50% 50%;stroke-dasharray: 48;stroke-dashoffset: 48;animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;}
         @keyframes stroke { 100% {stroke-dashoffset: 0;}}
         @keyframes scale {0%, 100% {transform: none;}  50% {transform: scale3d(1.1, 1.1, 1); }}
         @keyframes fill { 100% {box-shadow: inset 0px 0px 0px 54px #7ac142; }}
      </style>
   </head>
   <body>
      <div class="content-container container">
         <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-10">
               <div class="wo_install_wiz">
                 <?php if ($updated == false) { ?>
                  <div>
                     <h2 class="light">Update to v<?php echo $versionToUpdate;?></span></h2>
                     <div class="setting-well">
                        <h4>Changelog</h4>
                        <ul class="wo_update_changelog">
                        <li>[Added] monetization system, users are able now to sell their own content like only fans.</li>
                                <li>[Added] directory system, users can view the site content without the need to login.</li>
                                <li>[Added] the option to choose the default landing page, NewsFeed, Register, Welcome or Directory.</li>
                                <li>[Added] reels system, users can upload their own videos as reels.</li>
                                <li>[Added] watch page, users can view and watch all videos from one page. </li>
                                <li>[Added] qiwi pament method.</li>
                                <li>[Added] payfast payment method.</li>
                                <li>[Added] switch accounts system, user can login to multiple accounts.</li>
                                <li>[Added] the ability for users to create ads in story system.</li>
                                <li>[Added] wordpress login system.</li>
                                <li>[Fixed] 95+ reported bugs in the system.</li>
                                <li>[Improved] code in few files.</li>
                                <li>[Updated] few libs in PHP.</li>
                                <li>[Improved] nodejs chat system speed and code. </li>
                        </ul>
                        <p class="hide_print">Note: The update process might take few minutes.</p>
                        <p class="hide_print">Important: If you got any fail queries, please copy them, open a support ticket and send us the details.</p>
                        <p class="hide_print">Most of the features are disabled by default, you can enable them from Admin -> Manage Features -> Enable / Disable Features, reaction can be enabled from Settings > Posts Sttings.</p><br>
                        <p class="hide_print">Please enter your valid purchase code:</p>
                        <input type="text" id="input_code" class="form-control" placeholder="Your Envato purchase code" style="padding: 10px; width: 50%;"><br>

                        <br>
                             <button class="pull-right btn btn-default" onclick="window.print();">Share Log</button>
                             <button type="button" class="btn btn-main" id="button-update" disabled>
                             Update
                             <svg viewBox="0 0 19 14" xmlns="http://www.w3.org/2000/svg" width="18" height="18">
                                <path fill="currentColor" d="M18.6 6.9v-.5l-6-6c-.3-.3-.9-.3-1.2 0-.3.3-.3.9 0 1.2l5 5H1c-.5 0-.9.4-.9.9s.4.8.9.8h14.4l-4 4.1c-.3.3-.3.9 0 1.2.2.2.4.2.6.2.2 0 .4-.1.6-.2l5.2-5.2h.2c.5 0 .8-.4.8-.8 0-.3 0-.5-.2-.7z"></path>
                             </svg>
                          </button>
                     </div>
                     <?php }?>
                     <?php if ($updated == true) { ?>
                      <div>
                        <div class="empty_state">
                           <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                              <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                              <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                           </svg>
                           <p>Congratulations, you have successfully updated your site. Thanks for choosing WoWonder.</p>
                           <br>
                           <a href="<?php echo $wo['config']['site_url'] ?>" class="btn btn-main" style="line-height:50px;">Home</a>
                        </div>
                     </div>
                     <?php }?>
                  </div>
               </div>
            </div>
            <div class="col-md-1"></div>
         </div>
      </div>
   </body>
</html>
<script>
var queries = [
    "ALTER TABLE `Wo_Products` CHANGE COLUMN `price` `price` FLOAT(20) NOT NULL DEFAULT 0;",
    "INSERT INTO Wo_Config (name, value)VALUES ('watch_page', 1);",
    "INSERT INTO Wo_Config (name, value)VALUES ('directory_system', 1);",
    "INSERT INTO Wo_Config (name, value)VALUES ('directory_landing_page', 'welcome');",
    "INSERT INTO Wo_Config (name, value)VALUES ('switch_account', 1);",
    "INSERT INTO Wo_Config (name, value)VALUES ('wordpressLogin', 0);",
    "INSERT INTO Wo_Config (name, value)VALUES ('WordPressAppkey', \"\");",
    "INSERT INTO Wo_Config (name, value)VALUES ('WordPressAppId', \"\");",
    "ALTER TABLE Wo_UserStory ADD ad_id int default null;",
    "ALTER TABLE Wo_UserStory ADD constraint Wo_UserStory_Wo_UserAds_id_fk foreign key (ad_id) references Wo_UserAds (id);",
    "ALTER TABLE Wo_Posts ADD videoTitle varchar(200) default null;",
    "ALTER TABLE Wo_Posts ADD is_reel tinyint default 0;",
    "INSERT INTO Wo_Config (name, value)VALUES ('reels_upload_request', 'all');",
    "INSERT INTO Wo_Config (name, value)VALUES ('reels_upload', '1');",
    "INSERT INTO Wo_Config (name, value)VALUES ('monetization', '1');",
    "INSERT INTO Wo_Config (name, value)VALUES ('monetization_commission_percentage', 10);",
    "CREATE TABLE `Wo_UserMonetization` (`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,`user_id` int(11) UNSIGNED NOT NULL DEFAULT '0',`price` varchar(11) NOT NULL DEFAULT '0',`currency` varchar(5) NOT NULL DEFAULT 'USD',`paid_every` int(3) NOT NULL DEFAULT 1,`period` varchar(10) NOT NULL DEFAULT 'Daily',`multiplier` int(3) NOT NULL DEFAULT '1',`title` varchar(255) NOT NULL,`description` tinytext NOT NULL,`status` tinyint NOT NULL DEFAULT 1,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
    "ALTER TABLE Wo_Posts MODIFY COLUMN postPrivacy ENUM('0', '1', '2', '3', '4', '5', '6') DEFAULT '1' NOT NULL;",
    "CREATE TABLE `Wo_MonetizationSubscription` ( `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,`user_id` int(11) UNSIGNED NOT NULL DEFAULT '0',`monetization_id` int(11) NOT NULL,`status` tinyint NOT NULL DEFAULT 1,`last_payment_date` timestamp default current_timestamp() not null ,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
    "ALTER TABLE Wo_Payment_Transactions ADD admin_commission decimal(11) unsigned default 0;",
    "INSERT INTO Wo_Config (name, value)VALUES ('qiwi_payment', 0);",
    "INSERT INTO Wo_Config (name, value)VALUES ('qiwi_merchant_id', '');",
    "INSERT INTO Wo_Config (name, value)VALUES ('qiwi_public_key', '');",
    "INSERT INTO Wo_Config (name, value)VALUES ('qiwi_private_key', '');",
    "ALTER TABLE Wo_UserMonetization drop column multiplier;",
    "ALTER TABLE Wo_GroupChat ADD type varchar(50) NOT NULL DEFAULT 'group';",
    "ALTER TABLE `Wo_GroupChat`  ADD KEY `type` (`type`);",
    "ALTER TABLE Wo_Messages  ADD remove_at int(11) UNSIGNED NOT NULL DEFAULT '0';",
    "ALTER TABLE `Wo_Messages` ADD KEY `remove_at` (`remove_at`);",
    "ALTER TABLE Wo_GroupChat ADD destruct_at int(11) UNSIGNED NOT NULL DEFAULT '0';",
    "ALTER TABLE `Wo_GroupChat` ADD KEY `destruct_at` (`destruct_at`);",
    "INSERT INTO Wo_Config (name, value)VALUES ('admob_point', 5);",
    "ALTER TABLE Wo_Users  ADD phone_privacy enum('0','1','2') NOT NULL DEFAULT '0';",
    "INSERT INTO Wo_Config (name, value) VALUES ('fluttewave_public_key', '');",
    "INSERT INTO Wo_Config (name, value) VALUES ('fluttewave_encryption_key', '');",
    "INSERT INTO Wo_Config (name, value) VALUES ('payfast_payment', '0');",
    "INSERT INTO Wo_Config (name, value) VALUES ('payfast_mode', 'sandbox');",
    "INSERT INTO Wo_Config (name, value) VALUES ('payfast_merchant_id', '');",
    "INSERT INTO Wo_Config (name, value) VALUES ('payfast_merchant_key', '');",
    "ALTER TABLE Wo_UsersChat ADD type varchar(50) NOT NULL DEFAULT 'chat';",
    "ALTER TABLE Wo_UsersChat ADD disappearing_time varchar(50) NOT NULL DEFAULT '';",
    "INSERT INTO Wo_Config (name, value) VALUES ('exchangerate_key', '');",
    "ALTER TABLE Wo_AudioCalls ADD status varchar(100) NOT NULL DEFAULT '';",
    "ALTER TABLE Wo_VideoCalles ADD status varchar(100) NOT NULL DEFAULT '';",
    "INSERT INTO Wo_Config (name, value) VALUES ('qiwi_mode', 'sandbox');",
    "ALTER TABLE Wo_Posts ADD blur_url varchar(300) default '';",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'watch');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'there_are_no_videos_watchable');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'publisher_box_title_placeholder');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'title_required_for_video');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'untitled_video');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'switch_account');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'add_account');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'upload_reels');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'reels');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'monetization');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'post_monetization');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'period');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'monetization_added');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'edit_monetization');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'monetized');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'subscribed_successfully');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'subscribed_to');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'subscribed');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'post_is_monetized');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'subscription_earnings');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'subscribe');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'subscribed_to_you');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'subscriptions');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'unsubscribe_from_monetization');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'unsubscribe');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'unsubscribe_successful');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'qiwi');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'photos_selected');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'generate_ai_tweet');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'saved_tweets');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'retweet');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'share_new_tweet');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'users_shared_tweet');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'shared_a_tweet');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'monetized_tx');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'there_are_no_subscriptions');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'delete_your_monetization');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'are_you_delete_your_monetization');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'add_new_monetization');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'monetization_edited');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'no_monetization_found');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'directory');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'chat_group_mention');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'not_channel_owner');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'friends_request');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'payfast');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'name_more_than4');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'name_less_than100');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'desc_more_than4');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'desc_less_than190');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'you_can_create_just_plans');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'unlock_your_earning_potential');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'choose_monetization_pack');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'next_up');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'explore_captivating_content');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'explore_diverse_community');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'explore_a_world_of');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'explore_diverse_communities');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'explore_captivating_stories');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'explore_a_curated_marketplace');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'explore_a_world_of_exciting');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'dive_into_a_world');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'explore_diverse_discussions');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'explore_captivating_films');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'unlock_your_career');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'unlock_financial_opportunities');",
    "INSERT INTO `Wo_Langs` (`id`, `lang_key`) VALUES (NULL, 'no_money_for_subscriptions');",
];

$('#input_code').bind("paste keyup input propertychange", function(e) {
    if (isPurchaseCode($(this).val())) {
        $('#button-update').removeAttr('disabled');
    } else {
        $('#button-update').attr('disabled', 'true');
    }
});

function isPurchaseCode(str) {
    var patt = new RegExp("(.*)-(.*)-(.*)-(.*)-(.*)");
    var res = patt.test(str);
    if (res) {
        return true;
    }
    return true;
}

$(document).on('click', '#button-update', function(event) {
    if ($('body').attr('data-update') == 'true') {
        window.location.href = '<?php echo $wo['config']['site_url']?>';
        return false;
    }
    $(this).attr('disabled', true);
    var PurchaseCode = $('#input_code').val();
    $.post('?check', {code: PurchaseCode}, function(data, textStatus, xhr) {
        if (data.status != 200) {
            $('.wo_update_changelog').html('');
            $('.wo_update_changelog').css({
                background: '#1e2321',
                color: '#fff'
            });
            $('.setting-well h4').text('Updating..');
            $(this).attr('disabled', true);
            RunQuery();
        } else {
            $(this).removeAttr('disabled');
            alert(data.error);
        }
    });
});

var queriesLength = queries.length;
var query = queries[0];
var count = 0;
function b64EncodeUnicode(str) {
    // first we use encodeURIComponent to get percent-encoded UTF-8,
    // then we convert the percent encodings into raw bytes which
    // can be fed into btoa.
    return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g,
        function toSolidBytes(match, p1) {
            return String.fromCharCode('0x' + p1);
    }));
}
function RunQuery() {
    var query = queries[count];
    $.post('?update', {
        query: b64EncodeUnicode(query)
    }, function(data, textStatus, xhr) {
        if (data.status == 200) {
            $('.wo_update_changelog').append('<li><span class="added">SUCCESS</span> ~$ mysql > ' + query + '</li>');
        } else {
            $('.wo_update_changelog').append('<li><span class="changed">FAILED</span> ~$ mysql > ' + query + '</li>');
        }
        count = count + 1;
        if (queriesLength > count) {
            setTimeout(function() {
                RunQuery();
            }, 1500);
        } else {
            $('.wo_update_changelog').append('<li><span class="added">Updating & Adding Langauges</span> ~$ languages.sh, Please wait, this might take some time..</li>');
            $.post('?run_lang', {
                update_langs: 'true'
            }, function(data, textStatus, xhr) {
              $('.wo_update_changelog').append('<li><span class="fixed">Finished!</span> ~$ Congratulations! you have successfully updated your site. Thanks for choosing WoWonder.</li>');
              $('.setting-well h4').text('Update Log');
              $('#button-update').html('Home <svg viewBox="0 0 19 14" xmlns="http://www.w3.org/2000/svg" width="18" height="18"> <path fill="currentColor" d="M18.6 6.9v-.5l-6-6c-.3-.3-.9-.3-1.2 0-.3.3-.3.9 0 1.2l5 5H1c-.5 0-.9.4-.9.9s.4.8.9.8h14.4l-4 4.1c-.3.3-.3.9 0 1.2.2.2.4.2.6.2.2 0 .4-.1.6-.2l5.2-5.2h.2c.5 0 .8-.4.8-.8 0-.3 0-.5-.2-.7z"></path> </svg>');
              $('#button-update').attr('disabled', false);
              $(".wo_update_changelog").scrollTop($(".wo_update_changelog")[0].scrollHeight);
              $('body').attr('data-update', 'true');
            });
        }
        $(".wo_update_changelog").scrollTop($(".wo_update_changelog")[0].scrollHeight);
    });
}
</script>
