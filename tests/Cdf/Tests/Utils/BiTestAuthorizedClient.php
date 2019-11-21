<?php

namespace Cdf\BiCoreBundle\Tests\Utils;

use Symfony\Component\Panther\PantherTestCase;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

abstract class BiTestAuthorizedClient extends PantherTestCase
{

    const TIMEOUT = 4;

    /* @var $em \Doctrine\ORM\EntityManager */

    protected $em;

    protected function setUp(): void
    {
        //$client = static::createPantherClient();
        //$this->em = $container->get('doctrine')->getManager();
        $this->autologin();
    }

    protected function getRoute($name, $variables = array(), $absolutepath = false)
    {
        if ($absolutepath) {
            $absolutepath = \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL;
        }

        $container = static::createClient()->getContainer();
        return $container->get('router')->generate($name, $variables, $absolutepath);
    }

    public function getCurrentPage()
    {
        return static::createPantherClient();
    }

    public function getSession()
    {
        return static::createPantherClient();
    }

    public function getCurrentPageContent()
    {

        return static::createPantherClient()->getPageSource();
    }

    public function visit($url)
    {
        static::createPantherClient()->request('GET', $url);
    }

    public function autologin()
    {
        $client = static::createPantherClient();
        $container = static::createClient()->getContainer();
        $username4test = $container->getParameter('bi_core.admin4test');
        $password4test = $container->getParameter('bi_core.adminpwd4test');

        $client->request('GET', '/');
        $client->waitFor('#Login');
        $this->login($username4test, $password4test);
    }

    public function login($user, $pass)
    {
        $this->fillField('username', $user);
        $this->fillField('password', $pass);
        $this->pressButton('_submit');
    }

    public function evaluateScript($script)
    {
        return static::createPantherClient()->executeScript($script, array());
    }

    public function executeScript($script)
    {
        return $this->evaluateScript($script);
    }

    public function find($selector, $value, $timeout = self::TIMEOUT)
    {
        $e = new \Exception('Impossibile trovare ' . $selector);
        $i = 0;
        while ($i < $timeout) {
            try {
                $page = $this->getCurrentPage();
                $element = $page->find($selector, $value);
                if (!$element || (!$element->isVisible())) {
                    ++$i;
                    sleep(1);
                    continue;
                }

                return $element;
            } catch (\Exception $e) {
                ++$i;
                sleep(1);
            }
        }
        //$this->screenShot();
        throw($e);
    }

    private function getElementBySelector($selector)
    {
        return $this->getElementByWebDriverBy($selector);
    }

    private function getElementByWebDriver($webdriverby)
    {

        $elements = static::createPantherClient()->findElements($webdriverby);
        if (0 === count($elements)) {
            return null;
        } else {
            return $elements[0];
        }
    }

    private function getElementByWebDriverBy($selector)
    {
        $element = $this->getElementByWebDriver(WebDriverBy::id($selector));
        if ($element) {
            return $element;
        }
        $element = $this->getElementByWebDriver(WebDriverBy::cssSelector($selector));
        if ($element) {
            return $element;
        }
        $element = $this->getElementByWebDriver(WebDriverBy::name($selector));
        if ($element) {
            return $element;
        }
        $element = $this->getElementByWebDriver(WebDriverBy::className($selector));
        if ($element) {
            return $element;
        }
        $element = $this->getElementByWebDriver(WebDriverBy::linkText($selector));
        if ($element) {
            return $element;
        }
        $element = $this->getElementByWebDriver(WebDriverBy::xpath($selector));
        if ($element) {
            return $element;
        }
        $element = $this->getElementByWebDriver(WebDriverBy::tagName($selector));
        if ($element) {
            return $element;
        }

        return null;
    }

    private function getWebDriverBy($selector)
    {
        $element = $this->getElementByWebDriver(WebDriverBy::id($selector));
        if ($element) {
            return WebDriverBy::id($selector);
        }
        $element = $this->getElementByWebDriver(WebDriverBy::cssSelector($selector));
        if ($element) {
            return WebDriverBy::cssSelector($selector);
        }
        $element = $this->getElementByWebDriver(WebDriverBy::name($selector));
        if ($element) {
            return WebDriverBy::name($selector);
        }
        $element = $this->getElementByWebDriver(WebDriverBy::className($selector));
        if ($element) {
            return WebDriverBy::className($selector);
        }
        $element = $this->getElementByWebDriver(WebDriverBy::linkText($selector));
        if ($element) {
            return WebDriverBy::linkText($selector);
        }
        $element = $this->getElementByWebDriver(WebDriverBy::xpath($selector));
        if ($element) {
            return WebDriverBy::xpath($selector);
        }
        $element = $this->getElementByWebDriver(WebDriverBy::tagName($selector));
        if ($element) {
            return WebDriverBy::tagName($selector);
        }

        return null;
    }

    public function findField($selector, $timeout = self::TIMEOUT)
    {
        $e = new \Exception('Impossibile trovare ' . $selector);
        $i = 0;
        while ($i < $timeout) {
            try {
                $element = $this->getElementBySelector($selector);
                /* @var $element Behat\Mink\Element\NodeElement */
                if (!$element) {
                    ++$i;
                    sleep(1);
                    continue;
                }

                return $element;
            } catch (\Exception $e) {
                ++$i;
                sleep(1);
            }
        }
        //$this->screenShot();
        throw($e);
    }

    public function fillField($selector, $value, $timeout = self::TIMEOUT)
    {
        $e = new \Exception('Impossibile trovare ' . $selector);
        $i = 0;
        while ($i < $timeout) {
            try {
                //$this->ajaxWait();
                $element = $this->findField($selector);
                if ($element) {
                    if (!$element->isEnabled() || !$element->isDisplayed()) {
                        ++$i;
                        sleep(1);
                    } else {
                        $element->clear();
                        $element->sendKeys($value);

                        return;
                    }
                }
                ++$i;
                sleep(1);
            } catch (\Facebook\WebDriver\Exception\InvalidElementStateException $e) {
                ++$i;
                sleep(1);
            } catch (\Exception $e) {
                ++$i;
                sleep(1);
            }
        }
        //$this->screenShot();
        throw($e);
    }

    public function checkboxSelect($selector, $value, $timeout = self::TIMEOUT)
    {
        $e = new \Exception('Impossibile trovare ' . $selector);
        $i = 0;
        while ($i < $timeout) {
            try {
                //$this->ajaxWait();
                $select = $this->findField($selector);
                if ($select) {
                    if ($select->isSelected() != $value) {
                        //dump($select->isSelected());
                        //dump($value);
                        $select->click();
                        //$select->click();
                    }

                    return;
                }
                ++$i;
                sleep(1);
            } catch (\Exception $e) {
                ++$i;
                sleep(1);
            }
        }
        //$this->screenShot();
        throw($e);
    }

    public function checkboxIsChecked($selector, $timeout = self::TIMEOUT)
    {
        $e = new \Exception('Impossibile trovare ' . $selector);
        $i = 0;
        while ($i < $timeout) {
            try {
                //$this->ajaxWait();
                $select = $this->findField($selector);
                if ($select) {
                    return $select->isSelected();
                }
                ++$i;
                sleep(1);
            } catch (\Exception $e) {
                ++$i;
                sleep(1);
            }
        }
        //$this->screenShot();
        throw($e);
    }

    public function selectFieldOption($selector, $value, $timeout = self::TIMEOUT)
    {
        $e = new \Exception('Impossibile trovare ' . $selector);
        $i = 0;
        while ($i < $timeout) {
            try {
                //$this->ajaxWait();
                $select = $this->findField($selector);
                if ($select) {
                    $select->findElement(WebDriverBy::cssSelector("option[value='" . $value . "']"))
                            ->click();

                    return;
                }
                ++$i;
                sleep(1);
            } catch (\Exception $e) {
                ++$i;
                sleep(1);
            }
        }
        //$this->screenShot();
        throw($e);
    }

    public function pressButton($selector, $timeout = self::TIMEOUT)
    {
        $e = new \Exception('Impossibile trovare ' . $selector);
        $i = 0;
        while ($i < $timeout) {
            try {
                //$this->ajaxWait();
                $button = $this->getElementBySelector($selector);
                if (!$button) {
                    ++$i;
                    sleep(1);
                    continue;
                }
                static::createPantherClient()->wait(10)->until(WebDriverExpectedCondition::elementToBeClickable($this->getWebDriverBy($selector)));
                $button->click();
                //$this->ajaxWait();

                return;
            } catch (\Exception $e) {
                ++$i;
                sleep(1);
            }
        }
        //$this->screenShot();
        throw($e);
    }

    public function clickLink($selector, $timeout = self::TIMEOUT)
    {
        $e = new \Exception('Impossibile trovare ' . $selector);
        $i = 0;
        while ($i < $timeout) {
            try {
                //$this->ajaxWait();
                $page = $this->getCurrentPage();
                $page->clickLink($selector);
                //$this->ajaxWait();

                return;
            } catch (\Exception $e) {
                ++$i;
                sleep(1);
            }
        }

        echo $page->getHtml();
        //$this->screenShot();
        throw($e);
    }

    public function elementIsVisible($selector, $timeout = self::TIMEOUT)
    {
        $i = 0;
        while ($i < $timeout) {
            try {
                //$this->ajaxWait();
                $element = $this->getElementBySelector($selector);
                if ($element) {
                    return $element->isDisplayed();
                } else {
                    ++$i;
                    sleep(1);
                }
//                $this->ajaxWait();
            } catch (\Exception $e) {
                ++$i;
                sleep(1);
            }
        }

        return false;
    }

    public function clickElement($selector, $timeout = self::TIMEOUT)
    {
        $e = new \Exception('Impossibile trovare ' . $selector);
        $i = 0;
        while ($i < $timeout) {
            try {
//                $this->ajaxWait();
                $element = $this->getElementBySelector($selector);
                if (!$element || !$this->elementIsVisible($selector)) {
                    ++$i;
                    sleep(1);
                    continue;
                }
                static::createPantherClient()->wait(10)->until(WebDriverExpectedCondition::elementToBeClickable($this->getWebDriverBy($selector)));
                $element->click();
//                $this->ajaxWait();

                return;
            } catch (\Exception $e) {
                ++$i;
                sleep(1);
            }
        }

        //$this->screenShot();
        throw($e);
    }

    public function dblClickElement($selector, $timeout = self::TIMEOUT)
    {
        $e = new \Exception('Impossibile trovare ' . $selector);
        $i = 0;
        while ($i < $timeout) {
            try {
//                $this->ajaxWait();
                $element = static::createPantherClient()->findElement($selector);
                if (!$element) {
                    ++$i;
                    sleep(1);
                    continue;
                }
                $element->doubleClick();
//                $this->ajaxWait();

                return;
            } catch (\Exception $e) {
                ++$i;
                sleep(1);
            }
        }

        echo $page->getHtml();
        //$this->screenShot();
        throw($e);
    }

    public function rightClickElement($selector, $timeout = self::TIMEOUT)
    {
        $i = 0;
        while ($i < $timeout) {
            try {
//                $this->ajaxWait();
                $element = $this->findField($selector);
                if (!$element) {
                    ++$i;
                    sleep(1);
                    continue;
                }
                static::createPantherClient()->getMouse()->contextClickTo($selector);

//                $this->ajaxWait();

                return true;
            } catch (\Exception $e) {
                ++$i;
                sleep(1);
            }
        }

        //$this->screenShot();

        return null;
    }

    public function screenShot()
    {
        /* $driver = $this->minkSession->getDriver();
          if (!($driver instanceof Selenium2Driver)) {
          if ($driver instanceof \Behat\Mink\Driver\ZombieDriver) {
          return;
          } else {
          $this->minkSession->getDriver()->getScreenshot();
          return;
          }
          } else {
          $screenShot = base64_decode($driver->getWebDriverSession()->screenshot());
          }

          $timeStamp = (new \DateTime())->getTimestamp();
          file_put_contents('/tmp/' . $timeStamp . '.png', $screenShot);
          file_put_contents('/tmp/' . $timeStamp . '.html', $this->getCurrentPageContent());
         */
    }

    /**
     * waitForAjax : wait for all ajax request to close.
     *
     * @param int $timeout  timeout in seconds
     * @param int $interval interval in miliseconds
     */
//    public function ajaxWait($timeout = 5, $interval = 200)
//    {
//        static::createPantherClient()->wait($timeout, $interval)->until(function () {
//            // jQuery: "jQuery.active" or $.active
//            // Prototype: "Ajax.activeRequestCount"
//            // Dojo: "dojo.io.XMLHTTPTransport.inFlight.length"
//            $condition = 'return ($.active == 0);';
//
//            return static::createPantherClient()->executeScript($condition);
//        });
//    }
    public function logout()
    {
        $this->visit('/logout');
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

}
