<?php
/**
 * @copyright 2010-2013 JTL-Software GmbH
 * @package Jtl\Connector\Example\Controller
 */

namespace Jtl\Connector\Example\Controller;

use Jtl\Connector\Core\Model\ConnectorIdentification;
use Jtl\Connector\Core\Model\ConnectorServerInfo;
use Jtl\Connector\Core\Result\Action;

class Connector extends DataController
{
    /**
     * Identify
     *
     * @return Action
     */
    public function identify()
    {
        $action = new Action();
        
        $returnMegaBytes = function ($data) {
            $data = trim($data);
            $len = strlen($data);
            $value = substr($data, 0, $len - 1);
            $unit = strtolower(substr($data, $len - 1));
            switch ($unit) {
                case 'g':
                    $value *= 1024;
                    break;
                case 'k':
                    $value /= 1024;
                    break;
            }
            
            return (int)round($value);
        };
        
        $serverInfo = new ConnectorServerInfo();
        $serverInfo->setMemoryLimit($returnMegaBytes(ini_get('memory_limit')))
            ->setExecutionTime((int)ini_get('max_execution_time'))
            ->setPostMaxSize($returnMegaBytes(ini_get('post_max_size')))
            ->setUploadMaxFilesize($returnMegaBytes(ini_get('upload_max_filesize')));
        
        $identification = new ConnectorIdentification();
        $identification->setEndpointVersion('1.0.0')
            //Bulk platform is the license for third party connectors
            ->setPlatformName('Bulk')
            ->setProtocolVersion($this->application->getProtocolVersion())
            ->setServerInfo($serverInfo);
        
        $action->setResult($identification);
        
        return $action;
    }
}
