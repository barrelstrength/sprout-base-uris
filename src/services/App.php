<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbaseuris\services;

use craft\base\Component;

class App extends Component
{
    /**
     * @var UrlEnabledSections
     */
    public $urlEnabledSections;

    public function init()
    {
        $this->urlEnabledSections = new UrlEnabledSections();
    }
}