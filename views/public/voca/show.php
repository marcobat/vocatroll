<?php



function generate_pseudo_uniqueid ($element,$elem) {
  return 'summary_'.preg_replace('/[^a-z0-9]+/', '', strtolower($element)).'_'.preg_replace('/[^a-z0-9]+/', '', strtolower($elem));
}

function order_element($results) {
  $coverage = array();
  foreach ($results as $c) {
    if (preg_match('/^[^\(]+\((\d{4})[^\)]+\)/',$c['element'],$match)) {
      $coverage[$match[1]] = $c['element'];
    } else if (preg_match('/^[^\(]+\(to (\d{4})[^\)]*\)/',$c['element'],$match)) {
      $coverage[$match[1]] = $c['element'];
    } else {
      $coverage[] = $c['element'];
    }
  }
  ksort($coverage);
  return $coverage;
}

$html = '';
if (is_array($results)) {
  foreach (order_element($results) as $elem) {
    if ($element == 'Coverage') {
      $title = 'Historical Eras';
      $facet = '38_s';
    } else {
      // Subject
      $title = 'Themes';
      $facet = '49_s';
    }
    $html .= '<div class="summary_elem summary_elem_'.strtolower($element).'" id="'.generate_pseudo_uniqueid($element,$elem).'"><p><a href="'.WEB_DIR.'/solr-search?facet='.$facet.':%22'.urlencode($elem).'%22">'.$elem.'</a></p></div>';
  }
}

echo head(array(
    'title' => $title,
    'bodyclass' => 'page vocatroll',
    'bodyid' => 'vocatroll_page',
)); 

?>
<div id="primary">

	<h3><?php echo($title); ?></h3>
	<div>
		<?php echo($html); ?>
	</div>
	
</div>
<div id="side-area">

</div> 

<?php echo foot(); ?>
