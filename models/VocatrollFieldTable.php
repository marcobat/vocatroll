<?php

/**
 * @package     omeka
 * @subpackage  Vocatroll
 * @copyright   2015 ASHP/CML
 * @license     GPL
 */


class VocatrollFieldTable extends Omeka_Db_Table
{

  public function getVocatrollRecords() {

    $db = get_db();
    $sql = "
SELECT 

`{$db->prefix}vocatroll_fields`.`id` AS `id`,
CASE WHEN `{$db->prefix}vocatroll_fields`.`name` != '' THEN
  `{$db->prefix}vocatroll_fields`.`name`
ELSE
  `{$db->prefix}elements`.`name`
END AS `name`,
CASE WHEN `{$db->prefix}vocatroll_fields`.`description` != '' THEN
  `{$db->prefix}vocatroll_fields`.`description`
ELSE 
  `{$db->prefix}elements`.`description`
END AS `description`,
`{$db->prefix}vocatroll_fields`.`type` AS `type`,   
`{$db->prefix}vocatroll_fields`.`options` AS `options`,   
`{$db->prefix}element_sets`.`name` AS `element_set_name`

FROM `{$db->prefix}vocatroll_fields` 
LEFT JOIN `{$db->prefix}elements` ON `{$db->prefix}elements`.`id` = `{$db->prefix}vocatroll_fields`.`element_id`
LEFT JOIN `{$db->prefix}element_sets` ON `{$db->prefix}element_sets`.`id` = `{$db->prefix}vocatroll_fields`.`element_set_id`

ORDER BY `{$db->prefix}vocatroll_fields`.`element_set_id`, `{$db->prefix}elements`.`order`
    ";
    $result = $db->query($sql);
    return $result->fetchAll();
  
  }


  public function findByElemId($element_id) {
    return $this->findBySql('element_id=?', array($element_id), true);
  }

  public function findById($id) {
    return $this->findBySql('id=?', array($id), true);
  }

}
