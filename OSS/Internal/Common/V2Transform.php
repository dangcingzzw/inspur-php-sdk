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

namespace OSS\Internal\Common;

use OSS\OSSClient;
use OSS\Internal\Resource\V2Constants;

class V2Transform implements ITransform{
    private static $instance;
    
    private function __construct(){}
    
    public static function getInstance() {
        if (!(self::$instance instanceof V2Transform)) {
            self::$instance = new V2Transform();
        }
        return self::$instance;
    }
     
    public function transform($sign, $para) {
        if ($sign === 'storageClass') {
            $para = $this->transStorageClass($para);
        } else if ($sign === 'aclHeader') {
            $para = $this->transAclHeader($para);
        } else if ($sign === 'aclUri') {
            $para = $this->transAclGroupUri($para);
        } else if ($sign == 'event') {
            $para = $this->transNotificationEvent($para);
        }
        return $para;
    }
    
    private function transStorageClass($para) {
        $search = array(OSSClient::StorageClassStandard, OSSClient::StorageClassWarm, OSSClient::StorageClassCold);
        $repalce = array('STANDARD', 'STANDARD_IA', 'GLACIER');
        $para = str_replace($search, $repalce, $para);
        return $para;
    }
    
    private function transAclHeader($para) {
        if ($para === OSSClient::AclPublicReadDelivered || $para === OSSClient::AclPublicReadWriteDelivered) {
            $para = null;
        }
        return $para;                    
    }
    
    private function transAclGroupUri($para) {
        if ($para === OSSClient::GroupAllUsers) {
            $para = V2Constants::GROUP_ALL_USERS_PREFIX . $para;
        } else if ($para === OSSClient::GroupAuthenticatedUsers) {
            $para = V2Constants::GROUP_AUTHENTICATED_USERS_PREFIX . $para; 
        } else if ($para === OSSClient::GroupLogDelivery) {
            $para = V2Constants::GROUP_LOG_DELIVERY_PREFIX . $para;
        } else if ($para === OSSClient::AllUsers) {
            $para = V2Constants::GROUP_ALL_USERS_PREFIX . OSSClient::GroupAllUsers;
        }
        return $para;
    }
    
    private function transNotificationEvent($para) {
        $pos = strpos($para, 's3:');
        if ($pos === false || $pos !== 0) {
            $para = 's3:' . $para;
        }
        return $para;
    }
}


