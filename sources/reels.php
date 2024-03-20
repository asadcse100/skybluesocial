<?php
if ($wo['config']['reels_upload'] == 0 || $wo['have_reels'] == 0) {
    header("Location: " . $wo['config']['site_url']);
    exit();
}
// if(!isset($_COOKIE['page_before_reels'])) {
//     setcookie('page_before_reels', $_SERVER['HTTP_REFERER'],time() + 300);
// }
$reelOwnerName = null;
$getPosts = false;
$postsData = array(
    'limit' => 1,
    'filter_by' => 'video',
    'order' => 'ASC',
    'is_reel' => 'only'
);

if (!empty($_GET['user'])) {
    $reelOwnerName = Wo_Secure($_GET['user']);
    $reelOwnerId = Wo_UserIdFromUsername(Wo_Secure($_GET['user']));
    $postsData['publisher_id'] = $reelOwnerId;
    $getPosts = true;
}

if (empty($_GET['id'])) {
    $getPosts = true;
}

if ($_COOKIE['reels_id'] && empty($_GET['id'])) {
    $postsData['after_post_id'] = $_COOKIE['reels_id'];
}

if ($getPosts) {
    $reels = Wo_GetPosts($postsData);
    if (!$reels) {
        unset($postsData['after_post_id']);
        $reels = Wo_GetPosts($postsData);
        if (!$reels) {
            header("Location: " . $wo['config']['site_url']);
            exit();
        }
    }
    $id = $reels[0]['id'];
} else {
    $id = Wo_Secure($_GET['id']);
}

$wo['story'] = Wo_PostData($id);

if (empty($wo['story'])) {
    header("Location: " . $wo['config']['site_url']);
    exit();
}

setcookie("reels_id", $wo['story']['id'], time() + (10 * 365 * 24 * 60 * 60));

$wo['reelOwnerName'] = $reelOwnerName;
$wo['story']['likeCount'] = Wo_CountLikes($id);
$wo['story']['commentCount'] = Wo_CountPostComment($id);
$wo['description'] = $wo['config']['siteDesc'];
$wo['keywords'] = $wo['config']['siteKeywords'];
$wo['page'] = 'reels';
$wo['title'] = $wo['lang']['reels'];
$wo['content'] = Wo_LoadPage('lightbox/content');

