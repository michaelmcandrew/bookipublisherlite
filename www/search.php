<?php

require_once('../book.class.php');
require_once('../publisher.class.php');
$book = new Book;

$menu='<div id="menu"><ul>';
$menu .= "<li class='booki-section'>CiviCRM books</li>";
foreach($book->definitions as $definition){
    $menu .= "<li><a href='/{$definition['name']}'>{$definition['short-title']}</a></li>";
}
$menu .= '</ul></div>';



$replacements = array(
    'title' => "CiviCRM book search",
    'header' => "<a href='/'>CiviCRM books</a>",
    'menu' => $menu,
    'footer' => 'CiviCRM documentation is community driven. You can <a href="http://civicrm.org/participate/documentation">help improve documentation.</a>',
    'content' => '<h1>Search books</h1><gcse:searchbox-only></gcse:searchbox-only><gcse:searchresults-only></gcse:searchresults-only>'
    
);

echo Publisher::encodeTemplate(dirname(__FILE__).'/../plain.template.html', $replacements);

