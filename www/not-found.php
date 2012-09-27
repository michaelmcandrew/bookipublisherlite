<?php

require_once('../book.class.php');
require_once('../publisher.class.php');
$book = new Book;

$url=$_SERVER['REDIRECT_URL'];
// $directory = opendir(dirname(__FILE__));
// while ($file = readdir($directory)) {
//  if(is_dir($file) && $file!='.' && $file!='..'){
//      $books[]=$file;
//  }
// }
// preg_match('#^/([^/]+)/#', $url, $matches);
// //   print_r($matches);
// $first_part_of_path=$matches[1];
// if(in_array($first_part_of_path, $books)){
//  $in_book=TRUE;
//  $book=$first_part_of_path;
// }
if(strlen($url)){
    $url=substr($url, 1);
    $url_in_brackets = "({$url})";    
}else{
    $url_in_brackets ='';
}

$content = "<p>That page <b>{$url_in_brackets}</b> was not found in any CiviCRM book.</p>
<p>It may have been <b>moved</b>, <b>renamed</b> or <b>deleted</b>. Try searching the current versions of the book.</p>
<gcse:searchbox-only></gcse:searchbox-only>";

$menu='<div id="menu"><ul>';
$menu .= "<li class='booki-section'>CiviCRM books</li>";
foreach($book->definitions as $definition){
    $menu .= "<li><a href='/{$definition['name']}'>{$definition['short-title']}</a></li>";
}
$menu .= '</ul></div>';


$replacements = array(
    'title' => "CiviCRM books",
    'header' => "<a href='/'>CiviCRM books</a>",
    'menu' => $menu,
    'content' => $content,
    'footer' => 'CiviCRM documentation is community driven. You can <a href="http://civicrm.org/participate/documentation">help improve documentation.</a>'
    
);

echo Publisher::encodeTemplate(dirname(__FILE__).'/../plain.template.html', $replacements);
?>

</div>