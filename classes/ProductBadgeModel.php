<?php
/**
 * ObjectModel nativo para la persistencia de datos de las etiquetas
 */

class ProductBadgeModel extends ObjectModel
{
    public $id_productbadge;
    public $color_bg;
    public $color_text;
    public $position;
    public $active;
    public $date_add;
    public $date_upd;
    public $text;

    public static $definition = array(
        'table' => 'productbadges',
        'primary' => 'id_productbadge',
        'multilang' => true,
        'fields' => array(
            'color_bg' => array('type' => self::TYPE_STRING, 'validate' => 'isColor', 'required' => true, 'size' => 7),
            'color_text' => array('type' => self::TYPE_STRING, 'validate' => 'isColor', 'required' => true, 'size' => 7),
            'position' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 20),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'text' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
        ),
    );
}
