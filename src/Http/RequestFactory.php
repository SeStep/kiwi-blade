<?php

namespace KiwiBlade\Http;


class RequestFactory
{
    const NICE_URL_QUERY_FIELD = 'q';

    /** @var bool */
    private $niceUrl;
    /** @var string */
    private $wwwSubfolder;
    /** @var string */
    private $defaultController;

    /**
     * RequestFactory constructor.
     * @param boolean $niceUrl
     * @param string $wwwSubfolder
     * @param string $defaultController
     */
    public function __construct($niceUrl, $wwwSubfolder, $defaultController)
    {
        $this->niceUrl = (boolean)$niceUrl;
        $this->wwwSubfolder = $wwwSubfolder;
        $this->defaultController = $defaultController;
    }

    /** @return Request */
    public function create()
    {
        $protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) ? "https" : "http";
        $rootUrl = $baseUrl = $protocol . "://$_SERVER[SERVER_NAME]/";

        if ($this->wwwSubfolder) {
            $baseUrl .= "$this->wwwSubfolder/";
        }

        $request = new Request([
            INPUT_POST => $_POST,
            INPUT_GET => $_GET,
        ], $baseUrl, $rootUrl);

        if ($this->niceUrl) {
            $input = UrlHelper::parseNiceString(filter_input(INPUT_GET, self::NICE_URL_QUERY_FIELD));
            $controller = $input['controller'];
            $action = $input['action'];
        } else {
            $controller = filter_input(INPUT_GET, 'controller') ?: '';
            $action = filter_input(INPUT_GET, 'action') ?: '';
        }
        $request->setController($controller ?: $this->defaultController);
        $request->setAction($action);


        return $request;
    }
}
