<?php

require 'vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

$driver = RemoteWebDriver::create(
    'http://localhost:9515',
    DesiredCapabilities::chrome()
);

$temp_url = "https://bdjobs.com/h/jobs?lang=en&fcatId=";
$file = fopen("links.txt", "w");

for($i=0;$i<100;$i++){

    $driver->get($temp_url.$i);
    $current_page_url = $driver->getCurrentURL();
    fwrite($file,  $current_page_url."\n");

    sleep(5);


}
fclose($file);

echo "Press Enter to close browser...";



// $links = $driver->findElements(
//     WebDriverBy::cssSelector('.w-full a')
// );




fgets(STDIN);


?>




