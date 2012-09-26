<?php
$pathElements = explode('/', $_SERVER['REQUEST_URI']);

require_once('book.class.php');
require_once('publisher.class.php');

$book = new Book;
$book->set($pathElements[1]);

$replacements = array(
    'title' => "Previous versions of the '{$book->title}'",
    'header' => "CiviCRM books"
);

echo Publisher::encodeTemplate(dirname(__FILE__).'/plain.template.html', $replacements);

print_r($book->title);