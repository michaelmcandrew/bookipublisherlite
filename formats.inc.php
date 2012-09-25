<?php
$types=array('pdf','epub');
foreach($types as $type){
    $file[$type]="{$full_id}.{$type}";
    while(!file_exists($file[$type])){
        $file[$type] = "../{$file[$type]}";
    }
}
 ?>
<h2>Download this manual</h2>
<div id='formats'>
<div id="pdf"><a href="<?php echo $file['pdf']; ?>"><img src="/download.png"> download as pdf</a></div>
<div id="epub"><a href="<?php echo $file['epub']; ?>"><img src="/download.png"> download as ePub</a></div>
</div>

