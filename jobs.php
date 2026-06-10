<?php

require 'vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

$driver = RemoteWebDriver::create(
    'http://localhost:9515',
    DesiredCapabilities::chrome()
);

$file = fopen("elements.txt", "w");
$lines = file("links.txt");
$count = 1;

foreach ($lines as $line){
    $temp_url = trim($line);
    if (empty($temp_url)) {
        continue;
    }

    $driver->get($temp_url);
    
    try {
        $driver->wait(10)->until(
            WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
                WebDriverBy::cssSelector('div.relative.w-full.h-full.flex.flex-col')
            )
        );
        $cards = $driver->findElements(
            WebDriverBy::cssSelector('div.relative.w-full.h-full.flex.flex-col')
        );

        foreach ($cards as $card)
        {
            try {
                $titleElement = $card->findElement(WebDriverBy::cssSelector('[data-testid="job-title"]'));
                $title = trim($titleElement->getText());
            } catch (\Exception $e) {
                $title = "";
            }
            if (empty($title)) {
                continue;
            }
            try {
                $details = trim($card->getText());
                $details = str_replace($title, '', $details);
                $details = trim($details);
                $details = preg_replace("/\n+/", "\n", $details);
            } catch (\Exception $e) {
                $details = "No details found";
            }
            fwrite($file, $count . ': ' . $title . PHP_EOL);
            fwrite($file, $details . PHP_EOL);
            
            $count++;
        }
    } catch (\Exception $e) {
        continue;
    }
}

fclose($file);
$driver->quit();