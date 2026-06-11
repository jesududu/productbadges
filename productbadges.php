<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
// Incluir el modelo de datos de forma limpia en PrestaShop 1.7
include_once dirname(__FILE__) . '/classes/ProductBadgeModel.php';

class ProductBadges extends Module
{
    protected $config_keys = [
        'PRODUCTBADGES_GLOBAL_ACTIVE',
        'PRODUCTBADGES_SHOW_LIST',
        'PRODUCTBADGES_SHOW_PRODUCT',
        'PRODUCTBADGES_MAX_COUNT'
    ];

    public function __construct()
    {
        $this->name = 'productbadges';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Tu Nombre';
        $this->need_instance = 0;
        $this->bootstrap = true; // Requisito explícito de la prueba

        parent::__construct();

        $this->displayName = $this->l('Gestor de Etiquetas de Producto');
        $this->description = $this->l('Permite gestionar etiquetas visuales reutilizables para los productos del catálogo.');
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => '1.7.8.11'];
    }

    public function install()
    {
        // Comprobar multitienda (Requisito funcional de la prueba)
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        // Cargar scripts SQL de instalación
        include_once(dirname(__FILE__) . '/sql/install.php');

        return parent::install()
            && $this->registerHooks()
            && $this->installConfiguration()
            && $this->installTab();
    }

    public function uninstall()
    {
        // Cargar scripts SQL de desinstalación limpia
        include_once(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall()
            && $this->uninstallConfiguration()
            && $this->uninstallTab();
    }

    private function registerHooks()
    {
        // Registramos los ganchos visuales solicitados
        return $this->registerHook('displayHeader') // Para cargar CSS de las badges
            && $this->registerHook('displayProductPriceBlock') // Para listados, home y búsqueda
            && $this->registerHook('displayProductAdditionalInfo'); // Para la ficha de producto
    }

    private function installConfiguration()
    {
        // Valores iniciales por defecto en la tabla de configuración
        return Configuration::updateValue('PRODUCTBADGES_GLOBAL_ACTIVE', 1)
            && Configuration::updateValue('PRODUCTBADGES_SHOW_LIST', 1)
            && Configuration::updateValue('PRODUCTBADGES_SHOW_PRODUCT', 1)
            && Configuration::updateValue('PRODUCTBADGES_MAX_COUNT', 2);
    }

    private function uninstallConfiguration()
    {
        foreach ($this->config_keys as $key) {
            Configuration::deleteByName($key);
        }
        return true;
    }

    private function installTab()
    {
        // Aquí registraremos la pestaña del menú administrativo en el siguiente paso
        return true;
    }

    private function uninstallTab()
    {
        return true;
    }
}