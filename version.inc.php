<?php
$pathElements = explode('/', $_SERVER['REQUEST_URI']);

require_once('book.class.php');
require_once('publisher.class.php');

exec('find . -type l | sort -r', $versions);

$lis='';
foreach($versions as $version){
    $link=substr($version, 2);
    $lis .= "<li><a href='{$version}'>version {$link}</a></li>";
}


$book = new Book;
$book->set($pathElements[1]);

$replacements = array(
    'title' => "Previous versions of the {$book->title}",
    'header' => "<a href='/'>CiviCRM books</a>",
    'menu' => "<div id='menu'><ul><li class='booki-section'>All editions</li>{$lis}</div>",
    'content' => "
        <p>We aim to publish two updates of all CiviCRM books a year.  These updates normally co-incide with a point release of CiviCRM. </p>
        <p>As you might expect, the latest version is most up to date.  If you are looking for a specific version of the <b>{$book->title}</b>, browse the list below:</p>
    {$lis}",
    'footer' => 'CiviCRM documentation is community driven. You can <a href="http://civicrm.org/participate/documentation">help improve documentation.</a>',
    
);

echo Publisher::encodeTemplate(dirname(__FILE__).'/plain.template.html', $replacements);

