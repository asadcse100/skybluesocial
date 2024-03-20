<?php

if (!$wo['config']['directory_system']) {
    header("Location: " . Wo_SeoLink('index.php?link1=welcome'));
    exit();
}

$wo['description'] = $wo['config']['siteDesc'];
$wo['keywords'] = $wo['config']['siteKeywords'];
$wo['title'] = $wo['config']['siteTitle'];
$wo['page']        = 'forum';
$wo['active']      = 'forums';
$wo['content'] = Wo_LoadPage('directory/forums');
