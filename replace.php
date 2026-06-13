<?php
$dir = new RecursiveDirectoryIterator('c:\xampp\htdocs\MIS_PROJECTS\StationaryShop');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/.*\.(php|html)$/', RegexIterator::GET_MATCH);

$count = 0;
foreach($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    
    $modified = false;
    
    if (strpos($content, 'Paperly') !== false) {
        $content = str_replace('Paperly', 'DechenShop', $content);
        $modified = true;
    }
    
    if (strpos($content, 'paperly') !== false) {
        $content = str_replace('paperly', 'dechenshop', $content);
        $modified = true;
    }
    
    if ($modified) {
        file_put_contents($path, $content);
        echo "Updated: $path\n";
        $count++;
    }
}
echo "Total files updated: $count\n";
?>
