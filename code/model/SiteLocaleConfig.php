<?php

use Symfony\Component\Yaml\Yaml;

class SiteLocaleConfig
{
    /**
     * @var string
     */
    protected $file;

    /**
     * @todo If we can, replace next static & static methods with DI once that's in
     */
    protected static $instance;

    /**
     * Get the current active Config instance.
     *
     * Configs should not normally be manually created.
     * In general use you will use this method to obtain the current Config instance.
     *
     * @return SiteLocaleConfig
     */
    public static function inst()
    {
        if (!self::$instance) {
            self::$instance = new SiteLocaleConfig();
        }

        return self::$instance;
    }

    /**
     * SiteLocaleConfig constructor.
     */
    public function __construct()
    {
        $this->file = implode('/', [
            Director::baseFolder(),
            SITELOCALES_DIR,
            '_config',
            'locales.yml',
        ]);

        if (!file_exists($this->file)) {
            $this->writeConfigFile($this->file, null);
        }
    }

    public function initiateAllowedSiteLocales()
    {
        if ($locales = $this->getAllowedLocales()) {
            Translatable::set_allowed_locales($locales);

            if (class_exists('TranslatableDataObject')) {
                // remove default locale for TranslatableDataObject; need to keep the CMS working nicely.
                while (($key = array_search(Config::inst()->get('i18n', 'default_locale'), $locales)) !== false) {
                    unset($locales[$key]);
                }

                TranslatableDataObject::set_locales(array_values($locales));
            }
        }
    }

    public function getAllowedLocales()
    {
        $config = Yaml::parse(
            file_get_contents($this->file),
            $exceptionOnInvalidType = false,
            $objectSupport = true,
            $objectForMap = true
        );

        if ($config && is_object($config) && isset($config->sitelocales)) {
            $config->sitelocales[] = Config::inst()->get('i18n', 'default_locale');
            return $config->sitelocales;
        }
    }

    public function setAllowedLocales($locales)
    {
        if (is_array($locales) && count($locales)) {
            /*
             * Remove "default locale" from this list. Prevents double fields for the default locale @ CMS fields.
             */
            while (($key = array_search(Config::inst()->get('i18n', 'default_locale'), $locales)) !== false) {
                unset($locales[$key]);
            }

            $this->writeConfigFile($this->file, Yaml::dump([ 'sitelocales' => array_values($locales) ], 2, 2));
        }
    }

    protected function writeConfigFile($file, $content)
    {
        if (is_writable(dirname($file)) || is_writable($file)) {
            file_put_contents($file, $content);
        }
    }
}