<?php
$types=array('pdf','epub');
foreach($types as $type){
    $file[$type]="{$full_id}.{$type}";
    while(!file_exists($file[$type])){
        $file[$type] = "../{$file[$type]}";
    }
}
 ?>
<h2>Download this book</h2>
<div id='formats'>
<div id="pdf"><a href="<?php echo $file['pdf']; ?>"><img src="/download.png"> download as pdf</a></div>
<div id="epub"><a href="<?php echo $file['epub']; ?>"><img src="/download.png"> download as ePub</a></div>
</div>
<hr />
<h2>Get involved</h2>
<p>CiviCRM documentation is community driven. You can <a href="http://civicrm.org/participate/documentation">help improve documentation</a>.</p>
<hr />
<p>Powered by <a href="http://www.flossmanuals.org">Flossmanuals</a>.</p>
