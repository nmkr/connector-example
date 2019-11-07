<?php
/**
 * @copyright 2010-2013 JTL-Software GmbH
 * @package Jtl\Connector\Example\Controller
 */

namespace Jtl\Connector\Example\Controller;

use Jtl\Connector\Core\Controller\PullInterface;
use Jtl\Connector\Core\Model\Currency;
use Jtl\Connector\Core\Model\CustomerGroup;
use Jtl\Connector\Core\Model\CustomerGroupI18n;
use Jtl\Connector\Core\Model\DataModel;
use Jtl\Connector\Core\Model\GlobalData as GlobalDataModel;
use Jtl\Connector\Core\Model\Identity;
use Jtl\Connector\Core\Model\Language;
use Jtl\Connector\Core\Model\QueryFilter;
use Jtl\Connector\Core\Model\ShippingMethod;
use Jtl\Connector\Core\Model\TaxRate;
use Jtl\Connector\Core\Result\Action;

class GlobalData extends DataController implements PullInterface
{
    /**
     * Pull
     *
     * @param QueryFilter $queryFilter
     * @return Action
     */
    public function pull(QueryFilter $queryFilter): array
    {
        $globalData = new GlobalDataModel();
        
        // ***************************************
        // * Static values for presentation only *
        // ***************************************
        
        $id1 = new Identity(1);
        $id2 = new Identity(2);
        
        // Languages
        $globalData->addLanguage(
            (new Language())->setId($id1)
                ->setLanguageISO('ger')
                ->setIsDefault(true)
                ->setNameGerman('Deutsch')
                ->setNameEnglish('German')
        );
        
        $globalData->addLanguage(
            (new Language())->setId($id2)
                ->setLanguageISO('eng')
                ->setIsDefault(false)
                ->setNameGerman('Englisch')
                ->setNameEnglish('English')
        );
        
        // Currencies
        $globalData->addCurrency(
            (new Currency())->setId($id1)
                ->setIsDefault(true)
                ->setName('Euro')
                ->setDelimiterCent(',')
                ->setDelimiterThousand('.')
                ->setFactor(1.0)
                ->setHasCurrencySignBeforeValue(false)
                ->setIso('EUR')
                ->setNameHtml('&euro;')
        );
        
        // CustomerGroups
        $globalData->addCustomerGroup(
            (new CustomerGroup())->setId($id1)
                ->setIsDefault(true)
                ->setApplyNetPrice(false)
                ->addI18n((new CustomerGroupI18n())->setLanguageISO('ger')->setName('Endkunde'))
        );
        
        $globalData->addCustomerGroup(
            (new CustomerGroup())->setId($id2)
                ->setIsDefault(false)
                ->setApplyNetPrice(true)
                ->addI18n((new CustomerGroupI18n())->setLanguageISO('ger')->setName('Haendler'))
        );
        
        // TaxRates
        $globalData->addTaxRate(
            (new TaxRate())->setId($id1)
                ->setRate(19.0)
        );
        
        $globalData->addTaxRate(
            (new TaxRate())->setId($id2)
                ->setRate(7.0)
        );
        
        // shippingMethods
        $globalData->addShippingMethod(
            (new ShippingMethod())->setId($id1)
                ->setName('DHL Versand')
        );
        
        return [$globalData];
    }
    
    /**
     * Insert or update
     *
     * @param DataModel $model
     * @return Action
     */
    public function push(DataModel $model)
    {
        $action = new Action();

        $action->setResult(new GlobalData($this->application));

        return $action;
    }
}
