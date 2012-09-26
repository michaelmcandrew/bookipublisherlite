<?php

require_once('../book.class.php');
require_once('../publisher.class.php');
$book = new Book;
$menu='<div id="menu"><ul>';
$menu .= "<li class='booki-section'>CiviCRM books</li>";

foreach($book->definitions as $definition){
    $menu .= "<li><a href='/{$definition['name']}'>{$definition['title']}</a></li>";
}
$menu .= '</ul></div>';

$replacements = array(
    'title' => "Search CiviCRM books",
    'header' => "CiviCRM books",
    'menu' => $menu,
    'footer' => 'CiviCRM documentation is community driven. You can <a href="http://civicrm.org/participate/documentation">help improve documentation.</a>',
    'content' => '<gcse:searchresults-only></gcse:searchresults-only>'
    
);

echo Publisher::encodeTemplate(dirname(__FILE__).'/../plain.template.html', $replacements);

