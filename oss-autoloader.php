<?php


$mapping = [
	'OSS\Internal\Common\CheckoutStream' => __DIR__.'/OSS/Internal/Common/CheckoutStream.php',
	'OSS\Internal\Common\ITransform' => __DIR__.'/OSS/Internal/Common/ITransform.php',
	'OSS\Internal\Common\Model' => __DIR__.'/OSS/Internal/Common/Model.php',
	'OSS\Internal\Common\OSSTransform' => __DIR__.'/OSS/Internal/Common/OSSTransform.php',
	'OSS\Internal\Common\SchemaFormatter' => __DIR__.'/OSS/Internal/Common/SchemaFormatter.php',
	'OSS\Internal\Common\SdkCurlFactory' => __DIR__.'/OSS/Internal/Common/SdkCurlFactory.php',
	'OSS\Internal\Common\SdkStreamHandler' => __DIR__.'/OSS/Internal/Common/SdkStreamHandler.php',
	'OSS\Internal\Common\ToArrayInterface' => __DIR__.'/OSS/Internal/Common/ToArrayInterface.php',
	'OSS\Internal\Common\V2Transform' => __DIR__.'/OSS/Internal/Common/V2Transform.php',
	'OSS\Internal\GetResponseTrait' => __DIR__.'/OSS/Internal/GetResponseTrait.php',
	'OSS\Internal\Resource\Constants' => __DIR__.'/OSS/Internal/Resource/Constants.php',
	'OSS\Internal\Resource\OSSConstants' => __DIR__.'/OSS/Internal/Resource/OSSConstants.php',
	'OSS\Internal\Resource\OSSRequestResource' => __DIR__.'/OSS/Internal/Resource/OSSRequestResource.php',
	'OSS\Internal\Resource\V2Constants' => __DIR__.'/OSS/Internal/Resource/V2Constants.php',
	'OSS\Internal\Resource\V2RequestResource' => __DIR__.'/OSS/Internal/Resource/V2RequestResource.php',
	'OSS\Internal\SendRequestTrait' => __DIR__.'/OSS/Internal/SendRequestTrait.php',
	'OSS\Internal\Signature\AbstractSignature' => __DIR__.'/OSS/Internal/Signature/AbstractSignature.php',
	'OSS\Internal\Signature\DefaultSignature' => __DIR__.'/OSS/Internal/Signature/DefaultSignature.php',
	'OSS\Internal\Signature\SignatureInterface' => __DIR__.'/OSS/Internal/Signature/SignatureInterface.php',
	'OSS\Internal\Signature\V4Signature' => __DIR__.'/OSS/Internal/Signature/V4Signature.php',
	'OSS\Log\OSSConfig' => __DIR__.'/OSS/Log/OSSConfig.php',
	'OSS\Log\OSSLog' => __DIR__.'/OSS/Log/OSSLog.php',
	'OSS\OSSClient' => __DIR__.'/OSS/OSSClient.php',
	'OSS\OSSException' => __DIR__.'/OSS/OSSException.php',
];


spl_autoload_register(function ($class) use ($mapping) {
    if (isset($mapping[$class])) {
        require $mapping[$class];
    }
}, true);
