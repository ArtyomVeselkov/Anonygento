<?php
/**
 * @category    SchumacherFM_Anonygento
 * @package     Model
 * @author      Cyrill at Schumacher dot fm
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @bugs        https://github.com/SchumacherFM/Anonygento/issues
 */
class SchumacherFM_Anonygento_Model_Anonymizations_OrderAddress extends SchumacherFM_Anonygento_Model_Anonymizations_Abstract
{

    public function run()
    {
        parent::run($this->_getCollection(), '_anonymizeOrderAddress');
    }

    /**
     * @param Mage_Sales_Model_Order_Address $orderAddress
     */
    protected function _anonymizeOrderAddress(Mage_Sales_Model_Order_Address $orderAddress)
    {

        $randomCustomer = $this->_getRandomCustomer()->getCustomer();
        $this->_copyObjectData($randomCustomer, $orderAddress, $this->_getMappings('orderAddress'));
        $orderAddress->getResource()->save($orderAddress);
        $orderAddress = null;
    }

    /**
     * @param Mage_Sales_Model_Order       $order
     * @param Mage_Customer_Model_Customer $customer
     */
    public function anonymizeByOrder(Mage_Sales_Model_Order $order, Mage_Customer_Model_Customer $customer = null)
    {
        if ($customer === null) {
            $customer = $this->_getRandomCustomer()->getCustomer();
        }

        $orderAddressCollection = $order->getAddressesCollection();
        /* @var $orderAddressCollection Mage_Sales_Model_Resource_Order_Address_Collection */

        foreach ($orderAddressCollection as $orderAddress) {
            $this->_copyObjectData($customer, $orderAddress, $this->_getMappings('orderAddress'));
//            $orderAddress->getResource()->save($orderAddress);
            $orderAddress->save();
        }
        $orderAddressCollection = null;

    }

    /**
     * @return Mage_Sales_Model_Resource_Order_Address_Collection
     */
    protected function _getCollection()
    {
        return parent::_getCollection('sales/order_address', 'orderAddress');
    }
}