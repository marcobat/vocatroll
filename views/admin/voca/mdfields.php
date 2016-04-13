<?php 

queue_css_string('div.field-group{padding-top: 1em;margin-bottom:1em;border-top: 1px solid #cccccc;clear:both;overflow: auto;}');

echo head(array(
    'title' => 'Vocatroll | Configuration',
    'bodyclass' => 'page vocatroll',
    'bodyid' => 'vocatroll_page',
)); 

?>

<ul id="section-nav" class="navigation">
    <li class="<?php if ($action == 'dcfields') {echo 'current';} ?>">
        <a href="<?php echo html_escape(url('vocatroll/voca/dcfields')); ?>"><?php echo __('Set Dublin Core Fields'); ?></a>
    </li>
    <li class="<?php if ($action == 'mdfields') {echo 'current';} ?>">
        <a href="<?php echo html_escape(url('vocatroll/voca/mdfields')); ?>"><?php echo __('Set Metadata Fields'); ?></a>
    </li>
    <li class="<?php if ($action == 'csfields') {echo 'current';} ?>">
        <a href="<?php echo html_escape(url('vocatroll/voca/csfields')); ?>"><?php echo __('Set Custom Element Sets Fields'); ?></a>
    </li>
</ul>


<?php echo flash(); ?>
<?php


function even_or_odd() {
  static $value = 1;
  if ($value == 1) {
    $value = 2;
    return 'odd';
  } else {
    $value = 1;
    return 'even';
  }
}
function populate_type_options($value) {
  $options = '';
  foreach(array('text', 'checkbox', 'select', 'date', 'hide') as $v) {
    $sel = '';
    if ($value == $v) {
      $sel = ' selected="selected"';
    }
    $options .= '<option value="'.$v.'"'.$sel.'>'.ucwords($v).'</option>';
  }
  return $options;
}
?>
<div id="primary">
	<form id="vocatroll-fields-form" method="post" class="vocatroll-form">
	<section class="seven columns alpha">
<?php
if (is_array($records)) {
  $item_types_name = '';
  


  foreach ($records as $field) {
    if ($item_types_name != $field['item_types_name']) {
      if ($item_types_name != '') {
        echo('</div>'); // loses <div class="element_set_area"> if a previous one was opened (skips the first)
      }  
      $item_types_name = $field['item_types_name'];
      echo('<div class="item_types_name_area"><h3>'.$field['item_types_name'].'</h3>'); 
    }
    //print_r($field);
    echo('<div class="field-group '.even_or_odd().'">');
    echo('<input type="hidden" name="ids[]" value="'.$field['id'].'">');
    echo('<div class="field">');
    echo('<input type="hidden" name="vocatroll-name-original-value-'.$field['id'].'" value="'.$field['name'].'">');
    echo('<input type="hidden" name="vocatroll-element_id-'.$field['id'].'" value="'.$field['element_id'].'">');
    echo('<input type="hidden" name="vocatroll-element_set_id-'.$field['id'].'" value="'.$field['element_set_id'].'">');
    echo('<input type="hidden" name="vocatroll-item_type_id-'.$field['id'].'" value="'.$field['item_type_id'].'">');
    echo('<div class="two columns alpha"><label for="vocatroll-name-'.$field['id'].'">Field Name</label></div>');
    echo('<div class="inputs five columns omega"><input type="text" name="vocatroll-name-'.$field['id'].'" id="vocatroll-name-'.$field['id'].'" value="'.$field['name'].'"></div></div>');

    echo('<div class="field">');
    echo('<input type="hidden" name="vocatroll-description-original-value-'.$field['id'].'" value="'.$field['description'].'">');
    echo('<div class="two columns alpha"><label for="vocatroll-description-'.$field['id'].'">Description</label></div>');
    echo('<div class="inputs five columns omega"><input type="text" name="vocatroll-description-'.$field['id'].'" id="vocatroll-description-'.$field['id'].'" value="'.$field['description'].'"></div></div>');

    echo('<div class="field">');
    echo('<input type="hidden" name="vocatroll-type-original-value-'.$field['id'].'" value="'.$field['type'].'">');
    echo('<div class="two columns alpha"><label for="vocatroll-type-'.$field['id'].'">Type</label></div>');
    echo('<div class="inputs five columns omega"><select name="vocatroll-type-'.$field['id'].'">'.populate_type_options($field['type']).'</select></div></div>');

    echo('<div class="field">');
    echo('<input type="hidden" name="vocatroll-options-original-value-'.$field['id'].'" value="'.$field['options'].'">');
    echo('<div class="two columns alpha"><label for="vocatroll-options-'.$field['id'].'">Options</label></div>');
    echo('<div class="inputs five columns omega"><textarea name="vocatroll-options-'.$field['id'].'">'.$field['options'].'</textarea></div></div>');

    echo('</div>'); // field-group
  }
  echo('</div>'); // the last <div class="element_set_area"> is not closed by anything
}

?>

	</section>
	<section class="three columns omega">
        <div id="save" class="panel">
			<input type="submit" name="save_fields" id="save_fields" value="Save Changes" class="submit big green button">
		</div>
	</section>
	
	
	</form>

</div>

<?php echo foot(); ?>
