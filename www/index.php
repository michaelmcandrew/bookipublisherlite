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
    'title' => "CiviCRM books",
    'header' => "CiviCRM books",
    'menu' => '',
    'content' => '
	<p>This site contains the set of CiviCRM books developed and maintained by CiviCRM community.<p>
	<h2><a href="/user">User and administrator guide</a></h2>
	<p>A guide to the set up and every day use of CiviCRM, including planning, configuration, everyday use, reporting and more.</p>
	<h2><a href="/developer">Developer guide</a></h2>
	<p>How to get started with CiviCRM development.  Suitable for those that want to extend and develop CiviCRM.</p>
	',
    'footer' => 'CiviCRM documentation is community driven. You can <a href="http://civicrm.org/participate/documentation">help improve documentation.</a>'
    
);

echo Publisher::encodeTemplate(dirname(__FILE__).'/../plain.template.html', $replacements);

// 
// 
// <html>
// <head>
//  <link rel="stylesheet" type="text/css" media="all" href="/style.css" />
//  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
//  <link href='http://fonts.googleapis.com/css?family=Francois+One|Oswald|Lato' rel='stylesheet' type='text/css'
// </head>
// 
// <body>
//  <iframe width="100%" height="35px" scrolling="no" src="http://civicrm.org/sites/civicrm.org/developers/index.html"></iframe>
//  <div id='header'><h1>CiviCRM books</h1></div>
//  <div id='main'>
//  <div id='content'>
//  This site contains the set of CiviCRM books developed and maintained by CiviCRM documentation community.
//  <h2><a href="/user">User and administrator guide</a></h2>
//  <p>A guide to the set up and every day use of CiviCRM, including planning, configuration, everyday use, reporting and more.</p>
//  <h2><a href="/developer">Developer guide</a></h2>
//  <p>How to get started with CiviCRM development.  Suitable for those that want to extend and develop CiviCRM.</p>
//  </div>
//  </div>
//  <div id='footer'>
//      <p>CiviCRM documentation is community driven. You can <a href="http://civicrm.org/participate/documentation">help improve documentation.</a></p>
//  </div>
// </body>
// 
// </html>