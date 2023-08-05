<?php

namespace Business\Hyperf\Utils\Cdn;

use Business\Hyperf\Constants\Constant;
use Business\Hyperf\Utils\Context;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\Context\ApplicationContext;
use Hyperf\Filesystem\FilesystemFactory;
use Hyperf\Stringable\Str;
use League\Flysystem\AdapterInterface;

class AliOssCdn extends ResourcesCdn
{

    /**
     * 设置配置信息
     * @param int $storeId 商城id
     * @param array $configData 配置数据
     */
    public static function setConf($storeId = 1, $configData = [])
    {

        $key = static::getContextKey($storeId);

        return Context::storeData($key, function () use ($storeId, $configData) {
            //通过应用容器 获取配置类对象
            $config = getConfig();

            //获取 disk 名称
            $diskName = 'oss';
            if (empty($diskName)) {
                return [];
            }

            $configKey = 'file.storage.' . $diskName;

            //获取driver配置
            $diskConf = DictStoreService::getListByType($storeId, $diskName);

            //设置配置
            //获取默认配置
            $defaultDiskConf = $config->get('file.storage.oss');
            data_set($defaultDiskConf, 'diskName', $diskName);
//            'oss' => [
//                'driver' => \Hyperf\Filesystem\Adapter\AliyunOssAdapterFactory::class,
//                'accessId' => env('OSS_ACCESS_ID'),
//                'accessSecret' => env('OSS_ACCESS_SECRET'),
//                'bucket' => env('OSS_BUCKET'),
//                'endpoint' => env('OSS_ENDPOINT'),
//                // 'timeout' => 3600,
//                // 'connectTimeout' => 10,
//                // 'isCName' => false,
//                // 'token' => null,
//                // 'proxy' => null,
//            ],

            $config->set($configKey, $defaultDiskConf);

            return $defaultDiskConf;
        });
    }

    /**
     * 获取云存储对象
     * @param array $extData
     * @return array  云存储对象
     */
    public static function getDisk($extData)
    {

        $rs = static::getDefaultResponseData(Constant::ORDER_STATUS_SHIPPED_INT, Constant::PARAMETER_STRING_DEFAULT);

        $storeId = data_get($extData, Constant::DB_COLUMN_SITE_ID, 0);
        if (empty($storeId)) {
            data_set($rs, Constant::CODE, 0);
            data_set($rs, Constant::MSG, 'store ID 异常');
            return $rs;
        }

        $configData = self::setConf($storeId);
        $diskName = data_get($configData, 'diskName');
        $driver = data_get($configData, 'driver');
        if (empty($diskName)) {
            data_set($rs, Constant::CODE, 2);
            data_set($rs, Constant::MSG, $storeId . ' oss 配置不存在');
            return $rs;
        }

        if (empty($driver)) {
            data_set($rs, Constant::CODE, 3);
            data_set($rs, Constant::MSG, $storeId . ' ' . $diskName . ' oss 云存储配置不存在');
            return $rs;
        }

        //获取文件系统对象
        $factory = ApplicationContext::getContainer()->get(FilesystemFactory::class);
        $filesystem = $factory->get($diskName);

        data_set($rs, Constant::DATA, $filesystem);

        return $rs;
    }

    public static function uploadBase64File($file = null, $vitualPath = '', $is_del = false, $isCn = false, $fileName = '', $resourceType = 1, $extData = Constant::PARAMETER_ARRAY_DEFAULT)
    {

        $diskData = static::getDisk($extData);
        if (data_get($diskData, Constant::CODE, 0) != 1) {
            return $diskData;
        }
        $filesystem = data_get($diskData, Constant::DATA, null);

        $_data = [
            Constant::RESOURCE_TYPE => $resourceType, //资源类型 1:图片 2:视频 3:js 4:css 默认:1
        ];
        $rs = static::getDefaultResponseData(Constant::ORDER_STATUS_SHIPPED_INT, Constant::PARAMETER_STRING_DEFAULT, $_data);

        $fileExtension = '';
        if (strpos($file, 'data:image/png;base64') !== false) {
            $data = explode(',', $file); //data:image/png;base64,iVBORw0KGgoAAAANSUhEU
            $fileContents = base64_decode(end($data));

            $fileExtension = explode('/', $data[0]); //data:image/png;base64,
            unset($data);
            $fileExtension = explode(';', $fileExtension[1]);
            $fileExtension = '.' . $fileExtension[0];
        } else {
            $fileContents = base64_decode($file);
        }

        $path = static::getUploadFileName($resourceType, $vitualPath, $fileExtension, $fileName);

        $config = [
            'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
            //'mimetype'=>'',
        ];
        $isPut = $filesystem->write(
            $path,
            $fileContents,
            $config
        );

        if (empty($isPut)) {
            data_set($rs, Constant::CODE, 0);
            data_set($rs, Constant::MSG, '文件上传失败');

            return $rs;
        }

        $storeId = data_get($extData, Constant::DB_COLUMN_SITE_ID, 0);
        $url = static::getResourceUrl($storeId, $path);

        data_set($rs, Constant::DATA . Constant::LINKER . Constant::FILE_URL, $url);
        data_set($rs, Constant::DATA . Constant::LINKER . Constant::FILE_FULL_PATH, $url);
        data_set($rs, Constant::CODE, Constant::ORDER_STATUS_SHIPPED_INT);
        data_set($rs, Constant::MSG, Constant::PARAMETER_STRING_DEFAULT);

        return $rs;
    }

    /**
     * 上传文件
     * @param string $filePath 图片在服务器的绝对路径
     * @param string $resourceType 文件类型 1：图片 2：视频
     * @param string $vitualPath 云存储虚拟路径
     * @param boolean $is_del 是否删除原文件  false:否  true：是  默认:false
     * @param boolean $isCn 是否使用国内cdn  false:否  true：是  默认:false
     * @return array 上传结果
     */
    public static function upload($filePath, $files = null, $vitualPath = '', $is_del = false, $isCn = false, $fileName = '', $resourceType = 1, $extData = Constant::PARAMETER_ARRAY_DEFAULT)
    {

        $diskData = static::getDisk($extData);
        if (data_get($diskData, Constant::CODE, 0) != 1) {
            return $diskData;
        }
        $filesystem = data_get($diskData, Constant::DATA, null);

        $_data = [
            Constant::RESOURCE_TYPE => $resourceType, //资源类型 1:图片 2:视频 3:js 4:css 默认:1
        ];
        $rs = static::getDefaultResponseData(Constant::ORDER_STATUS_SHIPPED_INT, Constant::PARAMETER_STRING_DEFAULT, $_data);

        $files = is_array($files) ? $files : [$filePath => $files];
        $uploadData = [];
        $distVitualPath = static::getDistVitualPath($resourceType, $vitualPath);
        $storeId = data_get($extData, Constant::DB_COLUMN_SITE_ID, 0);
        foreach ($files as $key => $file) {
            if (is_array($file)) {
                data_set($uploadData, $key, static::upload(null, $file, $vitualPath, $is_del, $isCn, $fileName, $resourceType, $extData));
                continue;
            }

            if (!($file instanceof UploadedFile)) {

                static::setFile(null);
                $rs = static::uploadBase64File($file, $vitualPath, $is_del, $isCn, $fileName, $resourceType, $extData);

                data_set($uploadData, $key, $rs);
                continue;
            }

            if (!$file->isValid()) {
                data_set($rs, Constant::DATA . Constant::LINKER . Constant::FILE_URL, Constant::PARAMETER_STRING_DEFAULT);
                data_set($rs, Constant::DATA . Constant::LINKER . Constant::FILE_FULL_PATH, Constant::PARAMETER_STRING_DEFAULT);
                data_set($rs, Constant::CODE, 10031);
                data_set($rs, Constant::MSG, $file->getError());//
                $uploadData[$key] = $rs;
                continue;
            }

            static::setFile($file);
            $originalName = static::getName($file->getClientFilename());//原始文件名

            if (data_get($extData, 'use_origin_name', Constant::PARAMETER_INT_DEFAULT)) {//如果需要使用原始文件名，就获取客户原始文件名
                $fileName = $originalName;
            } else {
                $extension = $file->getExtension();
                $fileName = Str::random(10) . '.' . $extension;
            }

            $config = [
                'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
                //'mimetype'=>'',
            ];
            $path = static::normalizePath(implode('/', [$distVitualPath, $fileName]));
            $stream = fopen($file->getRealPath(), 'r+');
            $filesystem->writeStream(
                $path,
                $stream,
                $config
            );
            fclose($stream);

            $url = static::getResourceUrl($storeId, $path);

            data_set($rs, Constant::DATA . Constant::LINKER . Constant::FILE_URL, $url);
            data_set($rs, Constant::DATA . Constant::LINKER . Constant::FILE_FULL_PATH, $url);
            data_set($rs, Constant::CODE, Constant::ORDER_STATUS_SHIPPED_INT);
            data_set($rs, Constant::MSG, Constant::PARAMETER_STRING_DEFAULT);
            data_set($rs, Constant::DATA . Constant::LINKER . 'originalName', $originalName);

            data_set($uploadData, $key, $rs);
        }

        return data_get($uploadData, $filePath, Constant::PARAMETER_ARRAY_DEFAULT);
    }

}
