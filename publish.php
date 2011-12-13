<?php

//configuration variables (change me!)
$book = 'civicrm';
$booki = 'booki.flossmanuals.net';
$objavi = 'http://objavi.booki.cc';
$template_file = 'template.html';

//encode the template that we want to use
$template=rawurlencode(file_get_contents($template_file));

//write the URL that will be used to publish our book
$url="{$objavi}/?book={$book}&server={$booki}&mode=templated_html&html_template={$template}";

//call the URL
echo "Publishing $book at $objavi\n";
$contents=file_get_contents($url);

//find the name of the book that will be published
preg_match('#href="/books/(.+)">#', $contents, $matches);
$book_edition = $matches[1];
$book_url="{$objavi}/books/{$book_edition}.tar.gz";
echo "Created book at $book_url\n";

//download a copy of the tar.gz to the source directory
$book_directory = dirname(__FILE__)."/www/source/{$book_edition}";
$book_tar = "{$book_directory}/{$book_edition}.tar.gz";
file_put_contents($book_tar, file_get_contents($book_url));

//untar the book into the source directory
shell_exec("mkdir {$book_directory}");
shell_exec("tar -xf {$book_tar} -C {$book_directory}");
echo "Finished! - Edition $book_edition of $book was created by $objavi using the source at $booki and downloaded to {$book_directory}\n";
