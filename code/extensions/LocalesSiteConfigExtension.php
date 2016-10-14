<?php

class LocalesSiteConfigExtension extends DataExtension
{
    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab(
            "Root.Locales",
            ListboxField::create(
                'SiteLocales',
                $this->owner->fieldLabel('SiteLocales'),
                i18n::get_common_locales()
            )
                ->setMultiple(true)
                ->setValue(SiteLocaleConfig::inst()->getAllowedLocales())
                ->setAttribute('data-placeholder', 'Click to select locale(s)')
        );
    }

    public function onBeforeWrite()
    {
        /*
         * Save locales to lcales.yml config file.
         */
        if (($locales = $this->owner->SiteLocales)
            && ($localesArray = explode(',', $locales))
            && ($config = SiteLocaleConfig::inst())
        ) {
            $oldLocales = $config->getAllowedLocales();
            $config->setAllowedLocales($localesArray);

            /*
             * If the locales have been changed: We need to trigger a /dev/build to activate the changed locales
             */
            if($localesArray !== $oldLocales) {
                // update allowed_locales before DB build.
                $config->initiateAllowedSiteLocales();

                // update DB schema if needed
                (new DatabaseAdmin())->doBuild(true);
            }
        }
    }

    public function updateFieldLabels(&$labels) {
        $labels['SiteLocales'] = _t('SiteLocales.SiteLocalesField', 'Allowed Site Locales');
    }
}