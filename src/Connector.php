<?php

namespace Jtl\Connector\Example;


use Jtl\Connector\Core\Application\Application;
use Jtl\Connector\Core\Authentication\ITokenValidator;
use Jtl\Connector\Core\Connector\CoreConnector;
use Jtl\Connector\Core\Mapper\IPrimaryKeyMapper;
use Jtl\Connector\Core\Result\Action;
use Jtl\Connector\Core\Rpc\Method;
use Jtl\Connector\Core\Rpc\RequestPacket;
use Jtl\Connector\Core\Utilities\RpcMethod;
use Jtl\Connector\Example\Mapper\PrimaryKeyMapper;

class Connector extends CoreConnector
{
    /**
     * @var \PDO
     */
    protected $db;
    
    /**
     * @var string
     */
    protected $controllerNamespace = 'Jtl\\Connector\\Example\\Controller';
    
    public function __construct(PrimaryKeyMapper $primaryKeyMapper, ITokenValidator $tokenValidator)
    {
        $this->db = $primaryKeyMapper->getDb();
        parent::__construct($primaryKeyMapper, $tokenValidator);
    }
    
    public function handle(RequestPacket $requestPacket, Application $application): Action
    {
        $rpcMethod = RpcMethod::splitMethod($requestPacket->getMethod());
        
        $controllerName = sprintf('%s\%s', $this->getControllerNamespace(),
            RpcMethod::buildController($rpcMethod->getController()));
        $actionName = $rpcMethod->getAction();
        
        $controller = new $controllerName($application, $this->db);
        
        if (in_array($actionName, [Method::ACTION_DELETE, Method::ACTION_PUSH])) {
            $result = [];
            foreach ($requestPacket->getParams() as $model) {
                $result[] = $controller->{$actionName}($model);
            }
        } else {
            $result = $controller->{$actionName}($requestPacket->getParams());
        }
        
        if (!$result instanceof Action) {
            $result = (new Action())->setResult($result);
        }
        
        return $result;
    }
    
    
}
