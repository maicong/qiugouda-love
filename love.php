<?php
// error_reporting(0);

if (isset($_GET['add']) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $textHref = trim(strip_tags($_POST['textHref']));
    $textHref = str_replace('\'', '', $textHref);
    $textHref = str_replace('"', '', $textHref);

    $textMusic = trim(strip_tags($_POST['textMusic']));
    $textMusic = str_replace("'", '', $textMusic);
    $textMusic = str_replace('"', '', $textMusic);

    $textArr = $_POST['textArr'];

    for ($i = 1; $i < 79; ++$i) {
        $textShow[$i] = trim(strip_tags($textArr[$i]));
        $textShow[$i] = str_replace('\'', '', $textShow[$i]);
        $textShow[$i] = str_replace('"', '', $textShow[$i]);
    }

    if (preg_match('/^[\w\-]{3,30}$/', $textHref) === 0) {
        $return = array('status' => 0, 'msg' => '←格式不正确');
    } else {
        $str = file_get_contents(__DIR__.'/loveTpl.html');

// 模版文件
        for ($o = 1; $o < 79; ++$o) {
            $str = str_replace('{text_'.$o.'}', $textShow[$o], $str);
        }

        $str = str_replace('{work_time}', date('Y-m-d H:i:s', time()), $str);
        $str = str_replace('{music_src}', $textMusic, $str);

        $year = date('Y', time());
        $dir  = __DIR__."/{$year}";
        if (!file_exists($dir)) {
            mkdir($dir, 0755);
        }

        if (file_exists("{$dir}/{$textHref}.html")) {
            $return      = array('status' => 0, 'msg' => '←这个链接已存在');
            $path_status = '失败：存在';
        } else {
            $path   = "{$dir}/{$textHref}.html"; // 保存为
            $handle = file_put_contents($path, $str);

            if ($handle) {

                if (isset($_SERVER['HTTPS']) || '443' === $_SERVER['SERVER_PORT']) {
                    $url = 'https://';
                } else {
                    $url = 'http://';
                }
                $url         = rtrim($url.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['DOCUMENT_URI']), '\\'), '/');
                $url         = "{$url}/{$year}/{$textHref}.html";
                $return      = array('status' => 1, 'url' => $url);
                $path_status = '成功：生成';
            } else {
                $return      = array('status' => 0, 'msg' => '←系统错误暂无法处理');
                $path_status = '失败：无生';
            }

        }

        $note_path = __DIR__.'/loveNote.txt'; // 记录操作
        $note_str  = '时间：'.date('Y-m-d H:i:s', time())." {$path_status} 用户：{$textShow[75]} to {$textShow[76]} {$textHref}";
        file_put_contents($note_path, $note_str.PHP_EOL, FILE_APPEND);
    }

    header('Content-type:text/json');
    echo json_encode($return);
} else {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location:/');
}
