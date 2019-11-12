<?php

namespace Bearer;

class Curl
{
    private $curlHandle;


    public function init()
    {
        if (is_null($this->curlHandle)) {
            $this->curlHandle = curl_init();
        } else {
            curl_reset($this->curlHandle);
        }
    }

    public function setOpt($option, $value)
    {
        return curl_setopt($this->curlHandle, $option, $value);
    }

    public function setOptArray(array $options)
    {
        return curl_setopt_array($this->curlHandle, $options);
    }

    public function exec()
    {
        return curl_exec($this->curlHandle);
    }

    public function close()
    {
        if (!is_null($this->curlHandle)) {
            return curl_close($this->curlHandle);
        }
    }

    public function errno()
    {
        return curl_errno($this->curlHandle);
    }

    public function getinfo($opt = null) {
        if (!is_null($opt)) {
            return curl_getinfo($this->curlHandle, $opt);
        }
        return curl_getinfo($this->curlHandle);
    }
}
