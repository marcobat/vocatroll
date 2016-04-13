<?php
/**
 * Vocatroll
 *
 * @copyright Copyright 2015 ASHP/CML
 * @license GPL
 */


/**
 * Vocatroll plugin.
 */
 
if (!defined('VOCATROLL_PLUGIN_DIR')) {
  define('VOCATROLL_PLUGIN_DIR', dirname(__FILE__));
}

class VocatrollPlugin extends Omeka_Plugin_AbstractPlugin {
  /**
   * @var array Hooks and Filters for the plugin.
   */
  protected $_hooks = array('install', 'uninstall', 'upgrade', 'define_routes', 'admin_head');

  protected $_filters = array('admin_navigation_main');

  /**
   * Install the plugin.
   */
  public function hookInstall() {

        $this->_db->query(<<<SQL
        CREATE TABLE IF NOT EXISTS {$this->_db->prefix}vocatroll_fields (
            id              int(10) unsigned NOT NULL auto_increment,
            element_id      INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0',
            element_set_id  INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0',
            item_type_id    INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0',
            name            tinytext collate utf8_unicode_ci NOT NULL,
            description     text collate utf8_unicode_ci NOT NULL,
            type            tinytext collate utf8_unicode_ci NOT NULL,
            options         text collate utf8_unicode_ci NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=z DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
);



  }

  /**
   * Uninstall the plugin.
   */
  public function hookUninstall() {        

    $this->_db->query("DROP TABLE IF EXISTS {$this->_db->prefix}vocatroll_fields ");

  }


  /**
   * Upgrade the plugin.
   *
   * nothing to do
   */
  public function hookUpgrade($args) {

    $oldVersion = $args['old_version'];
    $newVersion = $args['new_version'];

    if (version_compare($oldVersion, '0.0.3', '<=')) {
      $this->_db->query("DELETE FROM {$this->_db->prefix}vocatroll_fields WHERE `type` = 'text'");
      $this->_db->query("ALTER TABLE  `{$this->_db->prefix}vocatroll_fields` CHANGE  `label`  `name` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
    }
      
    if (version_compare($oldVersion, '0.0.4', '<=')) {
      $this->_db->query("ALTER TABLE `{$this->_db->prefix}vocatroll_fields` ADD `item_type_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `element_set_id` ");
      $this->_db->query("ALTER TABLE `{$this->_db->prefix}vocatroll_fields` CHANGE `element_id` `element_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0' ");
      $this->_db->query("ALTER TABLE `{$this->_db->prefix}vocatroll_fields` CHANGE `element_set_id` `element_set_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' ");
    }

  }

  /**
   * Add the routes for accessing Vocatroll
   * 
   * @param Zend_Controller_Router_Rewrite $router
   */
  public function hookDefineRoutes($args) {

    if (is_admin_theme()) {
      return;
    }

    $router = $args['router'];
    $router->addConfig(new Zend_Config_Ini(VOCATROLL_PLUGIN_DIR .
    DIRECTORY_SEPARATOR . 'routes.ini', 'routes'));
  }



  /**
   * Adds a navigation item
   */
  public function filterAdminNavigationMain($nav) {
    $nav[] = array(
      'label' => __('Vocatroll'), 'uri' => url('vocatroll/voca/dcfields')
    );
    return $nav;
  }



  /**
   * Admin Head
   */
  public function hookAdminHead() {        
    $tags = array();
    if (is_array($tg = get_records('Tag',array('sort_field' => 'name', 'sort_dir' => 'a',),15000))) {
      foreach ($tg as $t) {
        $tags[] = $t->name;
      }
    }
    $itemTags = array();
    if (get_current_record('item', false) && metadata('item','has tags')){
      $itemTags = explode(',', tag_string('Item',null,','));
    }
    queue_js_file('vocatroll');
    queue_css_file('vocatroll');
    $db = get_db();
    
    $coverage = false;
    if (plugin_is_active('Coverage')) { // if the coverage plugin is active and installed
      $result = $this->_db->query("SELECT `title` FROM `{$db->prefix}coverages` ORDER BY `sort`");
      $coverage = array();
      foreach ($result->fetchAll() as $c) {
        $coverage[] = $c['title'];
      }
    }
    
    
    $result = $this->_db->query(<<<SQL

SELECT 

`{$db->prefix}elements`.`id` AS `id`,
`{$db->prefix}elements`.`element_set_id` AS `element_set_id`,

CASE WHEN `{$db->prefix}vocatroll_fields`.`name`  != '' THEN
  `{$db->prefix}vocatroll_fields`.`name`
ELSE
  `{$db->prefix}elements`.`name` 
END AS `name`,

CASE WHEN `{$db->prefix}vocatroll_fields`.`description`  != '' THEN
  `{$db->prefix}vocatroll_fields`.`description`
ELSE
  `{$db->prefix}elements`.`description` 
END AS `description`,

`{$db->prefix}element_sets`.`name` AS `element_set_name`,

CASE WHEN `{$db->prefix}vocatroll_fields`.`type` != '' THEN
  `{$db->prefix}vocatroll_fields`.`type`
ELSE
  'text'
END AS `type`,


CASE WHEN `{$db->prefix}vocatroll_fields`.`options`  != '' THEN
  `{$db->prefix}vocatroll_fields`.`options`
ELSE
  ''
END AS `options`


FROM

`{$db->prefix}elements`

INNER JOIN `{$db->prefix}vocatroll_fields` ON `{$db->prefix}vocatroll_fields`.`element_id` = `{$db->prefix}elements`.`id`
LEFT JOIN `{$db->prefix}element_sets` ON `{$db->prefix}element_sets`.`id` = `{$db->prefix}elements`.`element_set_id`

ORDER BY `{$db->prefix}element_sets`.`name`, `{$db->prefix}elements`.`order`

SQL
);

    $vocatroll_values = array();
    foreach ($result->fetchAll() as $field) {

      if ($coverage !== false && $field['name'] == 'Coverage' && $field['element_set_name'] == 'Dublin Core') {

        $vocatroll_values[] = array(
          'elemName' => $field['name'],
          'elemDescription' => $field['description'],
          'elem' => $field['id'],
          'elemtype' => 'checkbox',
          'elemSelected' => array(),
          'elemlist' => $coverage,
        );
      
      } else {

        $vocatroll_values[] = array(
          'elemName' => $field['name'],
          'elemDescription' => $field['description'],
          'elem' => $field['id'],
          'elemtype' => $field['type'],
          'elemSelected' => array(),
          'elemlist' => explode("\r",$field['options']),
        );
        
      }
    }

    queue_js_string('var customInput = '.json_encode($vocatroll_values).';');
    
    
 
 
     $result = $this->_db->query(<<<SQL

SELECT 

`{$db->prefix}elements`.`id` AS `id`,
`{$db->prefix}elements`.`element_set_id` AS `element_set_id`,

`{$db->prefix}vocatroll_fields`.`item_type_id` AS `item_type_id`,

CASE WHEN `{$db->prefix}vocatroll_fields`.`name`  != '' THEN
  `{$db->prefix}vocatroll_fields`.`name`
ELSE
  `{$db->prefix}elements`.`name` 
END AS `name`,

CASE WHEN `{$db->prefix}vocatroll_fields`.`description`  != '' THEN
  `{$db->prefix}vocatroll_fields`.`description`
ELSE
  `{$db->prefix}elements`.`description` 
END AS `description`,

`{$db->prefix}element_sets`.`name` AS `element_set_name`,

CASE WHEN `{$db->prefix}vocatroll_fields`.`type` != '' THEN
  `{$db->prefix}vocatroll_fields`.`type`
ELSE
  'text'
END AS `type`,


CASE WHEN `{$db->prefix}vocatroll_fields`.`options`  != '' THEN
  `{$db->prefix}vocatroll_fields`.`options`
ELSE
  ''
END AS `options`


FROM

`{$db->prefix}elements`

INNER JOIN `{$db->prefix}vocatroll_fields` ON `{$db->prefix}vocatroll_fields`.`element_id` = `{$db->prefix}elements`.`id`
LEFT JOIN `{$db->prefix}element_sets` ON `{$db->prefix}element_sets`.`id` = `{$db->prefix}elements`.`element_set_id`

WHERE

`{$db->prefix}element_sets`.`name` = 'Item Type Metadata'

ORDER BY `{$db->prefix}elements`.`order`

SQL
);

    $item_type_metadate = array();
    foreach ($result->fetchAll() as $field) {


        $item_type_metadate[$field['item_type_id']][] = array(
          'elemName' => $field['name'],
          'elemDescription' => $field['description'],
          'elem' => $field['id'],
          'elemtype' => $field['type'],
          'elemSelected' => array(),
          'elemlist' => explode("\r",$field['options']),
        );
        
      
    }

    queue_js_string('var item_type_metadate = '.json_encode($item_type_metadate).';');

   
    
    
  }



}