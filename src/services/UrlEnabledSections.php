<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbaseuris\services;

use barrelstrength\sproutbaseuris\base\UrlEnabledSectionType;
use barrelstrength\sproutbaseuris\events\RegisterUrlEnabledSectionTypesEvent;
use barrelstrength\sproutbaseuris\sectiontypes\Category;
use barrelstrength\sproutbaseuris\models\UrlEnabledSection;
use barrelstrength\sproutbaseuris\sectiontypes\Entry;
use barrelstrength\sproutbaseuris\sectiontypes\NoSection;
use barrelstrength\sproutbaseuris\sectiontypes\Product;

use Craft;
use yii\base\Component;

/**
 *
 * @property mixed                                                       $matchedElementVariables
 * @property \barrelstrength\sproutbaseuris\base\UrlEnabledSectionType[] $registeredUrlEnabledSectionsEvent
 */
class UrlEnabledSections extends Component
{
    const EVENT_REGISTER_URL_ENABLED_SECTION_TYPES = 'registerUrlEnabledSectionTypesEvent';

    /**
     * @var
     */
    public $urlEnabledSectionTypes;

    /**
     * Returns all registered Url-Enabled Section Types
     *
     * @return UrlEnabledSectionType[]
     */
    public function getRegisteredUrlEnabledSectionsEvent()
    {
        $urlEnabledSectionTypes = [
            Entry::class,
            Category::class,
            NoSection::class
        ];

        if (Craft::$app->getPlugins()->getPlugin('commerce')) {
            $urlEnabledSectionTypes[] = Product::class;
        }

        $event = new RegisterUrlEnabledSectionTypesEvent([
            'urlEnabledSectionTypes' => $urlEnabledSectionTypes
        ]);

        $this->trigger(self::EVENT_REGISTER_URL_ENABLED_SECTION_TYPES, $event);

        return $event->urlEnabledSectionTypes;
    }

    /**
     * @return array
     */
    public function getUrlEnabledSectionTypes()
    {
        $urlEnabledSectionTypes = $this->getRegisteredUrlEnabledSectionsEvent();

        $urlEnabledSections = [];

        foreach ($urlEnabledSectionTypes as $urlEnabledSectionType) {
            $urlEnabledSections[] = new $urlEnabledSectionType();
        }

        return $urlEnabledSections;
    }

    /**
     * @return array
     */
    public function getMatchedElementVariables()
    {
        $urlEnabledSections = $this->getUrlEnabledSectionTypes();

        $matchedElementVariables = [];

        foreach ($urlEnabledSections as $urlEnabledSection) {
            $matchedElementVariables[] = $urlEnabledSection->getMatchedElementVariable();
        }

        return array_filter($matchedElementVariables);
    }

    /**
     * Get the active URL-Enabled Section Type via the Element Type
     *
     * @param $elementType
     *
     * @return mixed|null
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getUrlEnabledSectionTypeByElementType($elementType)
    {
        $currentSite = Craft::$app->sites->getCurrentSite();
        $this->prepareUrlEnabledSectionTypesForMetadataField($currentSite->id);

        foreach ($this->urlEnabledSectionTypes as $urlEnabledSectionType) {

            if ($urlEnabledSectionType->getElementType() == $elementType) {
                return $urlEnabledSectionType;
            }
        }

        return null;
    }

    /**
     * @param $siteId
     */
    public function prepareUrlEnabledSectionTypesForMetadataField($siteId)
    {
        $registeredUrlEnabledSectionsTypes = $this->getRegisteredUrlEnabledSectionsEvent();

        foreach ($registeredUrlEnabledSectionsTypes as $urlEnabledSectionType) {
            /**
             * @var UrlEnabledSectionType $urlEnabledSectionType
             */
            $urlEnabledSectionType = new $urlEnabledSectionType();
            $allUrlEnabledSections = $urlEnabledSectionType->getAllUrlEnabledSections($siteId);
            $urlEnabledSections = [];
            /**
             * @var UrlEnabledSection $urlEnabledSection
             */
            foreach ($allUrlEnabledSections as $urlEnabledSection) {
                $uniqueKey = $urlEnabledSectionType->getId().'-'.$urlEnabledSection->id;
                $model = new UrlEnabledSection();

                $model->type = $urlEnabledSectionType;
                $model->id = $urlEnabledSection->id;
                $urlEnabledSections[$uniqueKey] = $model;
            }
            $urlEnabledSectionType->urlEnabledSections = $urlEnabledSections;
            $this->urlEnabledSectionTypes[$urlEnabledSectionType->getId()] = $urlEnabledSectionType;
        }
    }
}
