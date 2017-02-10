<?php

namespace KiwiBlade\Http;


class RequestFactory
{
    const NICE_URL_QUERY_FIELD = 'q';

    /**
     * @param boolean $niceUrl
     * @param string $wwwSubfolder
     * @param string $defaultController
     * @return Request
     * */
    public function create($niceUrl, $wwwSubfolder, $defaultController)
    {
        $protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) ? "https" : "http";
        $rootUrl = $baseUrl = $protocol . "://$_SERVER[SERVER_NAME]/";

        if ($wwwSubfolder) {
            $baseUrl .= $wwwSubfolder;
        }

        $input = [
            INPUT_POST => $_POST,
            INPUT_GET => $_GET,
        ];


        if ($niceUrl) {
            $input = UrlHelper::parseNiceString(filter_input(INPUT_GET, self::NICE_URL_QUERY_FIELD));
            $controller = $input['controller'];
            $action = $input['action'];
        } else {
            $controller = filter_input(INPUT_GET, 'controller') ?: '';
            $action = filter_input(INPUT_GET, 'action') ?: '';
        }

        return new Request($input, $controller ?: $defaultController, $action, $baseUrl, $rootUrl);
    }
}
