<?php
$myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
flock($myfile, LOCK_EX );
$txt = "Mickey Mouse\n";
fwrite($myfile, $txt);
$txt = "Minnie Mouse\n";
fwrite($myfile, $txt);
while(true) {

}
fclose($myfile);
?>