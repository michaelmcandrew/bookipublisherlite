<?php

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
        echo "Will be available at {$this->bookVars['name']}/source/{$this->bookVars['version']}-{$this->downloadTime}\n";
        // ...creating the publish directory

        $this->baseDir = dirname(__FILE__);
        $this->publishDir = "{$this->baseDir}/www/{$this->bookVars['name']}/source/{$this->bookVars['version']}-{$this->downloadTime}";
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
            $queryParams['html_template'] = $this->encodeTemplate("{$this->baseDir}/template.html", $this->bookVars);
        }
        if($type == 'pdf'){
            $queryParams['booksize'] = 'A4';
        }
        $url = "http://{$this->objaviHost}/?".http_build_query($queryParams);
                
        // call the URL that will produce the book
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $contents = curl_exec($ch);
        curl_close($ch);

        // find the download link in the page                
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

        echo "Cleaning html\n";

        echo "* defining new directory structure, links, etc.\n";

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
        		$chapters[$link->href]['new_path'] = "{$sections[$section]['path']}/{$chapters[$link->href]['name']}/index.php";
        		$chapters[$link->href]['new_link'] = "{$sections[$section]['path']}/{$chapters[$link->href]['name']}";
        		$chapters[$link->href]['title'] = "{$link->innertext} | {$sections[$section]['title']} | {$this->bookVars['title']}";
        		$chapters[$link->href]['prev_link'] = $previous_page;
        		$chapters[$link->href]['next_link'] = '';
        		$chapters[$previous_page]['next_link'] = $link->href;

        		//get ready for the next loop by setting the current $link->href to $previous_page
        		$previous_page = $link->href;
            }
          }
        }
        $contents->clear();
        
        //delete the first $chapters[$previous_page] that was created when $previous_page was not initialized
        unset($chapters['']);

        //create all section and chapter directories
        echo "* creating section directories\n";
        foreach ($chapters as $chapter){
        	exec("mkdir -p {$this->publishDir}/{$chapter['new_link']}");
        }
        echo "* editing HTML (links and images)\n";
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
            $html->clear();
        	
        	$html = file_get_html("{$this->publishDir}/{$chapter['old_path']}");	
        	//change links
        	foreach ($html->find('a') as $a){
        		if(in_array($a->href, array_keys($chapters))){
        			//add current class
        			if($chapter['old_path']==$a->href){
        				$a->class='current';			
        			}
        			$a->href='../../'.$chapters[$a->href]['new_link'];
        		}
        	}
        	foreach ($html->find('img') as $a){
        		$a->src='../../'.$a->src;
        	}
        	//update files
        	file_put_contents("{$this->publishDir}/{$chapter['old_path']}", $html);
            $html->clear();

        	//move all files to their new place	
        	exec("mv {$this->publishDir}/{$chapter['old_path']} {$this->publishDir}/{$chapter['new_path']}");
        }

        //create an index.php which is a copy of the first chapter (following same pattern as above)
        echo "* making index file\n";
        $first_chapter=reset($chapters);
        exec("cp {$this->publishDir}/{$first_chapter['new_path']} {$this->publishDir}/index.php");
        $html = file_get_html("{$this->publishDir}/index.php");
        foreach ($html->find('a') as $a){
        	if(substr($a->href, 0, 6)=='../../'){
        		$a->href=substr($a->href, 6);		
        	}
        }
        foreach ($html->find('img') as $img){
        	$img->src=substr($img->src, 6);
        }
        file_put_contents("{$this->publishDir}/index.php", $html);
        $html->clear();

        // a function to make nice looking urls 
    }
    
    function urlize($string){
    	return strtolower(preg_replace(array("/[^a-zA-Z0-9\s]/", "/\h/"), array("", "-"), $string));
    }
    
    function encodeTemplate($template_file, $vars){
        $template=file_get_contents($template_file);
        $x=0;
        foreach($vars as $s=>$r){
        	$search[$x++]='{{{'.$s.'}}}';
        	$replace[$x]=$r;
        }
        return str_replace($search , $replace, $template);        
    }    
    
}