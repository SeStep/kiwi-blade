<?php

namespace KiwiBlade\Http;


class RequestFactory
{
    /**
     * @param boolean $niceUrl
     * @param string  $wwwSubfolder
     * @param string  $defaultController
     * @return Request
     * */
    public function create($niceUrl, $wwwSubfolder, $defaultController)
    {
        $protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) ? "https" : "http";
        $rootUrl = $baseUrl = $protocol . "://$_SERVER[SERVER_NAME]";

        if ($wwwSubfolder) {
            $baseUrl .= $wwwSubfolder;
        }

        $input = [
            INPUT_POST => $_POST,
            INPUT_GET => $_GET,
        ];

        if ($niceUrl) {
            $target = UrlHelper::parseNiceString($this->validateUri($_SERVER['REQUEST_URI'], $wwwSubfolder));
            $controller = $target[UrlHelper::CONTROLLER];
            $action = $target[UrlHelper::ACTION];
        } else {
            $controller = filter_input(INPUT_GET, 'controller') ?: '';
            $action = filter_input(INPUT_GET, 'action') ?: '';
        }

        return new Request($input, $controller ?: $defaultController, $action, $baseUrl, $rootUrl);
    }

    private function validateUri($requestUri, $wwwSubfolder)
    {
        $subfolderLen = strlen($wwwSubfolder);
        $uri = substr($requestUri, 0, $subfolderLen);
        if(strcmp($uri, $wwwSubfolder)){
            throw new \InvalidArgumentException("Invallid wwwSubfolder value. $wwwSubfolder expected, got $uri");
        }

        $uriRest = substr($requestUri, $subfolderLen);
        return $uriRest;
    }
}
