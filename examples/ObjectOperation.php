<?php

/**
 * Copyright 2022 InspurCloud Technologies Co.,Ltd.
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use
 * this file except in compliance with the License.  You may obtain a copy of the
 * License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed
 * under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 * CONDITIONS OF ANY KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations under the License.
 *
 */

/**
 * This sample demonstrates how to do bucket-related operations
 * (such as do bucket ACL/CORS/Lifecycle/Logging/Website/Location/Tagging/OPTIONS)
 * on OSS using the OSS SDK for PHP.
 */
if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
} else {
    require '../vendor/autoload.php'; // sample env
}

if (file_exists('oss-autoloader.php')) {
    require 'oss-autoloader.php';
} else {
    require '../oss-autoloader.php'; // sample env
}

use OSS\OSSClient;
use OSS\OSSException;

use function GuzzleHttp\json_encode;

$ak = 'inspur-cl-oss';

$sk = 'inspur-cl-oss';

$endpoint = 'http://10.110.64.152:8088/';

$bucketName = 'my-zdemo';
$objectKey = 'test005' . rand(10000, 9999) . '.rar';

/*
 * Constructs a OSS client instance with your account for accessing OSS
 */
$OSSClient = OSSClient::factory ( [
    'key' => $ak,
    'secret' => $sk,
    'endpoint' => $endpoint,
    'socket_timeout' => 30,
    'connect_timeout' => 10
]);

try {
    /*
     * Create object
     */
    $content = 'Hello OSS';
    $OSSClient->putObject(['Bucket' => $bucketName, 'Key' => $objectKey, 'Body' => $content]);
    printf("Create object: %s successfully!\n\n", $objectKey);


    /*
     * Get object metadata
     */
    printf("Setting object metadata\n");
    $resp = $OSSClient->setObjectMetadata([
        'Bucket' => $bucketName,
        'Key' => $objectKey,
        'Metadata' => ['meta1' => 'value665', 'meta2' => 'value666'],
    ]);

    /*
     * Set object metadata
     */
    printf("Getting object metadata\n");
    $resp = $OSSClient->getObjectMetadata([
        'Bucket' => $bucketName,
        'Key' => $objectKey,
    ]);

    printf("\tMetadata:%s\n\n", json_encode($resp));
    /*
     * Get object metadata
     */
    printf("Getting object metadata\n");
    $resp = $OSSClient->getObjectMetadata([
        'Bucket' => $bucketName,
        'Key' => $objectKey,
    ]);
    printf("\tMetadata:%s\n\n", json_encode($resp));

    /*
     * Get object
     */
    printf("Getting object content\n");
    $resp = $OSSClient->getObject(['Bucket' => $bucketName, 'Key' => $objectKey]);
    printf("\t%s\n\n", $resp['Body']);

    /*
     * Copy object
     */
    $sourceBucketName = $bucketName;
    $destBucketName = $bucketName;
    $sourceObjectKey = $objectKey;
    $destObjectKey = $objectKey . '-back';
    printf("Copying object\n\n");
    $OSSClient->copyObject([
        'Bucket' => $destBucketName,
        'Key' => $destObjectKey,
        'CopySource' => $sourceBucketName . '/' . $sourceObjectKey,
        'MetadataDirective' => OSSClient::CopyMetadata
    ]);


    /*
     * Put/Get object acl operations
     */
    doObjectAclOperations();


    $resp = $OSSClient->listObjects(['Bucket' => $bucketName]);
    printf("object list\n");
    var_dump($resp->toArray()['Contents']);

    /*
    * Put/Get object acl operations
    */
    doesObjectExist();

    /*
    * Put object SignedUrl operations
    */
    createSignedUrl();

    /*
    * Put object SignedUrl operations
    */
    doObjectVersion();

    /*
    * Delete object
    */
    printf("Deleting objects\n\n");
    $OSSClient->deleteObject(['Bucket' => $bucketName, 'Key' => $objectKey]);
    printf("delete objects content\n");
    $OSSClient->deleteObjects([
        'Bucket' => $bucketName,
        'Quiet' => false,
        'Objects' => [
            [
                'Key' => 'objectkey1',
                'VersionId' => null
            ],
            [
                'Key' => 'objectkey2',
                'VersionId' => null
            ]
        ]
    ]);

    /*
    * part upload object
    */
    doPartUpload();

    /*
    * clen multipart
    */
    aboutMultipartUpload();
} catch (OSSException $e) {
    echo 'Response Code:' . $e->getStatusCode() . PHP_EOL;
    echo 'Error Message:' . $e->getExceptionMessage() . PHP_EOL;
    echo 'Error Code:' . $e->getExceptionCode() . PHP_EOL;
    echo 'Request ID:' . $e->getRequestId() . PHP_EOL;
    echo 'Exception Type:' . $e->getExceptionType() . PHP_EOL;
} finally {
    $OSSClient->close();
}
function doPartUpload()
{
    global $OSSClient;
    global $bucketName;
    global $objectKey;
    $resp = $OSSClient->initiateMultipartUpload(['Bucket' => $bucketName, 'Key' => $objectKey]);

    $uploadId = $resp['UploadId'];
    printf("Claiming a new upload id %s\n\n", $uploadId);

    $task_list = $OSSClient->listMultipartUploads(['Bucket' => $bucketName, 'Key' => $objectKey]);
    printf("task_list");
    var_dump($task_list['Uploads']);

    $sampleFilePath = 'C:\Users\dangcingzzw\Desktop\bbb.rar'; //sample large file path
    //  you can prepare a large file in you filesystem first
    createSampleFile($sampleFilePath);

    //clear multipartUpload
//        $this->aboutMultipartUpload($objectKey,$task_list['Uploads'][0]);


    $partSize = 2 * 1024 * 1024;
    $fileLength = filesize($sampleFilePath);

    $partCount = $fileLength % $partSize === 0 ? intval($fileLength / $partSize) : intval($fileLength / $partSize) + 1;

    if ($partCount > 10000) {
        throw new \RuntimeException('Total parts count should not exceed 10000');
    }

    printf("Total parts count %d\n\n", $partCount);
    $parts = [];
    $promise = null;
    /*
     * Upload multiparts to your bucket
     */
    printf("Begin to upload multiparts to OSS from a file\n\n");
    for ($i = 0; $i < $partCount; $i++) {
        $offset = $i * $partSize;
        $currPartSize = ($i + 1 === $partCount) ? $fileLength - $offset : $partSize;
        $partNumber = $i + 1;
        $p = $OSSClient->uploadPartAsync([
            'Bucket' => $bucketName,
            'Key' => $objectKey,
            'UploadId' => $uploadId,
            'PartNumber' => $partNumber,
            'SourceFile' => $sampleFilePath,
            'Offset' => $offset,
            'PartSize' => $currPartSize
        ], function ($exception, $resp) use (&$parts, $partNumber) {
            $parts[] = ['PartNumber' => $partNumber, 'ETag' => $resp['ETag']];
            printf("Part#" . strval($partNumber) . " done\n\n");
        });

        if ($promise === null) {
            $promise = $p;
        }
    }

    /*
     * Waiting for all parts finished
     */
    $promise->wait();

    usort($parts, function ($a, $b) {
        if ($a['PartNumber'] === $b['PartNumber']) {
            return 0;
        }
        return $a['PartNumber'] > $b['PartNumber'] ? 1 : -1;
    });

    /*
     * Verify whether all parts are finished
     */
    if (count($parts) !== $partCount) {
        throw new \RuntimeException('Upload multiparts fail due to some parts are not finished yet');
    }


    printf("Succeed to complete multiparts into an object named %s\n\n", $objectKey);

    /*
     * View all parts uploaded recently
     */
    printf("Listing all parts......\n");
    $resp = $OSSClient->listParts(['Bucket' => $bucketName, 'Key' => $objectKey, 'UploadId' => $uploadId]);
    foreach ($resp['Parts'] as $part) {
        printf("\tPart#%d, ETag=%s\n", $part['PartNumber'], $part['ETag']);
    }
    printf("\n");
//var_dump([
//    'Bucket' => $bucketName,
//    'Key' => $objectKey,
//    'UploadId' => $uploadId,
//    'Parts'=> $parts
//]);die;

    /*
     * Complete to upload multiparts
     */
    $resp = $OSSClient->completeMultipartUpload([
        'Bucket' => $bucketName,
        'Key' => $objectKey,
        'UploadId' => $uploadId,
        'Parts' => $parts
    ]);
    var_dump($resp->toArray());
}

function aboutMultipartUpload()
{
    global $OSSClient;
    global $bucketName;
    $objectKey = 'oktest002';
    $uploadId = '2~g2KlYL_7wBhF_3qfeWUn3_qIXOpGrQG';
    $resp = $OSSClient->abortMultipartUpload(array(
        'Bucket' => $bucketName,
        'Key' => $objectKey,
        'UploadId' => $uploadId
    ));
    printf("HttpStatusCode:%s\n", $resp ['HttpStatusCode']);
    printf("RequestId:%s\n", $resp ['RequestId']);
}

function doObjectVersion()
{
    global $OSSClient;
    global $bucketName;
    global $objectKey;
    printf("Getting object version list\n");
    $resp = $OSSClient->listVersions([
        'Bucket' => $bucketName,
    ]);
    printf("Getting object version list\n");
    var_dump($resp->toArray()['Versions']);
    printf("delete object version\n");

    $OSSClient->deleteVersion([
        'Bucket' => $bucketName,
        'Key' => '104875txt-back',
        'VersionId' => '.aW09.xEmo3NT7M-MqYKd.DrJQFCeWi'
    ]);
    $resp = $OSSClient->listVersions([
        'Bucket' => $bucketName,
    ]);
    printf("delete object version\n");
    var_dump($resp->toArray()['Versions']);
}

function createSignedUrl()
{
    global $OSSClient;
    global $bucketName;
    global $objectKey;
    $resp = $OSSClient->createSignedUrl([
        'Bucket' => $bucketName,
        'Key' => $objectKey,
        'Method' => 'GET'
    ]);
    printf("createSignedUrl" . "\n\n");
    var_dump($resp->toArray());
}

function doesObjectExist()
{
    global $OSSClient;
    global $bucketName;
    global $objectKey;
    printf("object is exit" . "\n\n");
    $resp = $OSSClient->doesObjectExist([
        'Bucket' => $bucketName,
        'Key' => $objectKey,
    ]);
    printf("object exit" . "\n\n", $resp->toArray());
}

function doObjectAclOperations()
{
    global $OSSClient;
    global $bucketName;
    global $objectKey;

    printf("Setting object ACL to " . OSSClient::AclPublicRead . "\n\n");

    $OSSClient->setObjectAcl([
        'Bucket' => $bucketName,
        'Key' => $objectKey,
        'ACL' => OSSClient::AclPublicRead
    ]);

    printf("Getting object ACL\n");
    $resp = $OSSClient->getObjectAcl([
        'Bucket' => $bucketName,
        'Key' => $objectKey
    ]);
    printf("\tOwner:%s\n", json_encode($resp['Owner']));
    printf("\tGrants:%s\n\n", json_encode($resp['Grants']));
}

function createSampleFile($filePath)
{
    if (file_exists($filePath)) {
        return;
    }
    $filePath = iconv('UTF-8', 'GBK', $filePath);
    if (is_string($filePath) && $filePath !== '') {
        $fp = null;
        $dir = dirname($filePath);
        try {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            if (($fp = fopen($filePath, 'w'))) {
                for ($i = 0; $i < 1000000; $i++) {
                    fwrite($fp, uniqid() . "\n");
                    fwrite($fp, uniqid() . "\n");
                    if ($i % 100 === 0) {
                        fflush($fp);
                    }
                }
            }
        } finally {
            if ($fp) {
                fclose($fp);
            }
        }
    }
}



