<?php
if ($f == 'get_next_video') {
    $html = '';
    $postsData = array(
        'limit' => 1,
        'filter_by' => 'video',
        'after_post_id' => Wo_Secure($_GET['post_id']),
        'order' => 'DESC',
    );
    $reelOwnerName = null;
    if (!empty($_GET['type']) && !empty($_GET['id'])) {
        if ($_GET['type'] == 'profile') {
            $postsData['publisher_id'] = $_GET['id'];
        } else if ($_GET['type'] == 'page') {
            $postsData['page_id'] = $_GET['id'];
        } else if ($_GET['type'] == 'group') {
            $postsData['group_id'] = $_GET['id'];
        }
    }

    if ($_GET['type'] == 'reels') {
        $postsData['is_reel'] = 'only';
        unset($postsData['after_post_id']);
        $postsData['order'] = 'ASC';
        $postsData['before_post_id'] = Wo_Secure($_GET['post_id']);
    }

    // if ($_GET['user']) {
    //     $reelOwnerName = Wo_Secure($_GET['user']);
    //     $reelOwnerId = Wo_UserIdFromUsername(Wo_Secure($_GET['user']));
    //     $postsData['publisher_id'] = $reelOwnerId;
    // }

    $videos = Wo_GetPosts($postsData);

    if (!$videos) {
        if ($_GET['type'] == 'reels') {
            //unset($postsData['before_post_id']);
            $videos = Wo_GetPosts($postsData);
        }
    }

    foreach ($videos as $wo['story']) {
        if (empty($wo['story']['album_name']) && $wo['story']['multi_image'] != 1) {
            if ($wo['story']['is_reel'] == 1) {
                $wo['page'] = 'reels';
                $wo['story']['likeCount'] = Wo_CountLikes($wo['story']['id']);
                $wo['story']['commentCount'] = Wo_CountPostComment($wo['story']['id']);
                $wo['reelOwnerName'] = $reelOwnerName;
            }
            $html .= Wo_LoadPage('lightbox/content');
        }
    }



    $data = array(
        'status' => 200,
        'html' => $html,
        'post_id' => (!empty($wo['story']) ? $wo['story']['id'] : ''),
    );

    if ($_GET['type'] == 'reels' && !empty($wo['story'])) {
        $data['url'] = Wo_SeoLink('index.php?link1=reels&id='.$wo['story']['id']);
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
