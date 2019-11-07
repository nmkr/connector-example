<?php
/**
 * @copyright 2010-2013 JTL-Software GmbH
 * @package Jtl\Connector\Example\Controller
 */

namespace Jtl\Connector\Example\Controller;

use Jtl\Connector\Core\Application\Application;
use Jtl\Connector\Core\Controller\AbstractController;

abstract class DataController extends AbstractController
{
    protected $db;
    
    public function __construct(Application $application, \PDO $pdo)
    {
        parent::__construct($application);
        
        $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, FALSE);
        $this->db = $pdo;
    }
    
    public function query(string $query, array $params = []): array
    {
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
    
        if ($result = $stmt->fetchAll(\PDO::FETCH_ASSOC)) {
            return $result;
        }
        
        return [];
    }
}
