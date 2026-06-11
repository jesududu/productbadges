<?php
/**
 * Controlador para gestionar las etiquetas mediante HelperList y HelperForm
 */

class AdminProductBadgesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'productbadges';
        $this->className = 'ProductBadgeModel'; // Vinculado al ObjectModel
        $this->lang = true; // Multilenguaje activo
        $this->bootstrap = true;

        parent::__construct();

        // Configuración de las columnas del listado
        $this->fields_list = array(
            'id_productbadge' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
            'text' => array('title' => $this->l('Texto de la etiqueta'), 'width' => 'auto'),
            'color_bg' => array('title' => $this->l('Color Fondo'), 'width' => 100, 'callback' => 'displayColorBadge'),
            'position' => array('title' => $this->l('Posición'), 'width' => 100),
            'active' => array('title' => $this->l('Activo'), 'width' => 25, 'active' => 'status', 'type' => 'bool', 'align' => 'center')
        );

        $this->addRowAction('edit');
        $this->addRowAction('delete');
    }

    public function renderForm()
    {
        $this->fields_form = array(
            'legend' => array('title' => $this->l('Configurar Etiqueta'), 'icon' => 'icon-tags'),
            'input' => array(
                array('type' => 'text', 'label' => $this->l('Texto de la etiqueta'), 'name' => 'text', 'lang' => true, 'required' => true),
                array('type' => 'color', 'label' => $this->l('Color de Fondo'), 'name' => 'color_bg', 'required' => true),
                array('type' => 'color', 'label' => $this->l('Color del Texto'), 'name' => 'color_text', 'required' => true),
                array(
                    'type' => 'select',
                    'label' => $this->l('Posición'),
                    'name' => 'position',
                    'required' => true,
                    'options' => array(
                        'query' => array(
                            array('id' => 'top-left', 'name' => $this->l('Esquina superior izquierda')),
                            array('id' => 'top-right', 'name' => $this->l('Esquina superior derecha')),
                        ),
                        'id' => 'id', 'name' => 'name'
                    )
                ),
                array('type' => 'switch', 'label' => $this->l('Activa'), 'name' => 'active', 'is_bool' => true, 'values' => array(array('id' => 'on', 'value' => 1), array('id' => 'off', 'value' => 0))),
                array(
                    'type' => 'select',
                    'label' => $this->l('Asignar a productos'),
                    'name' => 'products[]',
                    'multiple' => true,
                    'class' => 'chosen',
                    'options' => array(
                        'query' => Product::getProducts($this->context->language->id, 0, 0, 'name', 'ASC'),
                        'id' => 'id_product', 'name' => 'name'
                    )
                )
            ),
            'submit' => array('title' => $this->l('Guardar'), 'class' => 'btn btn-default pull-right')
        );

        return parent::renderForm();
    }

    public function displayColorBadge($value)
    {
        return '<span style="background-color:'.$value.'; color:#fff; padding:4px 8px; border-radius:3px;">'.$value.'</span>';
    }

    public function postProcess()
    {
        parent::postProcess();
        if ($this->display === null || $this->display === 'list') {
            $id_badge = (int)Tools::getValue('id_productbadge');
            if ($id_badge) {
                $this->updateProductRelations($id_badge, Tools::getValue('products'));
            }
        }
    }

    private function updateProductRelations($id_badge, $selected_products)
    {
        Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'productbadges_product` WHERE `id_productbadge` = '.(int)$id_badge);
        if (empty($selected_products) || !is_array($selected_products)) {
            return;
        }
        $insert_data = array();
        foreach ($selected_products as $id_product) {
            $insert_data[] = array('id_productbadge' => (int)$id_badge, 'id_product' => (int)$id_product);
        }
        Db::getInstance()->insert('productbadges_product', $insert_data);
    }

    public function getFieldsValue($obj)
    {
        $fields_value = parent::getFieldsValue($obj);
        if (Validate::isLoadedObject($obj)) {
            $associated = Db::getInstance()->executeS('SELECT `id_product` FROM `'._DB_PREFIX_.'productbadges_product` WHERE `id_productbadge` = '.(int)$obj->id);
            $products = array();
            foreach ($associated as $row) {
                $products[] = (int)$row['id_product'];
            }
            $fields_value['products[]'] = $products;
        }
        return $fields_value;
    }
}
