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

// Inclusión nativa del modelo de datos para persistencia multilenguaje
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
        $this->author = 'Jesús Bueno';
        $this->need_instance = 0;
        $this->bootstrap = true; // Requisito explícito de la prueba

        parent::__construct();

        $this->displayName = $this->l('Gestor de Etiquetas de Producto');
        $this->description = $this->l('Permite gestionar etiquetas visuales reutilizables para los productos del catálogo.');
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => '1.7.8.11'];
    }

    public function install()
    {
        // Soporte multitienda integrado de forma nativa
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        // Ejecución del script SQL de creación de tablas
        include_once(dirname(__FILE__) . '/sql/install.php');

        return parent::install()
            && $this->registerHooks()
            && $this->installConfiguration()
            && $this->installTab();
    }

    public function uninstall()
    {
        // Ejecución del script SQL de borrado limpio (sin tablas huérfanas)
        include_once(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall()
            && $this->uninstallConfiguration()
            && $this->uninstallTab();
    }

    private function registerHooks()
    {
        return $this->registerHook('displayHeader')
            && $this->registerHook('displayProductPriceBlock')
            && $this->registerHook('displayProductAdditionalInfo');
    }

    private function installConfiguration()
    {
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
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminProductBadges';
        $tab->name = array();
        
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->l('Etiquetas de Productos');
        }
        
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminCatalog');
        $tab->module = $this->name;
        
        return $tab->add();
    }

    private function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminProductBadges');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }

    /**
     * Gestión del panel de configuración global del módulo
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitProductBadgesConfig')) {
            Configuration::updateValue('PRODUCTBADGES_GLOBAL_ACTIVE', (int)Tools::getValue('PRODUCTBADGES_GLOBAL_ACTIVE'));
            Configuration::updateValue('PRODUCTBADGES_SHOW_LIST', (int)Tools::getValue('PRODUCTBADGES_SHOW_LIST'));
            Configuration::updateValue('PRODUCTBADGES_SHOW_PRODUCT', (int)Tools::getValue('PRODUCTBADGES_SHOW_PRODUCT'));
            Configuration::updateValue('PRODUCTBADGES_MAX_COUNT', (int)Tools::getValue('PRODUCTBADGES_MAX_COUNT'));

            $output .= $this->displayConfirmation($this->l('Configuración guardada correctamente.'));
        }

        return $output . $this->renderConfigForm();
    }

    protected function renderConfigForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitProductBadgesConfig';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value['PRODUCTBADGES_GLOBAL_ACTIVE'] = Configuration::get('PRODUCTBADGES_GLOBAL_ACTIVE');
        $helper->fields_value['PRODUCTBADGES_SHOW_LIST'] = Configuration::get('PRODUCTBADGES_SHOW_LIST');
        $helper->fields_value['PRODUCTBADGES_SHOW_PRODUCT'] = Configuration::get('PRODUCTBADGES_SHOW_PRODUCT');
        $helper->fields_value['PRODUCTBADGES_MAX_COUNT'] = Configuration::get('PRODUCTBADGES_MAX_COUNT');

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Ajustes Generales de las Etiquetas'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Activar módulo globalmente'),
                        'name' => 'PRODUCTBADGES_GLOBAL_ACTIVE',
                        'is_bool' => true,
                        'values' => [['id' => 'active_on', 'value' => 1], ['id' => 'active_off', 'value' => 0]]
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Mostrar en listados de categorías y búsquedas'),
                        'name' => 'PRODUCTBADGES_SHOW_LIST',
                        'is_bool' => true,
                        'values' => [['id' => 'active_on', 'value' => 1], ['id' => 'active_off', 'value' => 0]]
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Mostrar en la ficha del producto'),
                        'name' => 'PRODUCTBADGES_SHOW_PRODUCT',
                        'is_bool' => true,
                        'values' => [['id' => 'active_on', 'value' => 1], ['id' => 'active_off', 'value' => 0]]
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Número máximo de etiquetas visibles por producto'),
                        'name' => 'PRODUCTBADGES_MAX_COUNT',
                        'class' => 'fixed-width-sm',
                        'required' => true,
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Guardar Ajustes'),
                    'class' => 'btn btn-default pull-right'
                ]
            ]
        ];

        return $helper->generateForm([$fields_form]);
    }

    /**
     * Lógica e inyección de datos en el Frontend
     */
    public function hookDisplayHeader()
    {
        if (!Configuration::get('PRODUCTBADGES_GLOBAL_ACTIVE')) {
            return;
        }
        $this->context->controller->addCSS($this->_path . 'views/css/productbadges.css', 'all');
    }

    public function hookDisplayProductPriceBlock($params)
    {
        if (!Configuration::get('PRODUCTBADGES_GLOBAL_ACTIVE') || !Configuration::get('PRODUCTBADGES_SHOW_LIST')) {
            return;
        }

        if (!isset($params['product']['id_product']) && !isset($params['product']->id)) {
            return;
        }
        $id_product = isset($params['product']['id_product']) ? (int)$params['product']['id_product'] : (int)$params['product']->id;

        return $this->renderBadgesFrontend($id_product);
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        if (!Configuration::get('PRODUCTBADGES_GLOBAL_ACTIVE') || !Configuration::get('PRODUCTBADGES_SHOW_PRODUCT')) {
            return;
        }

        if (!isset($params['product']->id)) {
            return;
        }
        
        return $this->renderBadgesFrontend((int)$params['product']->id);
    }

    private function renderBadgesFrontend($id_product)
    {
        $id_lang = (int)$this->context->language->id;
        $max_badges = (int)Configuration::get('PRODUCTBADGES_MAX_COUNT');

        // Consulta SQL limpia con variables en singular
        $badges = Db::getInstance()->executeS('
            SELECT b.*, bl.`text` 
            FROM `' . _DB_PREFIX_ . 'productbadges` b
            INNER JOIN `' . _DB_PREFIX_ . 'productbadges_lang` bl ON (b.`id_productbadge` = bl.`id_productbadge` AND bl.`id_lang` = ' . $id_lang . ')
            INNER JOIN `' . _DB_PREFIX_ . 'productbadges_product` bp ON (b.`id_productbadge` = bp.`id_productbadge`)
            WHERE bp.`id_product` = ' . $id_product . ' AND b.`active` = 1
            LIMIT ' . ($max_badges > 0 ? $max_badges : 1)
        );

        if (empty($badges)) {
            return '';
        }

        $this->context->smarty->assign([
            'badges' => $badges
        ]);

        return $this->display(__FILE__, 'views/templates/hook/productbadges.tpl');
    }
}
