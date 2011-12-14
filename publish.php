<?php

//configuration variables (change me!)
$booki_host = 'booki.flossmanuals.net';
$objavi_host = 'objavi.booki.cc';
$template_file = 'template.html';


//book definitions
$book_definitions['user']=array(
	'dir_name'=>'user',
	'booki_name'=>'civicrm',
	'name'=>'CiviCRM user and administrator guide',
	'edition'=>'4th edition',
	'civicrm-version'=>'4.1');
$book_definitions['developer']=array(
	'dir_name'=>'developer',
	'booki_name'=>'civicrm-developer-guide',
	'name'=>'CiviCRM developer guide',
	'edition'=>'1st edition',
	'civicrm-version'=>'4.1');
	
if(isset($argv[1]) && array_key_exists($argv[1], $book_definitions)){
	$book = $argv[1];
} else {
	$book = 'user';
}
$booki_name=$book_definitions[$book]['booki_name'];


//encode the template that we want to use
$template=file_get_contents($template_file);
foreach($book_definitions[$book] as $s=>$r){
	$search[$x++]='{{{'.$s.'}}}';
	$replace[$x]=$r;
}
$template = str_replace($search , $replace , $template);
$template_encoded=rawurlencode($template);

//write the URL that will be used to publish our book
$url="http://{$objavi_host}/?book={$booki_name}&server={$booki_host}&mode=templated_html&html_template={$template_encoded}";

//call the URL
echo "Publishing $booki_name at $objavi_host\n";
$contents=file_get_contents($url);

//find the name of the book that will be published
preg_match('#href="/books/(.+)">#', $contents, $matches);
$book_edition = $matches[1];
$book_url="http://{$objavi_host}/books/{$book_edition}.tar.gz";
echo "Created book at $book_url\n";

//download a copy of the tar.gz to the source directory
$source_directory = dirname(__FILE__)."/www/{$book_definitions[$book]['dir_name']}/source";
$book_directory = "{$source_directory}/{$book_edition}";
$book_tar = "{$book_directory}.tar.gz";
file_put_contents($book_tar, file_get_contents($book_url));

//untar the book into the source directory
shell_exec("mkdir -p {$book_directory}");
shell_exec("tar -xf {$book_tar} -C {$source_directory}");
echo "Finished! - Edition $book_edition of $book was created by $objavi_host using the source at $booki_host and downloaded to {$book_directory}\n";
