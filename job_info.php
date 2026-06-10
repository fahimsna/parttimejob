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
$linkFile = fopen("job_link.txt", "w");

$lines = file("links.txt");
$count = 1;
$seenJobs = [];

foreach ($lines as $line)
{
    $temp_url = trim($line);

    if (empty($temp_url))
    {
        continue;
    }

    $driver->get($temp_url);

    while (true)
    {
        try
        {
            $driver->wait(10)->until(
                WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
                    WebDriverBy::cssSelector(
                        'div.relative.w-full.h-full.flex.flex-col'
                    )
                )
            );

            $cards = $driver->findElements(
                WebDriverBy::cssSelector(
                    'div.relative.w-full.h-full.flex.flex-col'
                )
            );

            $totalCards = count($cards);

            $originalWindow = $driver->getWindowHandle();

            for ($i = 0; $i < $totalCards; $i++)
            {
                $currentCards = $driver->findElements(
                    WebDriverBy::cssSelector(
                        'div.relative.w-full.h-full.flex.flex-col'
                    )
                );

                if (!isset($currentCards[$i]))
                {
                    continue;
                }

                $card = $currentCards[$i];

                $title = "";
                $titleElement = null;

                try
                {
                    $titleElement = $card->findElement(
                        WebDriverBy::cssSelector(
                            '[data-testid="job-title"]'
                        )
                    );

                    $title = trim($titleElement->getText());
                }
                catch (\Exception $e)
                {
                    $title = "";
                }

                if (empty($title) || $titleElement === null)
                {
                    continue;
                }

                try
                {
                    $details = trim($card->getText());

                    $details = str_replace($title, '', $details);

                    $details = trim($details);

                    $details = preg_replace("/\n+/", " | ", $details);

                    $details = preg_replace('/\s+/', ' ', $details);
                }
                catch (\Exception $e)
                {
                    $details = "No details found";
                }

                $jobFingerprint = md5($title . $details);

                if (in_array($jobFingerprint, $seenJobs))
                {
                    continue;
                }

                $jobUrl = "No link found";

                try
                {
                    $titleElement->click();

                    sleep(2);

                    $allWindows = $driver->getWindowHandles();

                    if (count($allWindows) > 1)
                    {
                        foreach ($allWindows as $window)
                        {
                            if ($window !== $originalWindow)
                            {
                                $driver->switchTo()->window($window);

                                $jobUrl = $driver->getCurrentURL();

                                $driver->close();

                                break;
                            }
                        }

                        $driver->switchTo()->window($originalWindow);
                    }
                    else
                    {
                        $jobUrl = $driver->getCurrentURL();

                        $driver->navigate()->back();

                        $driver->wait(10)->until(
                            WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
                                WebDriverBy::cssSelector(
                                    'div.relative.w-full.h-full.flex.flex-col'
                                )
                            )
                        );
                    }
                }
                catch (\Exception $clickEx)
                {
                    $jobUrl = "Error capturing URL";

                    try
                    {
                        $driver->switchTo()->window($originalWindow);
                    }
                    catch (\Exception $wEx)
                    {
                    }
                }

                $seenJobs[] = $jobFingerprint;

                fwrite(
                    $file,
                    $count .
                    " | " .
                    $title .
                    " | " .
                    $details .
                    PHP_EOL
                );

                fwrite(
                    $linkFile,
                    $count .
                    " | " .
                    $jobUrl .
                    PHP_EOL
                );

                echo $count . " -> " . $title . PHP_EOL;

                $count++;
            }

            try
            {
                $nextButton = $driver->findElement(
                    WebDriverBy::cssSelector(
                        'li.next a, a.page-link[aria-label="Next"], .icon-arrow-right'
                    )
                );

                if (
                    $nextButton->isDisplayed() &&
                    $nextButton->isEnabled()
                )
                {
                    $driver->executeScript(
                        "arguments[0].scrollIntoView(true);",
                        [$nextButton]
                    );

                    sleep(1);

                    $nextButton->click();

                    sleep(3);
                }
                else
                {
                    break;
                }
            }
            catch (\Exception $paginationEx)
            {
                break;
            }
        }
        catch (\Exception $e)
        {
            break;
        }
    }
}

fclose($file);
fclose($linkFile);

$driver->quit();

echo "Scraping completed!" . PHP_EOL;