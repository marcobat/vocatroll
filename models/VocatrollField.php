<?php

/**
 * @package     omeka
 * @subpackage  Vocatroll
 * @copyright   2015 ASHP/CML
 * @license     GPL
 */


class VocatrollField extends Omeka_Record_AbstractRecord
{


    /**
     * The id of the parent element [integer].
     */
    public $element_id;

    /**
     * The id of the element set element [integer].
     */
    public $element_set_id;

    /**
     * The id of the item_type [integer].
     */
    public $item_type_id = 0;

    /**
     * The label of the element.
     **/
    public $name;

    /**
     * The desciption of the element.
     **/
    public $description;

    /**
     * field Type  [string]. This field can be 'text', 'checkbox', 'radio', 'select', 'hide'
     */
    public $type;

    /**
     * field options  [string]. For field type 'text' or 'hide' this field will be empty, for all otehrs wil contain a list of values.
     */
    public $options;


    /**
     * Set the parent element reference.
     *
     * @param Element $element The parent element.
     */
    public function __construct($element=null)
    {

        parent::__construct();


    }





}
