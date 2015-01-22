<?php

include_once './Config.php';

// Function to remove folders and files 
function rrmdir($dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file)
            if ($file != "." && $file != "..")
                rrmdir("$dir/$file");
        rmdir($dir);
    }
    else if (file_exists($dir))
        unlink($dir);
}

// Function to Copy folders and files       
function rcopy($src, $dst) {
    if (file_exists($dst))
        rrmdir($dst);
    if (is_dir($src)) {
        mkdir($dst);
        $files = scandir($src);
        foreach ($files as $file)
            if ($file != "." && $file != "..")
                rcopy("$src/$file", "$dst/$file");
    } else if (file_exists($src))
        copy($src, $dst);
}

function deleteDirectory($dirPath) {
    if (is_dir($dirPath)) {
        $objects = scandir($dirPath);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
                    deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
                } else {
                    unlink($dirPath . DIRECTORY_SEPARATOR . $object);
                }
            }
        }
        reset($objects);
        rmdir($dirPath);
    } else {
        unlink($dirPath);
    }
}

function auth($u, $p) {
    $conf = new Config();
    return (($u === $conf->getUsername()) && ($p === $conf->getPassword()));
}

function isAuth($u, $p, $s) {
    $conf = new Config();
    return (($u === $conf->getUsername()) && ($p === $conf->getPassword()) && ($s === $conf->getStatus()));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    switch ($action) {
        case "AUTH":
            if (auth($_POST['username'], $_POST['password'])) {
                $authResult = 'OK';
            } else
                $authResult = 'RESTRICTED';
            echo $authResult;
            break;

        case "REMOVE":
            if (isAuth($_COOKIE['username'], $_COOKIE['password'], $_COOKIE['status'])) {
                $authResult = 'OK';
                $fold = $_POST['fold'];
                $item = $_POST['item'];
                $conf = new Config();
                if (!empty($fold)) {
                    $authResult = $conf->getBaseDir() . $fold . "/" . $item;
                } else {
                    $authResult = $conf->getBaseDir() . "/" . $item;
                }
                deleteDirectory($authResult);
                $authResult = 'OK';
            } else {
                $authResult = 'RESTRICTED';
            }
            echo $authResult;
            break;
        case "RENAME":
            if (isAuth($_COOKIE['username'], $_COOKIE['password'], $_COOKIE['status'])) {
                $authResult = 'OK';
                $fold = $_POST['fold'];
                $item = $_POST['item'];
                $newitem = $_POST['newitem'];
                $conf = new Config();
                if (!empty($fold)) {
                    $authResult = $conf->getBaseDir() . $fold . "/" . $item;
                    $destResult = $conf->getBaseDir() . $fold . "/" . $newitem;
                } else {
                    $authResult = $conf->getBaseDir() . "/" . $item;
                    $destResult = $conf->getBaseDir() . "/" . $newitem;
                }
                //переименовываем
                if (is_dir($authResult)) {
                    rcopy($authResult, $destResult);
                    rrmdir($authResult);
                    $authResult = 'OK';
                } else {
                    rename($authResult, $destResult);
                    $authResult = 'OK';
                }
            } else {
                $authResult = 'RESTRICTED';
            }
            echo $authResult;
            break;


        case "CREATE":
            if (isAuth($_COOKIE['username'], $_COOKIE['password'], $_COOKIE['status'])) {
                $fold = $_POST['fold'];
                $item = $_POST['item'];
                $conf = new Config();
                if (!empty($fold)) {
                    $authResult = $conf->getBaseDir() . $fold . "/" . $item;
                } else {
                    $authResult = $conf->getBaseDir() . "/" . $item;
                }
                if (!mkdir($authResult, 0777, true)) {
                    $authResult = 'Не удалось создать директории...';
                } else {
                    $authResult = 'OK';
                }
            } else {
                $authResult = 'RESTRICTED';
            }
            echo $authResult;
            break;
        default:
            break;
    }
}