<?php
/**
 * @copyright 2010-2013 JTL-Software GmbH
 * @package Jtl\Connector\Example\Controller
 */

namespace Jtl\Connector\Example\Controller;


use Jtl\Connector\Core\Controller\DeleteInterface;
use Jtl\Connector\Core\Controller\PullInterface;
use Jtl\Connector\Core\Controller\PushInterface;
use Jtl\Connector\Core\Controller\StatisticInterface;
use Jtl\Connector\Core\Linker\IdentityLinker;
use Jtl\Connector\Core\Model\Category as CategoryModel;
use Jtl\Connector\Core\Model\CategoryI18n;
use Jtl\Connector\Core\Model\DataModel;
use Jtl\Connector\Core\Model\Identity;
use Jtl\Connector\Core\Model\QueryFilter;

class Category extends DataController implements PullInterface, PushInterface, DeleteInterface, StatisticInterface
{
    /**
     * Delete
     *
     * @param DataModel $model
     * @return DataModel
     */
    public function delete(DataModel $model): DataModel
    {
        $this->query('DELETE FROM category WHERE id_category = ?', [$model->getId()->getEndpoint()]);
        $this->query('DELETE FROM category_lang WHERE id_category = ?', [$model->getId()->getEndpoint()]);
        
        return $model;
    }
    
    /**
     * Select
     *
     * @param QueryFilter $queryFilter
     * @return DataModel[]
     */
    public function pull(QueryFilter $queryFilter): array
    {
        $results = [];
        
        $categories = $this->query('
			SELECT c.*
			FROM category c
			LEFT JOIN mapping l ON c.id_category = l.endpoint AND l.type = ?
            WHERE l.host IS NULL
            LIMIT ?', [
                IdentityLinker::TYPE_CATEGORY,
                $queryFilter->getLimit(),
            ]
        );
        
        foreach ($categories as $category) {
            $categoryModel = new CategoryModel();
            $categoryModel->setId(new Identity($category['id_category']));
            $categoryModel->setParentCategoryId(new Identity($category['id_parent']));
            
            $categoryI18ns = $this->query('SELECT * FROM category_lang WHERE id_category = ?', [$categoryModel->getId()->getEndpoint()]);
            
            foreach ($categoryI18ns as $categoryI18n) {
                $categoryI18nModel = new CategoryI18n();
                $categoryI18nModel->setName($categoryI18n['name']);
                $categoryI18nModel->setDescription($categoryI18n['description']);
                $categoryI18nModel->setLanguageISO($this->getLanguageISOById($categoryI18n['id_language']));
                
                $categoryModel->addI18n($categoryI18nModel);
            }
            
            $results[] = $categoryModel;
        }
        
        return $results;
    }
    
    /**
     * Insert or update
     *
     * @param DataModel $model
     * @return DataModel
     */
    public function push(DataModel $model): DataModel
    {
        $endpointId = $this->query('SELECT endpoint FROM mapping WHERE host = ? and type = ?',
            [
                $model->getId()->getHost(),
                IdentityLinker::TYPE_CATEGORY
            ])[0]['endpoint'];
        
        $this->query('DELETE FROM category_lang WHERE id_category = ?', [$model->getId()->getEndpoint()]);
        
        if (empty($endpointId)) {
            $this->query('INSERT INTO `category`(`id_category`) VALUES (NULL)');
            $model->getId()->setEndpoint($this->db->lastInsertId());
        }
        
        /** @var CategoryI18n $categoryI18n */
        foreach ($model->getI18ns() as $categoryI18n) {
            $this->query('INSERT INTO `category_lang`(`id_category`, `id_language`, `name`, `description`) VALUES (?,?,?,?)', [
                $model->getId()->getEndpoint(),
                $this->getLanguageIdByISO($categoryI18n->getLanguageISO()),
                $categoryI18n->getName(),
                $categoryI18n->getDescription()
            ]);
        }
        
        return $model;
    }
    
    /**
     * Statistic
     *
     * @param QueryFilter $queryFilter
     * @return int
     */
    public function statistic(QueryFilter $queryFilter): int
    {
        return $this->query('
			SELECT COUNT(*) AS cnt
			FROM category c
			LEFT JOIN mapping l ON c.id_category = l.endpoint AND l.type = ?
            WHERE l.host IS NULL
        ', [
            IdentityLinker::TYPE_CATEGORY,
        ])[0]['cnt'];
    }
    
    protected function getLanguageISOById($id_language)
    {
        return $this->query('SELECT iso_code FROM language WHERE id_language = ?', [$id_language])[0]['iso_code'];
    }
    
    protected function getLanguageIdByISO($iso)
    {
        return $this->query('SELECT id_language FROM language WHERE iso_code = ?', [$iso])[0]['id_language'];
    }
}
