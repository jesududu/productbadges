<?php
/**
 * Script de desinstalación de Base de Datos para productbadges
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

$sql = array(
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'productbadges`',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'productbadges_lang`',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'productbadges_product`'
);

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

return true;
