<?php
/**
 * @category    SchumacherFM_Anonygento
 * @package     sql
 * @author      Cyrill at Schumacher dot fm
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @bugs        https://github.com/SchumacherFM/Anonygento/issues
 */
$installer = $this;
/* @var $installer SchumacherFM_Anonygento_Model_Resource_Setup */

$installer->startSetup();

$entityTableNames = array(
    'customer/entity',
    'customer/address_entity',

    'sales/order',
    'sales/order_address',
    'sales/order_grid',
    'sales/order_payment',

    'sales/quote',
    'sales/quote_address',
    'sales/quote_payment',

    'sales/creditmemo',
    'sales/creditmemo_grid',
    'sales/creditmemo_comment',

    'sales/invoice',
    'sales/invoice_grid',
    'sales/invoice_comment',
    'sales/shipment',
    'sales/shipment_grid',
    'sales/shipment_comment',

    'newsletter/subscriber',
    'giftmessage/message',
    'review/review',
    'rating/rating_option_vote',
    'sendfriend/sendfriend',

//    'review/review',

    /*
     * @todo add tables from enterprise
     * enterprise_giftregistry_entity
     * enterprise_rma
     * enterprise_rma_grid
     * enterprise_scheduled_operations ?
     *
     */
);

if ($this->isEnterpriseEdition()) {
    $entityTableNames[] = 'enterprise_salesarchive/order_grid';
    $entityTableNames[] = 'enterprise_salesarchive/creditmemo_grid';
    $entityTableNames[] = 'enterprise_salesarchive/invoice_grid';
    $entityTableNames[] = 'enterprise_salesarchive/shipment_grid';
}

foreach ($entityTableNames as $entity) {

    $installer
        ->getConnection()
        ->addColumn($installer->getTable($entity), 'anonymized', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0');

}

$attributeEavEntities = array(
    'customer',
    'customer_address',
    'creditmemo',
    'order',
    'invoice',
    'shipment',

);
foreach ($attributeEavEntities as $name) {

    $installer->removeAttribute($name, 'anonymized');

    $installer->addAttribute($name, 'anonymized', array(
        'type'         => 'static',
        'group'        => 'General',
        'label'        => 'Is anonymized',
        'is_visible'   => TRUE,
        'visible'      => TRUE,
        'default'      => 0,
        'user_defined' => FALSE,
        'input'        => 'boolean',
        'backend'      => 'customer/attribute_backend_data_boolean',
        'required'     => FALSE,
        'is_system'    => TRUE,
        'position'     => 200,
        'sort_order'   => 200,
    ));

}

$installer->endSetup();