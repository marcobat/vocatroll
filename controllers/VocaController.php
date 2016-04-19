<?php
/**
 * Vocatroll
 *
 * @copyright Copyright 2008-2015 ASHP/CML
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Vocatroll voca controller class.
 *
 * @package Vocatroll
 */
class Vocatroll_VocaController extends Omeka_Controller_AbstractActionController {

  public function showAction() {
    switch ($this->_getParam('element')) {
      case 'subject':
      case 'coverage':
        $element = ucwords($this->_getParam('element'));
        break;
      default:
        $element = 'Subject';
    }
    
    $this->view->element = $element;
    $db = get_db();
    $sql = "

SELECT `{$db->prefix}element_texts`.`text` AS `element` FROM `{$db->prefix}element_texts` 
LEFT JOIN `{$db->prefix}elements` ON `{$db->prefix}elements`.`id` = `{$db->prefix}element_texts`.`element_id`
LEFT JOIN `{$db->prefix}element_sets` ON `{$db->prefix}element_sets`.`id` = `{$db->prefix}elements`.`element_set_id`
LEFT JOIN `{$db->prefix}items` ON `{$db->prefix}items`.`id` = `{$db->prefix}element_texts`.`record_id`

WHERE

`{$db->prefix}elements`.`name` = '$element' AND 
`{$db->prefix}element_sets`.`name` = 'Dublin Core' AND
`{$db->prefix}element_texts`.`record_type` = 'item' AND 
`{$db->prefix}items`.`public` = '1'

GROUP BY `{$db->prefix}element_texts`.`text`


    ";
    $result = $db->query($sql);
    $this->view->results = $result->fetchAll();
  }



  private function _savePosts () {
  
    $VocatrollField = $this->_helper->db->getTable('VocatrollField');
    if ($this->_request->isPost()) {
      $post = $this->_request->getPost();
      if (is_array($post['ids'])) {
        foreach ($post['ids'] as $id) {
          if (!isset($post['vocatroll-item_type_id-'.$id]) || $post['vocatroll-item_type_id-'.$id] == NULL) {
            $post['vocatroll-item_type_id-'.$id] = 0;
          }
          // Apply the updated values.
          if (($post['vocatroll-name-'.$id] != $post['vocatroll-name-original-value-'.$id]) 
              || 
              ($post['vocatroll-description-'.$id] != $post['vocatroll-description-original-value-'.$id])
              ||
              ($post['vocatroll-options-'.$id] != '')
              ||
              ($post['vocatroll-type-'.$id] != $post['vocatroll-type-original-value-'.$id])
              ) {
            if (is_numeric($id)) {
              $voca = $VocatrollField->findById($id);
              $voca->name = $post['vocatroll-name-'.$id];
              $voca->type = $post['vocatroll-type-'.$id];
              $voca->options = $post['vocatroll-options-'.$id];
              $voca->description = $post['vocatroll-description-'.$id];              
              $voca->save();
            } else {
         
              $voca = new VocatrollField();
              $voca->setPostData(array('element_id' => $post['vocatroll-element_id-'.$id], 'element_set_id' => $post['vocatroll-element_set_id-'.$id], 'item_type_id' => $post['vocatroll-item_type_id-'.$id], 'name' => $post['vocatroll-name-'.$id], 'type' => $post['vocatroll-type-'.$id], 'options' => $post['vocatroll-options-'.$id], 'description' => $post['vocatroll-description-'.$id]));
              $voca->save(false);

            }
          }
        }
      }

      $this->_helper->flashMessenger(__('Fields successfully updated!'),'success');
    }
  
  
  }



  public function mdfieldsAction () { // Media Type Fields
  
     $this->_savePosts();    

    $db = get_db();
    $result = $db->query(<<<SQL

SELECT 

`{$db->prefix}elements`.`id` AS `element_id`,
`{$db->prefix}elements`.`element_set_id` AS `element_set_id`,
`{$db->prefix}item_types`.`id` AS `item_type_id`,
`{$db->prefix}item_types`.`name` AS `item_types_name`,

CASE WHEN (`{$db->prefix}vocatroll_fields`.`id` != '' AND `{$db->prefix}vocatroll_fields`.`id` IS NOT NULL) THEN
  `{$db->prefix}vocatroll_fields`.`id`
ELSE
  MD5(CONCAT(`{$db->prefix}elements`.`name`, `{$db->prefix}item_types`.`name`))
END AS `id`,


CASE WHEN (`{$db->prefix}vocatroll_fields`.`name`  != '' AND `{$db->prefix}vocatroll_fields`.`name` IS NOT NULL) THEN
  `{$db->prefix}vocatroll_fields`.`name`
ELSE
  `{$db->prefix}elements`.`name` 
END AS `name`,

CASE WHEN (`{$db->prefix}vocatroll_fields`.`description` != '' AND `{$db->prefix}vocatroll_fields`.`description` IS NOT NULL) THEN
  `{$db->prefix}vocatroll_fields`.`description`
ELSE
  `{$db->prefix}elements`.`description` 
END AS `description`,

`{$db->prefix}element_sets`.`name` AS `element_set_name`,

CASE WHEN (`{$db->prefix}vocatroll_fields`.`type` != '' AND `{$db->prefix}vocatroll_fields`.`type` IS NOT NULL) THEN
  `{$db->prefix}vocatroll_fields`.`type`
ELSE
  'text'
END AS `type`,


CASE WHEN (`{$db->prefix}vocatroll_fields`.`options`  != '' AND `{$db->prefix}vocatroll_fields`.`options` IS NOT NULL) THEN
  `{$db->prefix}vocatroll_fields`.`options`
ELSE
  ''
END AS `options`


FROM

`{$db->prefix}item_types_elements`

LEFT JOIN `{$db->prefix}elements` ON `{$db->prefix}elements`.`id` = `{$db->prefix}item_types_elements`.`element_id`
LEFT JOIN `{$db->prefix}element_sets` ON `{$db->prefix}element_sets`.`id` = `{$db->prefix}elements`.`element_set_id`
LEFT JOIN `{$db->prefix}item_types` ON `{$db->prefix}item_types`.`id` = `{$db->prefix}item_types_elements`.`item_type_id`
LEFT JOIN `{$db->prefix}vocatroll_fields` ON `{$db->prefix}vocatroll_fields`.`element_id` = `{$db->prefix}elements`.`id`  AND `{$db->prefix}vocatroll_fields`.`item_type_id` = `{$db->prefix}item_types_elements`.`item_type_id`

WHERE

`{$db->prefix}element_sets`.`name` = 'Item Type Metadata' AND `{$db->prefix}item_types`.`id` IS NOT NULL

ORDER BY `{$db->prefix}item_types`.`name`

SQL
);

    $this->view->records = $result->fetchAll();
    $this->view->action = 'mdfields';
 
  
  }







  public function csfieldsAction () {

    $this->_savePosts();
    
    $db = get_db();
    $conditional_where_clause = '';
    if (plugin_is_active('ProductionNotes')) { // if the prodduction notes plugin is active and installed
      $conditional_where_clause .= " AND `{$db->prefix}element_sets`.`name` != 'Production Notes' \n";
    }
    

    $result = $db->query(<<<SQL

SELECT 

`{$db->prefix}elements`.`id` AS `element_id`,
`{$db->prefix}elements`.`element_set_id` AS `element_set_id`,

CASE WHEN (`{$db->prefix}vocatroll_fields`.`id`  != '' AND `{$db->prefix}vocatroll_fields`.`id` IS NOT NULL) THEN
  `{$db->prefix}vocatroll_fields`.`id`
ELSE
  MD5(`{$db->prefix}elements`.`name`)
END AS `id`,


CASE WHEN (`{$db->prefix}vocatroll_fields`.`name`  != '' AND `{$db->prefix}vocatroll_fields`.`name` IS NOT NULL) THEN
  `{$db->prefix}vocatroll_fields`.`name`
ELSE
  `{$db->prefix}elements`.`name` 
END AS `name`,

CASE WHEN (`{$db->prefix}vocatroll_fields`.`description` != '' AND `{$db->prefix}vocatroll_fields`.`description` IS NOT NULL) THEN
  `{$db->prefix}vocatroll_fields`.`description`
ELSE
  `{$db->prefix}elements`.`description` 
END AS `description`,

`{$db->prefix}element_sets`.`name` AS `element_set_name`,

CASE WHEN (`{$db->prefix}vocatroll_fields`.`type` != '' AND `{$db->prefix}vocatroll_fields`.`type` IS NOT NULL) THEN
  `{$db->prefix}vocatroll_fields`.`type`
ELSE
  'text'
END AS `type`,


CASE WHEN (`{$db->prefix}vocatroll_fields`.`options`  != '' AND `{$db->prefix}vocatroll_fields`.`options` IS NOT NULL) THEN
  `{$db->prefix}vocatroll_fields`.`options`
ELSE
  ''
END AS `options`


FROM

`{$db->prefix}elements`

LEFT JOIN `{$db->prefix}vocatroll_fields` ON `{$db->prefix}vocatroll_fields`.`element_id` = `{$db->prefix}elements`.`id`
LEFT JOIN `{$db->prefix}element_sets` ON `{$db->prefix}element_sets`.`id` = `{$db->prefix}elements`.`element_set_id`

WHERE

`{$db->prefix}element_sets`.`name` != 'Dublin Core' AND `{$db->prefix}element_sets`.`name` != 'Item Type Metadata' 
$conditional_where_clause

ORDER BY `{$db->prefix}element_sets`.`name`, `{$db->prefix}elements`.`order`

SQL
);

    $this->view->records = $result->fetchAll();
    $this->view->action = 'csfields';






  }
  

  public function dcfieldsAction() {

    $this->_savePosts();
    
    $db = get_db();
    $conditional_where_clause = '';
    if (plugin_is_active('Coverage')) { // if the coverage plugin is active and installed
      $conditional_where_clause .= " AND `{$db->prefix}elements`.`name` != 'Coverage' \n";
      $this->_helper->flashMessenger(__('The Coverage Field is not displayed. It is controlled by the "coverage" Plugin'),'');
    }
    

    $result = $db->query(<<<SQL

SELECT 

`{$db->prefix}elements`.`id` AS `element_id`,
`{$db->prefix}elements`.`element_set_id` AS `element_set_id`,

CASE WHEN (`{$db->prefix}vocatroll_fields`.`id`  != '' AND `{$db->prefix}vocatroll_fields`.`id` IS NOT NULL) THEN
  `{$db->prefix}vocatroll_fields`.`id`
ELSE
  MD5(`{$db->prefix}elements`.`name`)
END AS `id`,


CASE WHEN (`{$db->prefix}vocatroll_fields`.`name`  != '' AND `{$db->prefix}vocatroll_fields`.`name` IS NOT NULL) THEN
  `{$db->prefix}vocatroll_fields`.`name`
ELSE
  `{$db->prefix}elements`.`name` 
END AS `name`,

CASE WHEN (`{$db->prefix}vocatroll_fields`.`description` != '' AND `{$db->prefix}vocatroll_fields`.`description` IS NOT NULL) THEN
  `{$db->prefix}vocatroll_fields`.`description`
ELSE
  `{$db->prefix}elements`.`description` 
END AS `description`,

`{$db->prefix}element_sets`.`name` AS `element_set_name`,

CASE WHEN (`{$db->prefix}vocatroll_fields`.`type` != '' AND `{$db->prefix}vocatroll_fields`.`type` IS NOT NULL) THEN
  `{$db->prefix}vocatroll_fields`.`type`
ELSE
  'text'
END AS `type`,


CASE WHEN (`{$db->prefix}vocatroll_fields`.`options`  != '' AND `{$db->prefix}vocatroll_fields`.`options` IS NOT NULL) THEN
  `{$db->prefix}vocatroll_fields`.`options`
ELSE
  ''
END AS `options`


FROM

`{$db->prefix}elements`

LEFT JOIN `{$db->prefix}vocatroll_fields` ON `{$db->prefix}vocatroll_fields`.`element_id` = `{$db->prefix}elements`.`id`
LEFT JOIN `{$db->prefix}element_sets` ON `{$db->prefix}element_sets`.`id` = `{$db->prefix}elements`.`element_set_id`

WHERE

`{$db->prefix}element_sets`.`name` = 'Dublin Core' 
$conditional_where_clause

ORDER BY `{$db->prefix}element_sets`.`name`, `{$db->prefix}elements`.`order`

SQL
);

    $this->view->records = $result->fetchAll();
    $this->view->action = 'dcfields';


  
  }

}
