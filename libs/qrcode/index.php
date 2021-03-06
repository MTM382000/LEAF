<?php 
include 'qrlib.php';
$cacheDir = 'cache/';

$encode = 'Invalid Input.';
if(isset($_GET['encode'])) {
    $len = strlen($_GET['encode']);
    if($len > 0 && $len < 4000) {
        $encode = $_GET['encode'];
    }
}

$cacheFile = $cacheDir . 'tmp_' . sha1($encode) . '.png';
if(file_exists($cacheFile)) {
    // prune cache... sometimes.
    if(mt_rand(1, 100) == 1) {
        $queue = scandir($cacheDir);

        $time = time();
        foreach($queue as $item) {
            if(strpos($item, 'tmp_') !== false && strpos($item, '.png') == 44) {
                // delete if older than 1 week
                $cachedItem = $cacheDir.$item;
                if(($time - filemtime($cachedItem)) > 604800
                        && $cacheFile != $cachedItem) {
                    unlink($cachedItem);
                }
            }
        }
    }
}
else {
    QRcode::png($encode, $cacheFile, 'L', 4, 2);
}

$time = filemtime($cacheFile);
if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == date(DATE_RFC822, $time)) {
    header('Last-Modified: ' . date(DATE_RFC822, $time), true, 304);
}
else {
    header('Last-Modified: ' . date(DATE_RFC822, $time));
    header('Expires: ' . date(DATE_RFC822, time() + 604800));
    header('Content-Type: image/png');
    echo file_get_contents($cacheFile);
}

?>