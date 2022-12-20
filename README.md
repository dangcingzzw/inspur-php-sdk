# 安装

###### 更新时间： 2022-12-10

> 目录
>
> [环境准备](#环境准备)
>
> [下载sdk](#下载sdk)

## 环境准备

* 环境要求

  - php: >=5.6.0
  - [guzzlehttp/guzzle](https://packagist.org/packages/guzzlehttp/guzzle): ^6.3.0 || ^7.0
  - [guzzlehttp/psr7](https://packagist.org/packages/guzzlehttp/psr7): ^1.4.2 || ^2.0
  - [monolog/monolog](https://packagist.org/packages/monolog/monolog): ^1.23.0 || ^2.0

* 查看版本

  执行命令`php -version`查看PHP版本

## 下载sdk

* [sdk下载](https://github.com/dangcingzzw/inspur-php-sdk)
* https://github.com/dangcingzzw/inspur-php-sdk



## 简单文件上传

文件上传使用本地文件作为OSS文件的数据源。

以下代码用于简单文件上传：

```php
// 声明命名空间
use OSS\OSSClient;
// 创建OSSClient实例
$OSSClient = new OSSClient([
       'key' => '*** Provide your Access Key ***',
       'secret' => '*** Provide your Secret Key ***',
       'endpoint' => 'https://your-endpoint'
]);
$resp = $OSSClient->putObject([ 
       		  'Bucket' => 'bucketname',
              'Key' => 'objectkey'，
              'SourceFile' => 'localfile',
              'Body' => 'Hello OSS',
              'ContentType' => 'text/plain'
]);
printf ( "RequestId:%s\n", $resp ['RequestId'] );
```

## 下载对象

下载指定桶中的对象。

以下代码用于下载指定对象：  

```php
// 声明命名空间
use OSS\OSSClient;
// 创建OSSClient实例
$OSSClient = new OSSClient([
       'key' => '*** Provide your Access Key ***',
       'secret' => '*** Provide your Secret Key ***',
       'endpoint' => 'https://your-endpoint'
]);
try{
    $resp = $OSSClient->getObject([ 
              'Bucket' => 'bucketname',
              'Key' => 'objectkey',
              'Range' => 'bytes=0-10'
    ]);
       printf("RequestId:%s\n", $resp['RequestId']);
       printf("ETag:%s\n", $resp['ETag']);
       printf("VersionId:%s\n", $resp['VersionId']);
       printf("StorageClass:%s\n", $resp['StorageClass']);
       printf("ContentLength:%s\n", $resp['ContentLength']);
       printf("DeleteMarker:%s\n", $resp['DeleteMarker']);
       printf("LastModified:%s\n", $resp['LastModified']);
       printf("Body:%s\n", $resp['Body']);
       printf("Metadata:%s\n", print_r($resp['Metadata'], true));
}catch (OSS\Common\OSSException $OSSException){
       printf("ExceptionCode:%s\n", $OSSException->getExceptionCode());
       printf("ExceptionMessage:%s\n", $OSSException->getExceptionMessage());
}
```

## 删除对象

删除指定桶中的对象

以下代码用于删除指定桶中的对象：

```php
// 声明命名空间
use OSS\OSSClient;
// 创建OSSClient实例
$OSSClient = new OSSClient([
       'key' => '*** Provide your Access Key ***',
       'secret' => '*** Provide your Secret Key ***',
       'endpoint' => 'https://your-endpoint'
]);
try{
    $resp = $OSSClient->deleteObject([ 
            'Bucket' => 'bucketname'         
    ]);
}catch (OSS\Common\OSSException $OSSException){
       printf("ExceptionCode:%s\n", $OSSException->getExceptionCode());
       printf("ExceptionMessage:%s\n", $OSSException->getExceptionMessage());
}
```

## 简单创建存储桶

以下代码用于简单创建存储桶：

```php
// 声明命名空间
use OSS\OSSClient;
// 创建OSSClient实例
$OSSClient = new OSSClient([
       'key' => '*** Provide your Access Key ***',
       'secret' => '*** Provide your Secret Key ***',
       'endpoint' => 'https://your-endpoint'
]);
// 创建桶
$resp = $OSSClient->createBucket([
       'Bucket' => 'bucketname',
]);
printf("RequestId:%s\n", $resp['RequestId']);
```

存储桶（Bucket）是存储对象（Object）的容器，对象都隶属于存储桶。

本节介绍如何删除存储桶。

<font color="red">⚠   删除存储桶之前，必须先删除存储桶下的所有文件、分片上传产生的碎片。</font>

以下代码用于删除存储桶：

```php
// 声明命名空间
use OSS\OSSClient;
// 创建OSSClient实例
$OSSClient = new OSSClient([
       'key' => '*** Provide your Access Key ***',
       'secret' => '*** Provide your Secret Key ***',
       'endpoint' => 'https://your-endpoint'
]);
//删除bucket
$resp = $OSSClient->deleteBucket([
       'Bucket' => 'bucketname',
]);
printf("RequestId:%s\n", $resp['RequestId']);

```
