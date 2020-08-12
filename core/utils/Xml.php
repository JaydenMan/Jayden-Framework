<?php

namespace module\utils;

use SimpleXMLElement;

class Xml
{

    public static function xmlToArray($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xml_string = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xml_string), true);
        return $val;
    }

    public static function array2xml($arr, $level = 1)
    {
        $s = $level == 1 ? "<xml>" : '';
        foreach ($arr as $tagname => $value) {
            if (is_numeric($tagname)) {
                $tagname = $value['TagName'];
                unset($value['TagName']);
            }
            if (!is_array($value)) {
                $s .= "<{$tagname}>" . (!is_numeric($value) ? '<![CDATA[' : '') . $value . (!is_numeric($value) ? ']]>' : '') . "</{$tagname}>";
            } else {
                $s .= "<{$tagname}>" . static::array2xml($value, $level + 1) . "</{$tagname}>";
            }
        }
        $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
        return $level == 1 ? $s . "</xml>" : $s;
    }

    public static function xml2array($xml)
    {
        if (empty($xml)) {
            return array();
        }
        $result = array();
        $xml_obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xml_obj instanceof SimpleXMLElement) {
            $result = json_decode(json_encode($xml_obj), true);
            if (is_array($result)) {
                return $result;
            } else {
                return '';
            }
        } else {
            return $result;
        }
    }
}