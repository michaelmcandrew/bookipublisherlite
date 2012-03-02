<?php
require_once 'simpledom.inc.php';
ini_set('default_socket_timeout', 1200);    

//configuration variables (change me!)

$booki_host = 'booki.flossmanuals.net';
$objavi_host = 'objavi.booki.cc';
$template_file = 'template.html';


//book definitions
$book_definitions['user']=array(
	'dir_name'=>'user',
	'booki_name'=>'civicrm',
	'name'=>'CiviCRM user and administrator guide',
	'edition'=>'Fourth edition',
	'civicrm-version'=>'4.1',
	'published-date'=>'Aug 2011');
$book_definitions['developer']=array(
	'dir_name'=>'developer',
	'booki_name'=>'civicrm-developer-guide',
	'name'=>'CiviCRM developer guide',
	'edition'=>'First edition',
	'civicrm-version'=>'4.1',
	'published-date'=>'Aug 2011');
	
if(isset($argv[1]) && array_key_exists($argv[1], $book_definitions)){
	$book = $argv[1];
} else {
	$book = 'user';
}
$booki_name=$book_definitions[$book]['booki_name'];

//encode the template that we want to use
$template=file_get_contents($template_file);
$x=0;
foreach($book_definitions[$book] as $s=>$r){
	$search[$x++]='{{{'.$s.'}}}';
	$replace[$x]=$r;
}
$template = str_replace($search , $replace , $template);
$template_encoded=rawurlencode($template);

//write the URL that will be used to publish our book
$url="http://{$objavi_host}/?book={$booki_name}&server={$booki_host}&mode=templated_html&html_template={$template_encoded}";
//call the URL
"Publishing $booki_name at $objavi_host\n";
$contents=file_get_contents($url);

//find the name of the book that will be published
preg_match('#href(.+)books/(.+)">#', $contents, $matches);
$book_edition = $matches[2];
$book_url="http://{$objavi_host}/books/{$book_edition}.tar.gz";

//download a copy of the tar.gz to the source directory
echo "Downloading book from $book_url\n";
$source_dir = dirname(__FILE__)."/www/{$book_definitions[$book]['dir_name']}/source";
$book_dir = "{$source_dir}/{$book_edition}";
$book_tar = "{$book_dir}.tar.gz";
file_put_contents($book_tar, file_get_contents($book_url));

//untar the book into the source directory
shell_exec("mkdir -p {$book_dir}");
shell_exec("tar -xf {$book_tar} -C {$source_dir}");
echo "Edition $book_edition of $book was created by $objavi_host using the source at $booki_host and downloaded to {$book_dir}\n";


echo "Starting cleanup\n";

$contents = file_get_html("{$book_dir}/contents.html");

//make two arrays that gives us all the data we need to do the transformations
foreach($contents->find('ul[class=menu-goes-here]') as $toc)
{
  foreach($toc->find('li') as $chapter)
  {
    if($chapter->class=='booki-section') { // i.e. if this is a section
		$section=$chapter->innertext;
		$sections[$section]['title']=$chapter->innertext;
		$sections[$section]['path']=urlize($chapter->innertext);;
	}else{
		$link = $chapter->find('a',0);
		//do we need to create the chapters array?
		
		$chapters[$link->href]['old_path'] = $link->href;
		$chapters[$link->href]['section'] = $section;
		$chapters[$link->href]['name'] = urlize($link->innertext);
		$chapters[$link->href]['new_path'] = "{$sections[$section]['path']}/{$chapters[$link->href]['name']}";
		$chapters[$link->href]['title'] = "{$link->innertext} | {$sections[$section]['title']} | {$book_definitions[$book]['name']}";
		$chapters[$link->href]['prev_link'] = $previous_page;
		$chapters[$previous_page]['next_link'] = $link->href;

		//get ready for the next loop by setting the current $link->href to $previous_page
		$previous_page = $link->href;
    }
  }
}

//delete the first $chapters[$previous_page] that was created when $previous_page was not initialized
unset($chapters['']);


//create all section directories
echo "Creating section directories\n";
foreach ($sections as $section){
	shell_exec("mkdir $book_dir/{$section['path']}");
}

echo "Editing HTML (links and images)\n";
foreach ($chapters as $chapter){
	$html = file_get_html("{$book_dir}/{$chapter['old_path']}");
	$navs=$html->find('div[class=bc]');
	$links=array();
	if($chapter['prev_link']){
		$links[]="<a href='{$chapter['prev_link']}'>previous</a> ";
	}
	if($chapter['next_link']){
		$links[]="<a href='{$chapter['next_link']}'>next</a>";
	}
	$nav_links=implode(' | ', $links);
	foreach($navs as $nav){
		$nav->innertext = '$nav_links';
	}
	$html->find('div[class=section-breadcrumb]', 0)->innertext = $chapter['section'];;
	$html->find('title', 0)->innertext = $chapter['title'];
	
	$mikey=$html->find('div[class=sectionbreadcrumb]');
	
	//add the breadcrumb here
	
	// we need to save and load the dom again because we add some html elements and they need to get registered in the dom.  Maybe there is a html->refresh
	file_put_contents("{$book_dir}/{$chapter['old_path']}", $html);
	$html = file_get_html("{$book_dir}/{$chapter['old_path']}");	
	
	//change links
	foreach ($html->find('a') as $a){
		if(in_array($a->href, array_keys($chapters))){
			//add current class
			if($chapter['old_path']==$a->href){
				$a->class='current';			
			}
			$a->href='../'.$chapters[$a->href]['new_path'];
		}
	}
	foreach ($html->find('img') as $a){
		$a->src='../'.$a->src;
	}
	
	//update files
	file_put_contents("{$book_dir}/{$chapter['old_path']}", $html);

	//move all files to their new place	
	shell_exec("mv {$book_dir}/{$chapter['old_path']} {$book_dir}/{$chapter['new_path']}\n");
}

//create an index.php which is a copy of the first chapter (following same pattern as above)
echo "Making index file\n";
$first_chapter=reset($chapters);
shell_exec("cp {$book_dir}/{$first_chapter['new_path']} {$book_dir}/index.html\n");
$html = file_get_html("{$book_dir}/index.html");
foreach ($html->find('a') as $a){
	if(substr($a->href, 0, 3)=='../'){
		$a->href=substr($a->href, 3);		
	}
}
foreach ($html->find('img') as $img){
	$img->src=substr($img->src, 3);
}
file_put_contents("{$book_dir}/index.html", $html);
echo "Finished creating book at www/{$book_definitions[$book]['dir_name']}/source/{$book_edition}\n";

// a function to make nice looking urls 
function urlize($string){
	return strtolower(preg_replace(array("/[^a-zA-Z0-9\s]/", "/\h/"), array("", "-"), $string));
}

