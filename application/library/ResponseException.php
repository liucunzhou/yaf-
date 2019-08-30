<?php
class ResponseException extends Exception
{
    public function getErrorMessage()
    {
        $msg = $this->getMessage();
        $code = $this->getCode();
        echo $this->jsonResponseFormat($msg, $code);
        die();
    }

    public function jsonResponseFormat(string $msg, string $code)
    {
        header('Content-type: application/json');
        header('Access-Control-Allow-Headers: x-requested-with,content-type');
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST,GET');
        header('Access-Control-Allow-Credentials:true');

        $result = [
            'code'   => $code,
            'msg'    => $msg
        ];
        
        return json_encode($result);
    }
}
