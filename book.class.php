<?php

class Book{
    
    var $id;
    
    var $definitions = array(
        'user' => array(
            'name'=>'user',
        	'booki-name'=>'civicrm',
        	'title'=>'User and administrator guide',
        	'full-title'=>'CiviCRM user and administrator guide',
    ),
        
        
        'developer' => array(
        	'name'=>'developer',
        	'booki-name'=>'civicrm-developer-guide',
        	'title'=>'Developer guide',
        	'full-title'=>'CiviCRM developer guide',
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
    function set($id){
        foreach ($this->definitions[$id] +
        array(
            'version' => $this->getVersion(),
            'date' => $this->getDate(),
            'full-id' => $this->getFullId()
        ) as $name => $value){
            $this->$name = $value;
        };
    }
}

?>