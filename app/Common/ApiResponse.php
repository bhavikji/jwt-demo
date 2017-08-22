<?php

namespace App\Common;

use Illuminate\Support\Facades\Log;

trait ApiResponse
{
    protected $response = array();
    protected $meta = array();
    protected $pagination = array();
    protected $data = array();

    protected function setMeta($message = "")
    {
        $this->meta['message'] = $message;
    }

    protected function setPagination($value)
    {
        $this->pagination = $value;
    }

    protected function setData($key, $value)
    {
        /*array_walk_recursive($value, function (&$item, $key) {
            $item = null === $item ? '' : $item;
        });*/
        $this->data[$key] = $value;
        return $this->data;
    }

    protected function setDataWithoutKey($value)
    {
        $this->data = $value;
    }

    protected function setResponse()
    {
        $this->response['meta'] = $this->meta;
        if (count($this->data) > 0) {
            $this->response['data'] = $this->data;
        }
        if (count($this->pagination) > 0) {
            $this->response['pagination'] = $this->pagination;
        }
        return $this->response;
    }

    protected function setBlankResponse()
    {
        $this->response['meta'] = $this->meta;
        $this->response['data'] = $this->data;
        if (count($this->pagination) > 0) {
            $this->response['pagination'] = $this->pagination;
        }
        return $this->response;
    }

    protected function makeHidden()
    {
        return array('created_at', 'updated_at', 'status');
    }

    protected function whereStatus()
    {
        return array('status' => 1);
    }


    protected function setValue($value)
    {
        if($value==null) {
            return "";
        }
        return $value;
    }
    public function removeEmoji($text) {

        $clean_text = "";

        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, '', $text);

        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);

        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);

        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);

        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);

        return $clean_text;
    }

}