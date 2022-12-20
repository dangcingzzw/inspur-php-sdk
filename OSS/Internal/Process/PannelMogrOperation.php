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

class PannelMogrOperation{
    public static function main($params) {

        $fileName=$params['body']['file'];
        $instructions=$params['body']['instructions'];

        $arr=[];
        foreach($instructions as $key=>$v){
            $item='';
            switch($key){
                case 'rotate':
                    $item= '/'.$key.','.$v['value'];
                    break;
                case 'flip':
                    $item= '/'.$key.','.$v['value'];
                    break;
                case 'resize':
                    if($v['type']=='rate'){
                        $item= '/resize'.','.'p_'.$instruction['value'];
                    }else{
                        $item= '/resize'.','.
                            'm_'.$v['model'].','.
                            'w_'.$v['width'].','.
                            'h_'.$v['height'].','.
                            'limit_'.$v['limit'];
                    }
                    break;
                case 'indexcrop':
                    $item= '/indexcrop'.','.$v['type'].'_'.$v['value'].',i_'. $v['saveArea']??0;;
                    break;
                case 'circle':
                    $item= '/'.$key.','.'r_'.$v['radius'];
                    break;
                case 'rounded-corners':
                    $item= '/'.$key.','.'r_'.$v['radius'];
                    break;
                case 'format-conversion':
                    $item= '/'.'format'.','.$v['type'];
                    break;
                default:
                    break;
            }

           if($item!=''){
               array_push($arr,$item);
           }
        }
       $res=implode('',$arr);
        return $fileName.'?x-oss-process=image'.$res;
    }
}