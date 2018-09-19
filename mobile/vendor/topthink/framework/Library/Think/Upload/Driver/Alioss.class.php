<?php
namespace Think\Upload\Driver;

use OSS\OssClient;
use OSS\Core\OssException;

class Alioss
{

    /**
     * 上传文件根目录
     * @var string
     */
    private $rootPath;

    /**
     * 上传错误信息
     * @var string
     */
    private $error = '';

    /**
     * OSS配置
     * @var array
     */
    private $config = array(
        'bucket' => '',
        'accessKeyId' => '', // 您从OSS获得的AccessKeyId
        'accessKeySecret' => '', // 您从OSS获得的AccessKeySecret
        'endpoint' => '', // 您选定的OSS数据中心访问域名
        'isCName' => false
    );

    private $client;

    public function __construct($config)
    {
        $this->config = array_merge($this->config, $config);
        // OssClient初始化
        try {
            $this->client = new OssClient($this->config['accessKeyId'], $this->config['accessKeySecret'], $this->config['endpoint'], $this->config['isCName']);
        } catch (OssException $e) {
            $this->error = $e->getMessage();
        }
    }

    /**
     * 检测上传根目录(OSS上传时支持自动创建目录，直接返回)
     * @param string $rootpath 根目录
     * @return boolean true-检测通过，false-检测失败
     */
    public function checkRootPath($rootpath)
    {
        $this->rootPath = trim($rootpath, './') . '/';
        return true;
    }

    /**
     * 检测上传目录(OSS上传时支持自动创建目录，直接返回)
     * @param  string $savepath 上传目录
     * @return boolean          检测结果，true-通过，false-失败
     */
    public function checkSavePath($savepath)
    {
        return true;
    }

    /**
     * 创建文件夹 (OSS上传时支持自动创建目录，直接返回)
     * @param  string $savepath 目录名称
     * @return boolean          true-创建成功，false-创建失败
     */
    public function mkdir($savepath)
    {
        return true;
    }

    /**
     * 保存指定文件
     * @param  array $file 保存的文件信息
     * @param  boolean $replace 同名文件是否覆盖
     * @return boolean          保存状态，true-成功，false-失败
     */
    public function save(&$file, $replace = true)
    {
        $file['url'] = $file['savepath'] . $file['savename'];
        $bucket = $this->config['bucket'];
        $object = $file['url'];
        $filePath = $file['tmp_name'];
        $options = array();
        try {
            $this->client->uploadFile($bucket, $object, $filePath, $options);
            return true;
        } catch (OssException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 处理缩略图
     * @param $files
     * @param array $config
     */
    public function buildThumb($files, $config = array())
    {
        return false;
    }

    /**
     * 删除文件
     * @param string $filePath
     * @return bool
     */
    public function delete($filePath = ''){
        $bucket = $this->config['bucket'];
        $object = $filePath;
        try {
            $this->client->deleteObject($bucket, $object);
            return true;
        } catch (OssException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 获取最后一次上传错误信息
     * @return string 错误信息
     */
    public function getError()
    {
        return $this->error;
    }
}