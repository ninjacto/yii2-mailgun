<?php

namespace tests\codeception\owner\_pages;

use yii\codeception\BasePage;

/**
 * Represents about page
 * @property \codeception_owner\AcceptanceTester|\codeception_owner\FunctionalTester $actor
 */
class AboutPage extends BasePage
{
    public $route = 'site/about';
}
