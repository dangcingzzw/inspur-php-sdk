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
if (file_exists ( 'vendor/autoload.php' )) {
    require 'vendor/autoload.php';
} else {
    require '../vendor/autoload.php'; // sample env
}

if (file_exists ( 'oss-autoloader.php' )) {
    require 'oss-autoloader.php';
} else {
    require '../oss-autoloader.php'; // sample env
}

use OSS\OSSClient;
use OSS\OSSException;
use function GuzzleHttp\json_encode;

$ak = '*** Provide your Access Key ***';

$sk = '*** Provide your Secret Key ***';

$endpoint = 'https://your-endpoint:443';

$bucketName = 'my-OSS-bucket-demo';



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
     * Put bucket operation
     */
    createBucket ();

    /*
     * Get bucket location operation
     */
    getBucketLocation ();

    /*
     * Get bucket is exist operation
     */
    headBucket ();

    /*
     * Get bucket list  operation
     */
    listBuckets ();

    /*
    * Get bucket list  operation
    */
    pageListBuckets ();


    /*
     * Put/Get bucket versioning operations
     */
    doBucketVersioningOperation ();

    /*
     * Put/Get bucket acl operations
     */
    doBucketAclOperation ();

    /*
     * Put/Get/Delete bucket cors operations
     */
    doBucketCorsOperation ();

    /*
     * Put/Get/Delete bucket lifecycle operations
     */
    doBucketLifecycleOperation ();

    /*
     * Put/Get/Delete bucket website operations
     */
    doBucketWebsiteOperation ();

    /*
    * Put/Get/Delete bucket domain operations
    */
    doBucketPolicy();

    /*
     * Put/Get/Delete bucket domain operations
     */
    doBucketDomain();

    /*
     * Delete bucket operation
     */
    deleteBucket ();

} catch ( OSSException $e ) {
    echo 'Response Code:' . $e->getStatusCode () . PHP_EOL;
    echo 'Error Message:' . $e->getExceptionMessage () . PHP_EOL;
    echo 'Error Code:' . $e->getExceptionCode () . PHP_EOL;
    echo 'Request ID:' . $e->getRequestId () . PHP_EOL;
    echo 'Exception Type:' . $e->getExceptionType () . PHP_EOL;
} finally{
    $OSSClient->close ();
}


function createBucket()
{
    global $OSSClient;
    global $bucketName;

    $resp = $OSSClient->createBucket ([
        'Bucket' => $bucketName,
    ]);
    printf("HttpStatusCode:%s\n\n", $resp ['HttpStatusCode']);
    printf("Create bucket: %s successfully!\n\n", $bucketName);
}

function doBucketPolicy(){
    global $OSSClient;
    global $bucketName;
    printf("Hset bucket policy start...\n");
    $OSSClient->setBucketPolicy([
        'Bucket' => $bucketName,
        'Policy' => json_encode([
            "Statement" => [
                ["Principal" => "*", "Effect" => "Allow", "Action" => "ListBuckets", "Resource" => $bucketName]
            ]
        ])
    ]);
    printf("get bucket policy start...\n");
    $resp = $OSSClient->getBucketPolicy([
        'Bucket' => $bucketName
    ]);
    printf("get bucket policy start...\n");
    printf(["Policy:%s\n" => json_decode($resp['Policy'])]);

    printf("delete bucket policy start...\n");
    $resp = $OSSClient->deleteBucketPolicy([
        'Bucket' => $bucketName
    ]);
    printf(["HttpStatusCode:%s\n" => $resp ['HttpStatusCode']]);
}

function listBuckets(){
    global $OSSClient;
    global $bucketName;
    printf ("Getting bucket list:");
    $resp = $OSSClient->listBuckets();
    printf("HttpStatusCode:%s\n", $resp ['HttpStatusCode']);
    printf("RequestId:%s\n", $resp ['RequestId']);
    $i = 0;
    foreach ($resp ['Buckets'] as $bucket ) {
        printf("Buckets[$i][Name]:%s,Buckets[$i][CreationDate]:%s\n", $bucket ['Name'], $bucket ['CreationDate']);
        $i ++;
    }
    printf("Owner[ID]:%s\n", $resp ['Owner'] ['ID']);
}

function pageListBuckets(){
    global $OSSClient;
    global $bucketName;
    printf ("Getting bucket page list:%s\n\n");
    $resp = $OSSClient->pageListBuckets(
        [
            'Bucket' =>  $bucketName,
            'pageNo' => "1",
            'pageSize' => "2",
            "filterKey" => "zhaozhiwei"
        ]
    );
    printf("Owner[ID]:%s\n", $resp ['Buckets']);
}

function headBucket()
{
    global $OSSClient;
    global $bucketName;
    printf("headBucket start...\n");
    $resp = $OSSClient->headBucket(['Bucket' => $bucketName]);
    printf("HttpStatusCode:%s\n\n", $resp ['HttpStatusCode']);
    printf("HttpStatusCode:%s\n\n", $resp ['RequestId']);
}
function getBucketLocation()
{
    global $OSSClient;
    global $bucketName;

    $promise = $OSSClient -> getBucketLocationAsync(['Bucket' => $bucketName], function($exception, $resp){
        printf("Getting bucket location %s\n\n", $resp ['Location']);
    });
    $promise -> wait();
}


function doBucketVersioningOperation()
{
    global $OSSClient;
    global $bucketName;

    $resp = $OSSClient->getBucketVersioningConfiguration ( [
        'Bucket' => $bucketName
    ]);
    printf ( "Getting bucket versioning config:%s\n\n", $resp ['Status']);
    //Enable bucket versioning
    $OSSClient->setBucketVersioningConfiguration ([
        'Bucket' => $bucketName,
        'Status' => 'Enabled'
    ]);
    $resp = $OSSClient->getBucketVersioningConfiguration ( [
        'Bucket' => $bucketName
    ]);
    printf ( "Current bucket versioning config:%s\n\n", $resp ['Status']);

    //Suspend bucket versioning
    $OSSClient->setBucketVersioningConfiguration ([
        'Bucket' => $bucketName,
        'Status' => 'Suspended'
    ]);
    $resp = $OSSClient->getBucketVersioningConfiguration ( [
        'Bucket' => $bucketName
    ]);
    printf ( "Current bucket versioning config:%s\n\n", $resp ['Status']);
}

function doBucketAclOperation()
{
    global $OSSClient;
    global $bucketName;
    printf ("Setting bucket ACL to ". OSSClient::AclPublicRead. "\n\n");
    $OSSClient->setBucketAcl ([
        'Bucket' => $bucketName,
        'ACL' => OSSClient::AclPublicRead,
    ]);

    $resp = $OSSClient->getBucketAcl ([
        'Bucket' => $bucketName
    ]);
    printf ("Getting bucket ACL:%s\n\n", json_encode($resp -> toArray()));

    printf ("Setting bucket ACL to ". OSSClient::AclPrivate. "\n\n");

    $OSSClient->setBucketAcl ([
        'Bucket' => $bucketName,
        'ACL' => OSSClient::AclPrivate,
    ]);
    $resp = $OSSClient->getBucketAcl ([
        'Bucket' => $bucketName
    ]);
    printf ("Getting bucket ACL:%s\n\n", json_encode($resp -> toArray()));
    return $resp ['Owner'] ['ID'];
}

function doBucketCorsOperation()
{
    global $OSSClient;
    global $bucketName;
    printf ("Setting bucket CORS\n\n");
    $OSSClient->setBucketCors ( [
        'Bucket' => $bucketName,
        'CorsRules' => [
            [
                'AllowedMethod' => ['HEAD', 'GET', 'PUT'],
                'AllowedOrigin' => ['http://www.a1.com', 'http://www.b2.com'],
                'AllowedHeader'=> ['Authorization'],
                'ExposeHeaders' => ['x-OSS-test1', 'x-OSS-test2'],
                'MaxAgeSeconds' => 100
            ]
        ]
    ] );
    printf ("Getting bucket CORS:%s\n\n", json_encode($OSSClient-> getBucketCors(['Bucket' => $bucketName])-> toArray()));

}

function optionsBucket()
{
    global $OSSClient;
    global $bucketName;

    $resp = $OSSClient->optionsBucket([
        'Bucket'=>$bucketName,
        'Origin'=>'http://www.a.com',
        'AccessControlRequestMethods' => ['PUT'],
        'AccessControlRequestHeaders'=> ['Authorization']
    ]);
    printf ("Options bucket: %s\n\n", json_encode($resp -> toArray()));

}
function setBucketMetadata(){

}
function getBucketMetadata()
{
    global $OSSClient;
    global $bucketName;
    printf ("Getting bucket metadata\n\n");

    $resp = $OSSClient->getBucketMetadata ( [
        "Bucket" => $bucketName,
        "Origin" => "http://www.a.com",
        "RequestHeader" => "Authorization"
    ] );
    printf ( "\tHttpStatusCode:%s\n", $resp ['HttpStatusCode'] );
    printf ( "\tStorageClass:%s\n", $resp ["StorageClass"] );
    printf ( "\tAllowOrigin:%s\n", $resp ["AllowOrigin"] );
    printf ( "\tMaxAgeSeconds:%s\n", $resp ["MaxAgeSeconds"] );
    printf ( "\tExposeHeader:%s\n", $resp ["ExposeHeader"] );
    printf ( "\tAllowHeader:%s\n", $resp ["AllowHeader"] );
    printf ( "\tAllowMethod:%s\n", $resp ["AllowMethod"] );

    printf ("Deleting bucket CORS\n\n");
    $OSSClient -> deleteBucketCors(['Bucket' => $bucketName]);
}
function doBucketDomain(){
    global $OSSClient;
    global $bucketName;
    printf("set bucket domain start...\n");
    $OSSClient->setBucketDomain([
        'Bucket' => 'zhaozhiwei',
        'Domain' => json_encode([
            ["domainName" => "www.example213.com", "isWebsite" => false],
            ["domainName" => "www.example212.com", "isWebsite" => false]
        ])
    ]);

    printf("get bucket domain start...\n");
    $resp = $OSSClient->getBucketDomain(['Bucket' => $bucketName]);
    printf("get bucket domain start...\n",$resp['Domain']);;

    printf("HttpStatusCode:%s\n",$resp ['HttpStatusCode']);;
}
function doBucketLifecycleOperation()
{
    global $OSSClient;
    global $bucketName;

    $ruleId0 = "delete OSSoleted files";
    $matchPrefix0 = "OSSoleted/";
    $ruleId1 = "delete temporary files";
    $matchPrefix1 = "temporary/";
    $ruleId2 = "delete temp files";
    $matchPrefix2 = "temp/";

    printf ("Setting bucket lifecycle\n\n");

    $OSSClient->setBucketLifecycleConfiguration ( [
        'Bucket' => $bucketName,
        'Rules' => [
            [
                'ID' => $ruleId0,
                'Prefix' => $matchPrefix0,
                'Status' => 'Enabled',
                'Expiration'=> ['Days'=>5]
            ],
            [
                'ID' => $ruleId1,
                'Prefix' => $matchPrefix1,
                'Status' => 'Enabled',
                'Expiration' => ['Date' => '2017-12-31T00:00:00Z']
            ],
            [
                'ID' => $ruleId2,
                'Prefix' => $matchPrefix2,
                'Status' => 'Enabled',
                'NoncurrentVersionExpiration' => ['NoncurrentDays' => 10]
            ]
        ]
    ]);

    printf ("Getting bucket lifecycle\n\n");

    $resp = $OSSClient->getBucketLifecycleConfiguration ([
        'Bucket' => $bucketName
    ]);

    $i = 0;
    foreach ( $resp ['Rules'] as $rule ) {
        printf ( "\tRules[$i][Expiration][Date]:%s,Rules[$i][Expiration][Days]:%d\n", $rule ['Expiration'] ['Date'], $rule ['Expiration'] ['Days'] );
        printf ( "\yRules[$i][NoncurrentVersionExpiration][NoncurrentDays]:%s\n", $rule ['NoncurrentVersionExpiration'] ['NoncurrentDays'] );
        printf ( "\tRules[$i][ID]:%s,Rules[$i][Prefix]:%s,Rules[$i][Status]:%s\n", $rule ['ID'], $rule ['Prefix'], $rule ['Status'] );
        $i ++;
    }

    printf ("Deleting bucket lifecycle\n\n");
    $OSSClient->deleteBucketLifecycleConfiguration (['Bucket' => $bucketName]);
}

function doBucketLoggingOperation($ownerId)
{
    global $OSSClient;
    global $bucketName;

    printf ("Setting bucket ACL, give the log-delivery group " . OSSClient::PermissionWrite ." and " .OSSClient::PermissionReadAcp ." permissions\n\n");

    $OSSClient->setBucketAcl ([
        'Bucket' => $bucketName,
        'Owner' => [
            'ID' => $ownerId
        ],
        'Grants' => [
            [
                'Grantee' => [
                    'URI' => OSSClient::GroupLogDelivery,
                    'Type' => 'Group'
                ],
                'Permission' => OSSClient::PermissionWrite
            ],
            [
                'Grantee' => [
                    'URI' => OSSClient::GroupLogDelivery,
                    'Type' => 'Group'
                ],
                'Permission' => OSSClient::PermissionReadAcp
            ],
        ]
    ]);

    printf ("Setting bucket logging\n\n");

    $targetBucket = $bucketName;
    $targetPrefix = 'log-';

    $OSSClient->setBucketLoggingConfiguration ( [
        'Bucket' => $bucketName,
        'LoggingEnabled' => [
            'TargetBucket' => $targetBucket,
            'TargetPrefix' => $targetPrefix,
            'TargetGrants' => [
                [
                    'Grantee' => [
                        'URI' => OSSClient::GroupAuthenticatedUsers,
                        'Type' => 'Group'
                    ],
                    'Permission' => OSSClient::PermissionRead
                ]
            ]
        ]
    ]);

    printf ("Getting bucket logging\n");

    $resp = $OSSClient->getBucketLoggingConfiguration ([
        'Bucket' => $bucketName
    ]);

    printf ("\tTarget bucket=%s, target prefix=%s\n", $resp ['LoggingEnabled'] ['TargetBucket'], $resp ['LoggingEnabled'] ['TargetPrefix'] );
    printf("\tTargetGrants=%s\n\n", json_encode($resp ['LoggingEnabled'] ['TargetGrants']));

    printf ("Deletting bucket logging\n");

    $OSSClient->setBucketLoggingConfiguration ( [
        'Bucket' => $bucketName
    ]);
}

function doBucketWebsiteOperation()
{
    global $OSSClient;
    global $bucketName;

    printf ("Setting bucket website\n\n");

    $OSSClient->setBucketWebsiteConfiguration ([
        'Bucket' => $bucketName,
        'IndexDocument' => [
            'Suffix' => 'index.html'
        ],
        'ErrorDocument' => [
            'Key' => 'error.html'
        ]
    ]);
    printf ("Getting bucket website\n");

    $resp = $OSSClient->GetBucketWebsiteConfiguration ( [
        'Bucket' => $bucketName
    ]);

    printf ("\tIndex document=%s, error document=%s\n\n", $resp ['IndexDocument'] ['Suffix'], $resp ['ErrorDocument'] ['Key']);
    printf ("Deletting bucket website\n");

    $OSSClient->deleteBucketWebsiteConfiguration ([
        'Bucket' => $bucketName
    ]);
}

function doBucketTaggingOperation()
{
    global $OSSClient;
    global $bucketName;
    printf ("Setting bucket tagging\n\n");
    $OSSClient -> setBucketTagging([
        'Bucket' => $bucketName,
        'TagSet' => [
            [
                'Key' => 'testKey1',
                'Value' => 'testValue1'
            ],
            [
                'Key' => 'testKey2',
                'Value' => 'testValue2'
            ]
        ]
    ]);
    printf ("Getting bucket tagging\n");

    $resp = $OSSClient -> getBucketTagging(['Bucket' => $bucketName]);

    printf ("\t%s\n\n", json_encode($resp->toArray()));

    printf ("Deletting bucket tagging\n\n");

    $OSSClient -> deleteBucketTagging(['Bucket' => $bucketName]);
}

function deleteBucket()
{

    global $OSSClient;
    global $bucketName;

    $resp = $OSSClient->deleteBucket ([
        'Bucket' => $bucketName
    ] );
    printf("Deleting bucket %s successfully!\n\n", $bucketName);
    printf("HttpStatusCode:%s\n\n", $resp ['HttpStatusCode']);
}


