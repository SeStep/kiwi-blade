<?php

namespace KiwiBlade\Traits;


trait JsonSending
{
    public function sendJson($data = [], $status = '')
    {
        if ($status) {
            $data = array_merge(['status' => $status], $data);
        }
        echo json_encode($data);
        exit;
    }

    public function sendJsonSuccess($data)
    {
        $this->sendJson($data, 'success');
    }

    public function sendJsonError($message, $data = [])
    {
        $data = array_merge(['message' => $message], $data);
        $this->sendJson($data, 'error');
    }
}
