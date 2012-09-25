<?php

//initialise book

$book = new Book;
$book->version = 4.2;

//check book is defined

if(isset($argv[1]) && array_key_exists($argv[1], $book->definitions)){
	$book->id = $argv[1];
} else {
	exit("{$argv[1]} is not a defined book\n");
}

// initialise book publisher

$publisher = new Publisher($book->getAll());

//download files

$publisher->download('epub');
$publisher->download('pdf');
$publisher->download('html');

//massage html in online version

$publisher->massageHtml();

class Book{
    
    var $id;
    
    var $definitions = array(
        'user' => array(
            'name'=>'user',
        	'booki-name'=>'civicrm',
        	'title'=>'CiviCRM user and administrator guide',
    ),
        
        
        'developer' => array(
        	'name'=>'developer',
        	'booki-name'=>'civicrm-developer-guide',
        	'title'=>'CiviCRM developer guide',
        )
    );
    
    var $version;
        
    function getVersion(){
        return $this->version;
    }

    function getDate(){
        return date('M Y');
    }

    function getFullId(){
        $lowercasedate = strtolower(str_replace(' ', '-', $this->getDate()));
        return "civicrm-{$this->getVersion()}-{$this->id}-book-{$lowercasedate}";
    }

    function getAll(){
        return $this->definitions[$this->id] +
        array(
            'version' => $this->getVersion(),
            'date' => $this->getDate(),
            'full-id' => $this->getFullId()
        );
    }
}

class Publisher{
    
    var $bookiHost = 'booki.flossmanuals.net';

    var $objaviHost = 'objavi.booki.cc';
    
    var $bookVars = null;
    
    var $publishingMethods = array(
        'pdf' => array(
            'extension' => 'pdf',
            'mode' => 'book',
        ),
        'epub' => array(
            'extension' => 'epub',
            'mode' => 'epub',
        ),
        'html' => array(
            'extension' => 'tar.gz',
            'mode' => 'templated_html',
        ),
    );
    
    function __construct($bookVars){
        
        // initialise the publisher by importing some book variables and...
        
        $this->bookVars = $bookVars;
        $this->downloadTime = date('Y.m.d-h.i.s');
        echo "ID is {$this->downloadTime}\n";
        // ...creating the publish directory

        $this->baseDir = dirname(__FILE__);
        $this->publishDir = "{$this->baseDir}/www/{$this->bookVars['name']}/source/{$this->downloadTime}";
        exec("mkdir -p {$this->publishDir}");

    }
    
    function download($type){
        
        // create an appropriate download url for the book we want to download
        
        $queryParams = array(
            'book' => $this->bookVars['booki-name'],
            'title' => $this->bookVars['title'],
            'server' => $this->bookiHost,
            'mode' => $this->publishingMethods[$type]['mode'],
        );
        if($type == 'html'){
            $queryParams['html_template'] = $this->encodeTemplate('template.html');
        }
        if($type == 'book'){
            $queryParams['booksize'] = 'A4';
        }
                
        $url = "http://{$this->objaviHost}/?".http_build_query($queryParams);

        // find the download link in the page
                
        $contents=file_get_contents($url);
        preg_match('#href(.+)books/(.+)">#', $contents, $matches);

        $remoteFile = $matches[2];

        $extension = $this->publishingMethods[$type]['extension'];
        
        // if this is templated html, we need to download the tar.gz, but the link is to the dir, so lets add .tar.gz
        
        if($type == 'html'){
            $remoteFile .= ".{$extension}";
        }

        // download the url
        
        $remoteUrl = "http://{$this->objaviHost}/books/{$remoteFile}";
        $localFile = "{$this->publishDir}/{$this->bookVars['full-id']}.$extension";        
        file_put_contents($localFile, file_get_contents($remoteUrl));
        
        // if this is templated html, we need to untar the tar, and then delete the tar
        
        if($type == 'html'){
            
            // the --strip-components means that we remove the first directory, which is handy!
            
            exec("tar --strip-components 1 -xf {$localFile} -C {$this->publishDir}");
            exec("rm {$localFile}");
        }
        
    }
        
    function massageHtml(){


        require_once 'simpledom.inc.php';

        echo "Starting cleanup\n";

        $contents = file_get_html("{$this->publishDir}/contents.html");
        
        $previous_page = '';
        
        
        //make two arrays that gives us all the data we need to do the transformations
        foreach($contents->find('ul[class=menu-goes-here]') as $toc)
        {
          foreach($toc->find('li') as $chapter)
          {
            if($chapter->class=='booki-section') { // i.e. if this is a section
        		$section=$chapter->innertext;
        		$sections[$section]['title']=$chapter->innertext;
        		$sections[$section]['path']=$this->urlize($chapter->innertext);;
        	}else{
        		$link = $chapter->find('a',0);
        		//do we need to create the chapters array?

        		$chapters[$link->href]['old_path'] = $link->href;
        		$chapters[$link->href]['section'] = $section;
        		$chapters[$link->href]['name'] = $this->urlize($link->innertext);
        		$chapters[$link->href]['new_path'] = "{$sections[$section]['path']}/{$chapters[$link->href]['name']}.php";
        		$chapters[$link->href]['title'] = "{$link->innertext} | {$sections[$section]['title']} | {$this->bookVars['title']}";
        		$chapters[$link->href]['prev_link'] = $previous_page;
        		$chapters[$link->href]['next_link'] = '';
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
        	exec("mkdir {$this->publishDir}/{$section['path']}");
        }

        echo "Editing HTML (links and images)\n";
        foreach ($chapters as $chapter){
        	$html = file_get_html("{$this->publishDir}/{$chapter['old_path']}");
        	$navs=$html->find('div[class=nav]');
        	$links=array();
        	if($chapter['prev_link']){
        		$links[]="<a href='{$chapter['prev_link']}'>previous</a> ";
        	}
        	if($chapter['next_link']){
        		$links[]="<a href='{$chapter['next_link']}'>next</a>";
        	}
        	$nav_links=implode(' ', $links);
        	foreach($navs as $nav){
        		$nav->innertext = $nav_links;
        	}
        	$html->find('div[class=section-breadcrumb]', 0)->innertext = $chapter['section'];;
        	$html->find('title', 0)->innertext = $chapter['title'];

            $e = $html->find('ul[class=menu-goes-here]', 0)->find('li', 0);
            $e->class .=' first';

        	//add the breadcrumb here

        	// we need to save and load the dom again because we add some html elements and they need to get registered in the dom.  Maybe there is a html->refresh
        	file_put_contents("{$this->publishDir}/{$chapter['old_path']}", $html);
        	$html = file_get_html("{$this->publishDir}/{$chapter['old_path']}");	

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
        	file_put_contents("{$this->publishDir}/{$chapter['old_path']}", $html);

        	//move all files to their new place	
        	exec("mv {$this->publishDir}/{$chapter['old_path']} {$this->publishDir}/{$chapter['new_path']}");
        }

        //create an index.php which is a copy of the first chapter (following same pattern as above)
        echo "Making index file\n";
        $first_chapter=reset($chapters);
        exec("cp {$this->publishDir}/{$first_chapter['new_path']} {$this->publishDir}/index.php");
        $html = file_get_html("{$this->publishDir}/index.php");
        foreach ($html->find('a') as $a){
        	if(substr($a->href, 0, 3)=='../'){
        		$a->href=substr($a->href, 3);		
        	}
        }
        foreach ($html->find('img') as $img){
        	$img->src=substr($img->src, 3);
        }
        file_put_contents("{$this->publishDir}/index.php", $html);

        // a function to make nice looking urls 
    }
    
    function urlize($string){
    	return strtolower(preg_replace(array("/[^a-zA-Z0-9\s]/", "/\h/"), array("", "-"), $string));
    }
    
    function encodeTemplate($template_file){
        $template=file_get_contents("{$this->baseDir}/{$template_file}");
        $x=0;
        foreach($this->bookVars as $s=>$r){
        	$search[$x++]='{{{'.$s.'}}}';
        	$replace[$x]=$r;
        }
        return str_replace($search , $replace, $template);        
    }    
    
}



