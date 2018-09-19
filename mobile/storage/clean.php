<?php
/**
 * 功能说明：实现缓存清理
 */

define('STORAGE_PATH', str_replace('\\', '/', __DIR__) . '/');

// 待删除的文件
$files = array(
    'framework/*',
    // 'logs/*',
);

// 删除文件
foreach ($files as $file) {
    clean_delete($file);
}

function clean_delete($file = '')
{
    $suffix = substr($file, -2);
    if ($suffix == '/*') {
        clean_del_dir(STORAGE_PATH . substr($file, 0, -1));
    } else if ($suffix == '_*') {
        clean_del_pre(STORAGE_PATH . substr($file, 0, -1));
    } else {
        unlink(STORAGE_PATH . $file);
    }
}

function clean_del_dir($dir)
{
    if (!is_dir($dir)) {
        return false;
    }
    $handle = opendir($dir);
    while (($file = readdir($handle)) !== false) {
        if ($file != "." && $file != ".." && $file != ".gitignore" && $file != "sessions") {
            is_dir("$dir/$file") ? clean_del_dir("$dir/$file") : @unlink("$dir/$file");
        }
    }
    closedir($handle);
}

function clean_del_pre($files)
{
    $dir = dirname($files);
    //打开目录
    $handle = opendir($dir);
    //列出目录中的文件
    while (($file = readdir($handle)) !== false) {
        if ($file != "." and $file != ".." && $file != ".gitignore" && $file != "sessions") {
            $prefix = basename($files);
            $FP = stripos($file, $prefix);
            if ($FP === 0) {
                unlink($dir . '/' . $file);
            }
        }
    }
    closedir($handle);
}
