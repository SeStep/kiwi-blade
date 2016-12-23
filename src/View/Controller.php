<?php

namespace KiwiBlade\View;

use KiwiBlade\Core\Alerter;
use KiwiBlade\DI\Container;
use KiwiBlade\Forms\FormFactory;
use KiwiBlade\Http\LinkGenerator;
use KiwiBlade\Http\Request;
use KiwiBlade\Mail\MailService;
use KiwiBlade\Security\IUser;
use KiwiBlade\Traits\AlertLogging;
use KiwiBlade\Traits\JsonSending;

/**
 * Description of Controler
 */
abstract class Controller implements IController
{
    use JsonSending;
    use AlertLogging;

    /** @var mixed[] */
    protected $template = [];
    /** @var string */
    protected $layout;
    /** @var Container */
    protected $context;
    /** @var Request */
    protected $request;
    /** @var FormFactory */
    protected $formFactory;
    /** @var MailService */
    protected $mailService;
    /** @var IUser */
    protected $user;
    /** @var LinkGenerator */
    private $linkGenerator;
    /** @var boolean */
    private $startupCalled = false;

    /**
     * Controller constructor.
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->context = $container;

        $this->linkGenerator = $container->get(LinkGenerator::class);
        $this->request = $container->get(Request::class);
        $this->formFactory = new FormFactory();
        $this->alerter = Alerter::getInstance(__CLASS__);
        $this->mailService = $container->get(MailService::class);

        $this->layout = "layout.twig";
    }

    public function startUp()
    {
        $this->addCss("default.css");
        $this->user = $this->context->get(IUser::class);
        $this->startupCalled = true;
    }

    public final function isStartedUp()
    {
        return $this->startupCalled;
    }

    public function getDefaultAction()
    {
        return 'default';
    }

    protected function getParam($name, $input = INPUT_GET)
    {
        return $this->request->getParam($name, $input);
    }

    public function addCss($css)
    {
        $this->template['css'][] = $css;
    }

    public function addJs($js)
    {
        foreach ($this->template['js'] as $scr) {
            if ($scr === $js) {
                return;
            }
        }
        $this->template['js'][] = $js;
    }

    public function link($target, $parameters = [])
    {
        return $this->linkGenerator->link($target, $parameters);
    }

    public function redirect($target, $parameters = [])
    {
        $this->redirectUrl($this->link($target, $parameters));
    }

    public function redirectUrl($location)
    {
        \header("Location: $location");
        \header("Connection: close");
        die;
    }

    public function beforeRender()
    {
        $log = $this->getAlertLog();
        $this->template['alert_messages'] = $log;
    }

    /**
     * Returns name of layout to be used for current view.
     * @return string
     * @internal
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Gets current template variables.
     * @return mixed[]
     * @internal
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
