<?php

require 'vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;

// 1. Setup Maximum Anti-Bot Bypassing Options
$options = new ChromeOptions();
$options->addArguments([
    '--disable-blink-features=AutomationControlled', 
    '--user-agent=Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    '--start-maximized',
    '--disable-infobars',
    '--disable-extensions'
]);

$options->setExperimentalOption('excludeSwitches', ['enable-automation']);
$options->setExperimentalOption('useAutomationExtension', false);

$capabilities = DesiredCapabilities::chrome();
$capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

echo "Initializing Chrome Instance...\n";
$driver = RemoteWebDriver::create('http://localhost:9515', $capabilities);

// 2. Open image.txt for saving image URLs
$imageFile = fopen("image.txt", "w");

// 3. Read lines from job_link.txt
$links = file("job_link.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
echo "Found " . count($links) . " links. Extracting REAL company logos...\n";

foreach ($links as $line) {
    $raw_line = trim($line);
    if (empty($raw_line)) {
        continue;
    }

    $httpPos = strpos($raw_line, 'http');
    if ($httpPos !== false) {
        $jobUrl = trim(substr($raw_line, $httpPos));
        $job_id = trim(substr($raw_line, 0, $httpPos));
        $job_id = rtrim($job_id, ':| '); 
    } else {
        continue;
    }

    $imageUrl = "No company logo found";

    if (!empty($jobUrl) && filter_var($jobUrl, FILTER_VALIDATE_URL)) {
        try {
            // Navigate to the job page
            $driver->get($jobUrl);
            
            // Allow time for images and frames to populate completely
            usleep(rand(2000000, 3000000)); 

            // --- FIRST TRY: CHECK FOR LOGOS IN MAIN PAGE AND IFRAMES ---
            // We define a checker function to keep the logic clean across parent window and iframes
            $getRealLogo = function($webDriver) {
                // Highly targeted selectors looking closely at Bdjobs' exact corporate profile blocks
                $selectors = [
                    'div.company-logo-wrapper img',
                    'div.logo-container img',
                    '.cmp-logo img',
                    '.company-logo img',
                    'img[src*="logo"]',
                    'img[src*="Logo"]',
                    'img[alt*="logo"]',
                    'img[alt*="Logo"]'
                ];

                foreach ($selectors as $selector) {
                    try {
                        $elements = $webDriver->findElements(WebDriverBy::cssSelector($selector));
                        foreach ($elements as $el) {
                            $src = $el->getAttribute('src');
                            
                            if (!empty($src)) {
                                $srcLower = strtolower($src);
                                
                                // STRICT FILTERS: Avoid Bdjobs UI assets, spacers, and GOOGLE ADS
                                if (strpos($srcLower, 'googlesyndication') === false && 
                                    strpos($srcLower, 'googleads') === false &&
                                    strpos($srcLower, 'bdjobslogo') === false && 
                                    strpos($srcLower, 'mybdjobs') === false &&
                                    strpos($srcLower, 'elearning') === false && 
                                    strpos($srcLower, 'spacer') === false && 
                                    strpos($srcLower, 'blank') === false) {
                                    
                                    return $src; // Found a valid company image path link!
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
                return null;
            };

            // 1. Try gathering from the main base window content layout
            $foundSrc = $getRealLogo($driver);

            // 2. If not found, look inside embedded iframes (common for third-party company designs)
            if (!$foundSrc) {
                $iframes = $driver->findElements(WebDriverBy::cssSelector('iframe'));
                foreach ($iframes as $iframe) {
                    try {
                        $driver->switchTo()->frame($iframe);
                        $foundSrc = $getRealLogo($driver);
                        $driver->switchTo()->defaultContent();
                        
                        if ($foundSrc) {
                            break; // Stop looking if the frame context returned a match
                        }
                    } catch (\Exception $frameEx) {
                        try { $driver->switchTo()->defaultContent(); } catch (\Exception $e) {}
                    }
                }
            } else {
                $driver->switchTo()->defaultContent();
            }

            if ($foundSrc) {
                $imageUrl = $foundSrc;
            }

        } catch (\Exception $e) {
            $imageUrl = "Timeout / Processing failed";
            try { $driver->switchTo()->defaultContent(); } catch (\Exception $ex) {}
        }
    } else {
        $imageUrl = "Invalid URL format";
    }

    // 4. Append to image.txt keeping IDs perfectly synced
    fwrite($imageFile, $job_id . ' | ' . $imageUrl . PHP_EOL);
    echo "Processed ID $job_id -> Valid Logo Checked.\n";
    
    usleep(rand(500000, 1000000));
}

// Clean up
fclose($imageFile);
$driver->quit();
echo "\nAll done! Check your updated image.txt file.\n";
?>