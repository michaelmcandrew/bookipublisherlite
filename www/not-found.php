<?php
$url=$_SERVER['REDIRECT_URL'];
$directory = opendir(dirname(__FILE__));
while ($file = readdir($directory)) {
	if(is_dir($file) && $file!='.' && $file!='..'){
		$books[]=$file;
	}
}
preg_match('#^/([^/]+)/#', $url, $matches);
//	print_r($matches);
$first_part_of_path=$matches[1];
if(in_array($first_part_of_path, $books)){
	$in_book=TRUE;
	$book=$first_part_of_path;
}
?>
<link href='/style.css' rel='stylesheet' type='text/css'>
<iframe scrolling="no" style="max-height: 35px; border: 0px; width: 100%; display: block;" src="http://civicrm.org/sites/civicrm.org/developers/index.html"></iframe>
<div id='header'><h1>CiviCRM books</h1>
</div>
<div id='content'>

<p><b><?php echo $url ?></b> was not found in any CiviCRM book.</p>
<p>It may have been renamed or moved or deleted.</p>
<p>You might be able to find it in an older version.</p>
	<?php if($in_book): ?>
	<p>Try <a href="http://book.civicrm.org/<?php echo $book ?>/version">book.civicrm.org/<?php echo $book ?>/version</a>.	
	<?php else: ?>
	<p>Go to <a href="http://book.civicrm.org">book.civicrm.org</a> and then visit the older versions for each book (you can find a link to these in the footer of each page).
	<?php endif; ?>
</div>