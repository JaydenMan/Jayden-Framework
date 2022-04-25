<?php

/**
 * Rsa加密解密类
 * @author Ryanc [chaoma.me]
 */
class Rsa
{
    protected $privateKey;
    protected $publicKey;

    public function setKey($privateKey = '', $publicKey = '')
    {
        $this->privateKey = file_get_contents($privateKey);
        $this->publicKey = file_get_contents($publicKey);
    }

    /**
     *  私钥加密
     * @param $data
     * @return string
     * @throws Exception
     */
    public function privateEncrypt($data)
    {
        $encrypted = '';
        $this->_needKey(2);
        $private_key = openssl_pkey_get_private($this->privateKey);
        $fstr = array();
        $array_data = $this->_splitEncode($data); //把要加密的信息 base64 encode后 等长放入数组
        foreach ($array_data as $value) {//理论上是可以只加密数组中的第一个元素 其他的不加密 因为只要一个解密不出来 整体也就解密不出来 这里先全部加密
            openssl_private_encrypt($value, $encrypted, $private_key); //私钥加密
            $fstr[] = $encrypted; //对数组中每个加密
        }
        return base64_encode(serialize($fstr)); //序列化后base64_encode
    }

    /**
     * 公钥加密
     * @param $data
     * @return string
     * @throws Exception
     */
    public function publicEncrypt($data)
    {
        $encrypted = '';
        $this->_needKey(1);
        $public_key = openssl_pkey_get_public($this->publicKey);
        $fstr = array();
        $array_data = $this->_splitEncode($data);
        foreach ($array_data as $value) {
            openssl_public_encrypt($value, $encrypted, $public_key); //私钥加密
            $fstr[] = $encrypted;
        }
        return base64_encode(serialize($fstr));
    }

    /**
     * 用私钥解密公钥加密内容
     * @param $data
     * @return bool|string
     * @throws Exception
     */
    public function privateDecode($data)
    {
        $decrypted = '';
        $this->_needKey(2);
        $private_key = openssl_pkey_get_private($this->privateKey);
        $array_data = $this->_toArray($data);
        $str = '';
        foreach ($array_data as $value) {
            openssl_private_decrypt($value, $decrypted, $private_key); //私钥解密
            $str .= $decrypted;
        }
        return base64_decode($str);
    }

    /**
     * 用公钥解密私钥加密内容
     * @param $data
     * @return bool|string
     * @throws Exception
     */
    public function publicDecrypt($data)
    {
        $decrypted = '';
        $this->_needKey(1);
        $public_key = openssl_pkey_get_public($this->publicKey);
        $array_data = $this->_toArray($data); //数据base64_decode 后 反序列化成数组
        $str = '';
        foreach ($array_data as $value) {
            openssl_public_decrypt($value, $decrypted, $public_key); //私钥加密的内容通过公钥可用解密出来
            $str .= $decrypted; //对数组中的每个元素解密 并拼接
        }
        return base64_decode($str); //把拼接的数据base64_decode 解密还原
    }

    /**
     * 检查是否 含有所需配置文件
     * @param int 1 公钥 2 私钥
     * @return int 1
     * @throws Exception
     */
    private function _needKey($type)
    {
        switch ($type) {
            case 1:
                if (empty($this->publicKey)) {
                    throw new \Exception('请配置公钥');
                }
                break;
            case 2:
                if (empty($this->privateKey)) {
                    throw new \Exception('请配置私钥');
                }
                break;
        }
        return 1;
    }

    /**
     * @param $data
     * @return array
     */
    private function _splitEncode($data)
    {
        $data = base64_encode($data); //加上base_64 encode  便于用于 分组
        $total_lenth = strlen($data);
        $per = 96; // 能整除2 和 3 RSA每次加密不能超过100个
        $dy = $total_lenth % $per;
        $total_block = $dy ? ($total_lenth / $per) : ($total_lenth / $per - 1);
        $total_block = intval($total_block + 1);
        for ($i = 0; $i < $total_block; $i++) {
            $return[] = substr($data, $i * $per, $per); //把要加密的信息base64 后 按64长分组
        }
        return $return;
    }

    /**
     * 公钥加密并用 base64 serialize 过的 data
     * @param $data
     * @return mixed
     * @throws Exception
     */
    private function _toArray($data)
    {
        $data = base64_decode($data);
        $array_data = unserialize($data);
        if (!is_array($array_data)) {
            throw new \Exception('数据加密不符');
        }
        return $array_data;
    }

}
