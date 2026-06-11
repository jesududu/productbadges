<?php
/**
 * Script de instalación de Base de Datos para productbadges
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

$sql = array();

// 1. Tabla principal de la etiqueta
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadges` (
    `id_productbadge` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `color_bg` VARCHAR(7) NOT NULL DEFAULT "#000000",
    `color_text` VARCHAR(7) NOT NULL DEFAULT "#FFFFFF",
    `position` VARCHAR(20) NOT NULL DEFAULT "top-left",
    `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_productbadge`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

// 2. Tabla multilenguaje (Traducciones del texto)
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadges_lang` (
    `id_productbadge` INT(11) UNSIGNED NOT NULL,
    `id_lang` INT(11) UNSIGNED NOT NULL,
    `text` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_productbadge`, `id_lang`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

// 3. Tabla intermedia Muchos a Muchos con productos
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadges_product` (
    `id_productbadge` INT(11) UNSIGNED NOT NULL,
    `id_product` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_productbadge`, `id_product`),
    KEY `id_product` (`id_product`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

return true;
