<?php

if (isset($_POST['fold1']))
    $upload_dir = $_POST['fold1'];
if (isset($_GET['fold1']))
    $upload_dir = $_GET['fold1'];
if (isset($upload_dir))
    $upload_dir = "";
require('Uploader.php');
include_once './Config.php';

$conf = new Config();
$upload_dir = $conf->getBaseDir() . $upload_dir . "/";
//$upload_dir = 'test/';
$valid_extensions = array('jpg', 'jpeg', 'png', 'gif', 'zip', '7z', 'rar', 'exe');

$Upload = new FileUpload('uploadfile');
$result = $Upload->handleUpload($upload_dir, $valid_extensions);

if (!$result) {
    echo json_encode(array('success' => false, 'msg' => $Upload->getErrorMsg() . " " . $upload_dir));
} else {
    echo json_encode(array('success' => true, 'file' => $Upload->getFileName()));
}