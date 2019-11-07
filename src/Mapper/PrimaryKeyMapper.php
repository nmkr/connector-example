<?php
/**
 * @copyright 2010-2013 JTL-Software GmbH
 * @package Jtl\Connector\Example\Mapper
 */

namespace Jtl\Connector\Example\Mapper;

use Jtl\Connector\Core\IO\Path;
use Jtl\Connector\Core\Mapper\IPrimaryKeyMapper;

class PrimaryKeyMapper implements IPrimaryKeyMapper
{
    /* @var \PDO */
    protected $db;
    
    public function __construct()
    {
        $pdo = new \PDO(sprintf('sqlite:%s', Path::combine(CONNECTOR_DIR, 'db', 'connector.s3db')));
        
        $this->db = $pdo;
    }
    
    /**
     * Host ID getter
     *
     * @param string $endpointId
     * @param integer $type
     * @return integer|null
     */
    public function getHostId(int $type, string $endpointId): ?int
    {
        $stmt = $this->db->prepare('SELECT host FROM mapping WHERE endpoint = ? AND type = ?');
        $stmt->execute([$endpointId, $type]);
        
        if ($result = $stmt->fetch()) {
            return $result[0];
        }
        
        return null;
    }
    
    /**
     * Endpoint ID getter
     *
     * @param integer $hostId
     * @param integer $type
     * @param string $relationType
     * @return string|null
     */
    public function getEndpointId(int $type, int $hostId, string $relationType = null): ?string
    {
        $stmt = $this->db->prepare('SELECT endpoint FROM mapping WHERE host = ? AND type = ?');
        $stmt->execute([$hostId, $type]);
        if ($result = $stmt->fetch()) {
            return $result[0];
        }
    
        return null;
    }
    
    /**
     * Save link to database
     *
     * @param string $endpointId
     * @param integer $hostId
     * @param integer $type
     * @return boolean
     */
    public function save(int $type, string $endpointId, int $hostId): bool
    {
        $stmt = $this->db->prepare('INSERT INTO mapping (endpoint, host, type) VALUES (?, ?, ?)');
        
        return $stmt->execute([$endpointId, $hostId, $type]);
    }
    
    /**
     * Delete link from database
     *
     * @param string $endpointId
     * @param integer $hostId
     * @param integer $type
     * @return boolean
     */
    public function delete(int $type, string $endpointId = null, int $hostId = null): bool
    {
        $where = '';
        $values = [];
        
        if ($endpointId !== null && $hostId !== null) {
            $where = sprintf('WHERE endpoint = %s AND host = %s AND type = %s', $endpointId, $hostId, $type);
            $values = [$endpointId, $hostId, $type];
        } elseif ($endpointId !== null) {
            $where = sprintf('WHERE endpoint = %s AND type = %s', $endpointId, $type);
            $values = [$endpointId, $type];
        } elseif ($hostId !== null) {
            $where = sprintf('WHERE host = %s AND type = %s', $hostId, $type);
            $values = [$hostId, $type];
        }
        
        $stmt = $this->db->prepare(sprintf('DELETE FROM mapping %s', $where));
        
        return $stmt->execute($values);
    }
    
    /**
     * Clears the entire link table
     *
     * @param int|null $type
     * @return boolean
     */
    public function clear(int $type = null): bool
    {
        return $this->db->query('DELETE FROM mapping');
    }
}
