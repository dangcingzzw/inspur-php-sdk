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

namespace OSS\Internal\Process;
use OSS\OSSException;

class RequestResource{

    public  function request($domain,$path, $query = [], $body = [], $method = 'GET', $timeout = 15)
    {
        $method = strtoupper($method);
        $apiGateway = rtrim($domain, '/') . '/' . ltrim(
                $path,
                '/'
            ) . ($query ? '?' . http_build_query($query) : '');
        $timestamp = $this->getMillisecond();
        $params = ["path_url" => $path];
        if ($query) {
            foreach ($query as $k => $v) {
                $params["query_{$k}"] = $v;
                if (is_array($v)) {
                    foreach ($v as $ks => $va) {
                        $params["query_{$k}"]["query_{$ks}"] = $va;
                        unset($params["query_{$k}"][$ks]);
                    }
                }
            }
        }
        if ($body) {
            foreach ($body as $k => $v) {
                $params["body_{$k}"] = $v;
                if (is_array($v)) {
                    foreach ($v as $ks => $va) {
                        $params["body_{$k}"]["body_{$ks}"] = $va;
                        unset($params["body_{$k}"][$ks]);
                    }
                }
            }
        }
        $this->SortAll($params);
        $hexHash = hash("sha256", "{$timestamp}");
        if (count($params) > 0) {
            // 序列化且不编码
            $s = json_encode($params, JSON_UNESCAPED_UNICODE);
            $hexHash = hash("sha256", stripcslashes($s . "{$timestamp}"));
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiGateway);
        $header = [ ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $jsonStr = $body ? json_encode($body) : ''; //转换为json格式
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($jsonStr) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            }
        } elseif ($method == 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if ($jsonStr) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            }
        } elseif ($method == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($jsonStr) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            }
        } elseif ($method == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            if ($jsonStr) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            }
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //表示不检查证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); //信任任何证书
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);

        return $response;
    }

    public function SortAll(&$params)
    {
        if (is_array($params)) {
            ksort($params);
        }
        foreach ($params as &$v) {
            if (is_array($v)) {
                $this->SortAll($v);
            }
        }
    }
    private function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)));
    }

}