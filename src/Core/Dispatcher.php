<?php

namespace KiwiBlade\Core;

use KiwiBlade\Bridges\Tracy\RequestPanel;
use KiwiBlade\DI\Container;
use KiwiBlade\Helpers\SessionHelper;
use KiwiBlade\Http\Request;
use KiwiBlade\View\Controller;
use KiwiBlade\View\ControllerFactory;
use KiwiBlade\View\IErrorController;
use ReflectionClass;
use Tracy\Debugger;
use Twig_Environment;

/**
 * Description of Dispatcher
 *
 * @author Stepan
 */
class Dispatcher
{
    const CONTROLLER_STARTUP = "startup";
    const CONTROLLER_ACTION = 'action';
    const CONTROLLER_BEFORE_RENDER = 'beforeRender';
    const CONTROLLER_RENDER = 'render';

    /** @var Container */
    private $container;

    /** @var RequestPanel */
    private $panel;
    /**
     * @var ControllerFactory
     */
    private $controllerFactory;


    /**
     * Dispatcher constructor.
     * @param Container $container
     * @param ControllerFactory $controllerFactory
     * @param array $args
     */
    public function __construct(Container $container, ControllerFactory $controllerFactory)
    {
        $this->container = $container;
        $this->controllerFactory = $controllerFactory;
    }

    /**
     * @param Request $request
     */
    public function dispatch($request)
    {
        SessionHelper::start();

        $contName = $request->getController();

        $cont = $this->controllerFactory->getControler($contName, $this->container);
        if (!$cont) {
            $this->error(IErrorController::NO_CONTROLLER_FOUND, $contName);

            return;
        }

        $defaultAction = $cont->getDefaultAction();
        $action = $request->getAction() ?: $defaultAction;

        if (class_exists(Debugger::class)) {
            Debugger::getBar()->addPanel($this->panel = new RequestPanel($contName, $action));
        }

        if (!$request->getAction()) {
            $cont->redirect("$contName:$defaultAction");
        }
        $contResponse = $this->getControllerResponse($cont, $action);

        if (sizeof($contResponse) < 3) {
            $this->error(IErrorController::NOT_RECOGNISED_ACTION, $contName, $action);

            return;
        }

        $this->run($contResponse, $cont, $contName, $action);
    }

    /**
     *
     * @param \ReflectionMethod[] $contResponse
     * @param Controller $cont
     * @param string $contName
     * @param string $action
     */
    private function run($contResponse, $cont, $contName, $action)
    {
        $contResponse[self::CONTROLLER_STARTUP]->invoke($cont);

        if (!$cont->isStartedUp()) {
            throw new \LogicException("Controller $contName does not call parent::startUp() method");
        }

        if (isset($contResponse['action'])) {
            $contResponse['action']->invoke($cont, null);
        }
        if (isset($contResponse[self::CONTROLLER_RENDER])) {
            $templatePath = $this->getTemplatePath($contName, $action);
            if (!$templatePath) {
                $this->error(IErrorController::NO_TEMPLATE, $contName, $action);

                return;
            }
            $contResponse[self::CONTROLLER_RENDER]->invoke($cont, null);
            $contResponse[self::CONTROLLER_BEFORE_RENDER]->invoke($cont, null);

            $this->render($templatePath, $cont->getTemplate(), $cont->getLayout());
        } else {
            $this->error(IErrorController::NO_RENDER_OR_REDIRECT, $contName, $action);
        }
    }

    /**
     * @param string $template
     * @param mixed[] $vars
     * @param $layout
     */
    private function render($template, $vars, $layout)
    {
        /** @var Twig_Environment $twig */
        $twig = $this->container->get(Twig_Environment::class);

        $vars['layout'] = $layout;

        echo $twig->render($template, $vars);
    }

    private function error($errType, $contName, $action = null)
    {
        /** @var IErrorController $errCont */
        $errCont = $this->controllerFactory->getErrorController($this->container);
        $errCont->startUp();
        $errCont->renderError($errType, $contName, $action);

        if ($this->panel) {
            $this->panel->setError(true);
        }

        $this->render("error/error.twig", $errCont->getTemplate(), $errCont->getLayout());
    }

    /**
     *
     * @param Controller $cont
     * @param string $action
     * @return \ReflectionMethod[]
     */
    private function getControllerResponse($cont, $action)
    {
        $contClass = new ReflectionClass($cont);
        $return = [
            self::CONTROLLER_STARTUP => $contClass->getMethod("startUp"),
            self::CONTROLLER_BEFORE_RENDER => $contClass->getMethod("beforeRender"),
        ];

        foreach ([self::CONTROLLER_ACTION, self::CONTROLLER_RENDER] as $mt) {
            $methodName = $mt . ucfirst(strtolower($action));
            if ($contClass->hasMethod($methodName)) {
                $method = $contClass->getMethod($methodName);
                $return[$mt] = $method;
            }
        }

        return $return;
    }

    private function getTemplatePath($controller, $action)
    {
        $filename = "$controller/$action.twig";
        /** @var Twig_Environment $twig */
        $twig = $this->container->get(Twig_Environment::class);
        if ($twig->load($filename)) {
            return $filename;
        }

        return false;
    }

}
