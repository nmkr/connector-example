<?php
/**
 *
 * @copyright 2010-2015 JTL-Software GmbH
 * @package Jtl\Connector\Example
 */
namespace Jtl\Connector\Example;

use jtl\Connector\Base\Connector as BaseConnector;
use jtl\Connector\Core\Rpc\Method;
use jtl\Connector\Core\Rpc\RequestPacket;
use jtl\Connector\Core\Utilities\RpcMethod;
use jtl\Connector\Core\Controller\Controller as CoreController;
use Jtl\Connector\Example\Authentication\TokenLoader;
use Jtl\Connector\Example\Checksum\ChecksumLoader;
use Jtl\Connector\Example\Mapper\PrimaryKeyMapper;
use jtl\Connector\Result\Action;

/**
 * Example Connector
 *
 * @access public
 */
class Connector extends BaseConnector
{
    /**
     * Current Controller
     *
     * @var \jtl\Connector\Core\Controller\Controller
     */
    protected $controller;

    /**
     * @var string
     */
    protected $action;

    public function initialize()
    {
        $this->setPrimaryKeyMapper(new PrimaryKeyMapper())
            ->setTokenLoader(new TokenLoader())
            ->setChecksumLoader(new ChecksumLoader());
    }

    /**
     * (non-PHPdoc)
     *
     * @see \jtl\Connector\Application\IEndpointConnector::canHandle()
     */
    public function canHandle()
    {
        $controller = RpcMethod::buildController($this->getMethod()->getController());

        $class = "\\Jtl\\Connector\\Example\\Controller\\{$controller}";
        if (class_exists($class)) {
            $this->controller = $class::getInstance();
            $this->action = RpcMethod::buildAction($this->getMethod()->getAction());

            return is_callable(array($this->controller, $this->action));
        }

        return false;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \jtl\Connector\Application\IEndpointConnector::handle()
     */
    public function handle(RequestPacket $requestpacket)
    {
        // Set the method to our controller
        $this->controller->setMethod($this->getMethod());

        if ($this->action === Method::ACTION_PUSH || $this->action === Method::ACTION_DELETE) {
            /*
            if ($this->getMethod()->getController() === 'image') {
                return $this->controller->{$this->action}($requestpacket->getParams());
            }
            */

            if (!is_array($requestpacket->getParams())) {
                throw new \Exception("Expecting request array, invalid data given");
            }

            $action = new Action();
            $results = array();
            if ($this->action === Method::ACTION_PUSH && $this->getMethod()->getController() === 'product_price') {
                $params = $requestpacket->getParams();
                $result = $this->controller->update($params);
                $results[] = $result->getResult();
            }
            else {
                foreach ($requestpacket->getParams() as $param) {
                    $result = $this->controller->{$this->action}($param);
                    $results[] = $result->getResult();
                }
            }

            $action->setHandled(true)
                ->setResult($results)
                ->setError($result->getError());    // @todo: refactor to array of errors

            return $action;
        }
        else {
            return $this->controller->{$this->action}($requestpacket->getParams());
        }
    }

    /**
     * Getter Controller
     *
     * @return \jtl\Connector\Core\Controller\Controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Setter Controller
     *
     * @param \jtl\Connector\Core\Controller\Controller $controller
     */
    public function setController(CoreController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Getter Action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Setter Action
     *
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }
}
