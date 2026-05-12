<?php

namespace PHPMaker2026\Project1;

use Psr\Log\LoggerInterface;
use Dflydev\DotAccessData\Data;
use Symfony\Component\Finder\Finder;
use Symfony\Component\VarExporter\VarExporter;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Contracts\Translation\TranslatorInterface;
use NumberFormatter;
use IntlDateFormatter;
use ResourceBundle;
use Locale;
use RuntimeException;
use Exception;
use Throwable;

/**
 * Langauge class
 */
class Language
{
    public static bool $SortByName = false;
    public static bool $SortByCaseInsensitiveName = false;
    public static bool $SortBySize = false;
    public static bool $ReverseSorting = false;
    public Data $Data;
    public string $LanguageId = '';
    public string $Template = ''; // JsRender template
    public string $Method = 'prependTo'; // JsRender template method
    public string $Target = '.navbar-nav.ms-auto'; // JsRender template target
    public string $Type = 'LI'; // LI/DROPDOWN (for used with top Navbar) or SELECT/RADIO (NOT for used with top Navbar)

    // Constructor
    public function __construct(
        protected string $langFolder, // '%kernel.project_dir%/lang'
        protected string $cacheFolder, // '%kernel.project_dir%/translations'
        protected string $cacheFile, // 'messages.*.php'
        protected string $hashFolder, // '%kernel.cache_dir%/languages'
        protected LocaleSwitcher $localeSwitcher,
        protected TranslatorInterface $translator,
        protected LoggerInterface $logger,
    ) {
    }

    // Set language
    public function setLanguage(?string $langId = null): void
    {
        global $httpContext;
        $langId ??= Param('language');
        if ($langId) {
            $this->LanguageId = $langId;
            Session(SESSION_LANGUAGE_ID, $this->LanguageId);
        } elseif (Session(SESSION_LANGUAGE_ID) != '') {
            $this->LanguageId = Session(SESSION_LANGUAGE_ID);
        } else {
            $this->LanguageId = Config('DEFAULT_LANGUAGE_ID');
        }
        $httpContext['CurrentLanguage'] = $this->LanguageId;
        $this->loadLanguage($this->LanguageId);
        $this->setLocale($this->LanguageId);

        // Dispatch event
        DispatchEvent(new LanguageLoadEvent($this), LanguageLoadEvent::class);
        SetClientVar('languages', ['languages' => $this->getLanguages()]);
    }

    // Parse XML
    protected function parseXml(string $xml, mixed &$values): void
    {
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8'); // Always return in utf-8
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $xml, $values);
        $errorCode = xml_get_error_code($parser);
        if ($errorCode > 0) {
            throw new Exception(xml_error_string($errorCode));
        }
        xml_parser_free($parser);
    }

    /**
     * Load XML
     * <ew-language> // level 1
     *     <global> // level 2
     *         <phrase/> // level 3
     *         <extension> // level 3
     *             <phrase/> // level 4
     * @param string $xml XML
     * @return Data
     */
    protected function loadXml(string $xml): Data
    {
        $data = new Data();
        $xml = trim($xml);
        if (!$xml) {
            return $data;
        }
        $this->parseXml(trim($xml), $xmlValues);
        if (!is_array($xmlValues)) {
            return $data;
        }
        $tags = [];
        foreach ($xmlValues as $xmlValue) {
            $attributes = null; // Reset attributes first
            extract($xmlValue); // Extract as $tag (string), $type (string), $level (int) and $attributes (array)
            if ($level == 1) {
                continue; // Skip root tag
            }
            if ($type == 'open' || $type == 'complete') { // Open tag like '<tag ...>' or complete tag like '<tag/>'
                if ($attributes['id'] ?? false) { // Has 'id' attribute
                    $convert = fn ($id) => ($tags[2] ?? '') == 'global' && $level > 3 // Extension phrases
                        ? $id // Keep the id as camel case as JavaScript
                        : strtolower($id);
                    if ($type == 'open') {
                        $tag .= '.' . $convert($attributes['id']); // Convert id to lowercase
                    } elseif ($type == 'complete') { // <phrase/>
                        $tag = $convert($attributes['id']); // Convert id to lowercase
                    }
                    unset($attributes['id']);
                }
                $tags[$level] = $tag;
                if (is_array($attributes) && count($attributes) > 0 && $level > 1) {
                    $data->set(implode('.', array_filter(array_slice($tags, 0, $level - 1))), $attributes);
                }
            }
        }
        return $data;
    }

    // Load language file(s)
    public function loadLanguage(string $id): void
    {
        $cacheFile = str_replace('*', str_replace('-', '_', $id), PathJoin($this->cacheFolder, $this->cacheFile));
        $hashFile = str_replace('*', str_replace('-', '_', $id), PathJoin($this->hashFolder, $this->cacheFile . '.hash'));
        $finder = new Finder();
        $finder->files()->in($this->langFolder)->name("*.$id.xml");
        if (!$finder->hasResults()) {
            $this->logger->error("Missing language files for language ID '$id'");
            $finder->files()->in($this->langFolder)->name('*.en-US.xml');
        }

        // Sorting
        if (self::$SortBySize) {
            $finder->sortBySize();
        }
        if (self::$SortByName) {
            $finder->sortByName();
        } elseif (self::$SortByCaseInsensitiveName) {
            $finder->sortByCaseInsensitiveName();
        }
        if (self::$ReverseSorting) {
            $finder->reverseSorting();
        }

        // Build hash input from file names and mtimes
        $hashInput = '';
        foreach ($finder as $file) {
            $hashInput .= $file->getRealPath() . ':' . $file->getMTime() . "\n";
        }
        $currentHash = hash('sha256', $hashInput);
        $cachedHash = is_file($hashFile) ? file_get_contents($hashFile) : null;
        if (is_file($cacheFile) && $currentHash === $cachedHash) { // Load from cache
            $this->Data = new Data(require $cacheFile);
        } else { // Rebuild from XML files
            $this->Data = new Data();
            foreach ($finder as $file) {
                try {
                    $this->Data->importData($this->loadXml($file->getContents()));
                } catch (Throwable $e) {
                    Session()?->remove(SESSION_LANGUAGE_ID);
                    throw new RuntimeException('Error parsing ' . $file->getFilename() . ': ' . $e->getMessage());
                }
            }

            // Save cache
            $dir = dirname($cacheFile);
            if (!is_dir($dir)) {
                mkdir($dir, recursive: true);
            }
            file_put_contents($cacheFile, '<?php return ' . VarExporter::export($this->Data->export()) . ';');
            $dir = dirname($hashFile);
            if (!is_dir($dir)) {
                mkdir($dir, recursive: true);
            }
            file_put_contents($hashFile, $currentHash);
        }
    }

    /**
     * Set locale
     */
    function setLocale(string $id): void
    {
        global $httpContext;
        $localefile = Config('LOCALE_FOLDER') . $id . '.json';
        if (file_exists($localefile)) { // Load from locale file
            $locale = array_merge(['id' => $id], json_decode(file_get_contents($localefile), true));
        } else { // Load from PHP intl extension
            $locales = array_map('strtolower', ResourceBundle::getLocales(''));
            if (!in_array(strtolower(str_replace('-', '_', $id)), $locales)) { // Locale not supported by server
                $this->logger->error('Locale ' . $id . ' not supported by server.');
                $id = 'en-US'; // Fallback to 'en-US'
            }
            $locale = [
                'id' => $id,
                'desc' => Locale::getDisplayName($id),
            ];
        }
        $getSeparator = fn($str) => preg_match('/[^\w]+/', $str, $m) ? $m[0] : null;
        $httpContext['CurrentLocale'] = $locale['id'];
        $fmt = new NumberFormatter($httpContext['CurrentLocale'], NumberFormatter::DECIMAL);
        $currfmt = new NumberFormatter($httpContext['CurrentLocale'], NumberFormatter::CURRENCY);
        $pctfmt = new NumberFormatter($httpContext['CurrentLocale'], NumberFormatter::PERCENT);
        $currcode = $locale['currency_code'] ?? $currfmt->getTextAttribute(NumberFormatter::CURRENCY_CODE);
        $httpContext['CURRENCY_CODE'] = $currcode != 'XXX' ? $currcode : $httpContext['CURRENCY_CODE'];
        $httpContext['CURRENCY_SYMBOL'] = !empty($locale['currency_symbol']) ? $locale['currency_symbol'] : $currfmt->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
        $httpContext['DECIMAL_SEPARATOR'] = !empty($locale['decimal_separator']) ? $locale['decimal_separator'] : $fmt->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
        $httpContext['GROUPING_SEPARATOR'] = !empty($locale['grouping_separator']) ? $locale['grouping_separator'] : $fmt->getSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL);
        $httpContext['NUMBER_FORMAT'] = $fmt->getPattern();
        $httpContext['CURRENCY_FORMAT'] = $currfmt->getPattern();
        $httpContext['PERCENT_SYMBOL'] = $pctfmt->getSymbol(NumberFormatter::PERCENT_SYMBOL);
        $httpContext['PERCENT_FORMAT'] = !empty($locale['percent']) ? $locale['percent'] : $pctfmt->getPattern();
        $httpContext['NUMBERING_SYSTEM'] = '';
        $httpContext['DATE_FORMAT'] = !empty($locale['date']) ? $locale['date'] : (new IntlDateFormatter($httpContext['CurrentLocale'], IntlDateFormatter::SHORT, IntlDateFormatter::NONE))->getPattern();
        $httpContext['TIME_FORMAT'] = !empty($locale['time']) ? $locale['time'] : (new IntlDateFormatter($httpContext['CurrentLocale'], IntlDateFormatter::NONE, IntlDateFormatter::SHORT))->getPattern();
        $httpContext['DATE_SEPARATOR'] = $getSeparator($httpContext['DATE_FORMAT']) ?? $httpContext['DATE_SEPARATOR'];
        $httpContext['TIME_SEPARATOR'] = $getSeparator($httpContext['TIME_FORMAT']) ?? $httpContext['TIME_SEPARATOR'];
        $httpContext['TIME_ZONE'] = !empty($locale['time_zone']) ? $locale['time_zone'] : $httpContext['TIME_ZONE'];

        // Set up time zone from locale file (see https://www.php.net/timezones for supported time zones)
        if (!empty($httpContext['TIME_ZONE'])) {
            date_default_timezone_set($httpContext['TIME_ZONE']);
        }

        // Set locale
        $this->localeSwitcher->setLocale(str_replace('-', '_', $id));
    }

    // Get value only
    protected function getValue(array $data): array|string
    {
        if (count($data) > 0) {
            if (array_all($data, fn($v) => is_array($v))) { // Array of arrays
                $result = [];
                foreach ($data as $k => $v) {
                    $result[$k] = $this->getValue($v); // Preserve key
                }
                return $result;
            }
            return $data['value'] ?? '';
        }
        return '';
    }

    // Has data
    public function hasData(string $id): bool
    {
        return $this->Data->has($id) || $this->Data->has(strtolower($id));
    }

    // Set data
    public function setData(string $id, string|array $value): void
    {
        if ($this->Data->has($id)) {
            $this->Data->set($id, $value);
        } else {
            $this->Data->set(strtolower($id), $value);
        }
    }

    // Get data
    public function getData(string $id): string|array
    {
        if (!isset($this->Data)) { // For CLI only
            $this->setLanguage();
        }
        return $this->Data->get($id, null) ?? $this->Data->get(strtolower($id), '');
    }

    // Get translator
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    // Translate
    public function translate(?string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        $value = $this->translator->trans($id, $parameters, $domain, $locale);
        if ($value === $id) { // If translation not found
            $domain ??= 'messages'; // Default domain
            $locale ??= $this->translator->getLocale();
            $catalogue = $this->translator->getCatalogue($locale);
            if ($catalogue->has('global.' . $id . '.value', $domain)) { // Skip 'global.' prefix and '.value' suffix
                return $catalogue->get('global.' . $id . '.value', $domain);
            } elseif ($catalogue->has('global.' . strtolower($id ?? '') . '.value', $domain)) { // Skip 'global.' prefix and '.value' suffix
                return $catalogue->get('global.' . strtolower($id ?? '') . '.value', $domain);
            } elseif ($catalogue->has('global.' . $id, $domain)) { // Skip 'global.' prefix
                return $catalogue->get('global.' . $id, $domain);
            } elseif ($catalogue->has('global.' . strtolower($id ?? ''), $domain)) { // Skip 'global.' prefix
                return $catalogue->get('global.' . strtolower($id ?? ''), $domain);
            }
            return TitleCase($id); // Return title case of the $id
        }
        return $value;
    }

    /**
     * Get phrase
     *
     * @param string $id Phrase ID
     * @param ?bool $useText (true => text only, false => icon only, null => both)
     * @return string|array
     */
    public function phrase(string $id, ?bool $useText = false): string|array
    {
        $className = $this->getData('global.' . $id . '.class');
        if ($this->hasData('global.' . $id)) {
            $data = $this->getData('global.' . $id);
            $value = $this->getValue($data);
        } else {
            $value = TitleCase($id); // Return title case of the $id
        }
        if (is_string($value) && $useText !== true && $className != '') {
            if ($useText === null && $value !== '') { // Use both icon and text
                AppendClass($className, 'me-2');
            }
            if (preg_match('/\bspinner\b/', $className)) { // Spinner
                $res = '<div class="' . $className . '" role="status"><span class="visually-hidden">' . $value . '</span></div>';
            } else { // Icon
                $res = '<i data-phrase="' . $id . '" class="' . $className . '"><span class="visually-hidden">' . $value . '</span></i>';
            }
            if ($useText === null && $value !== "") { // Use both icon and text
                $res .= $value;
            }
            return $res;
        }
        return $value;
    }

    // Set phrase
    public function setPhrase(string $id, string $value): void
    {
        $this->setPhraseAttr($id, 'value', $value);
    }

    // Get project phrase
    public function projectPhrase(string $id): string
    {
        return $this->getData('project.' . $id . '.value');
    }

    // Set project phrase
    public function setProjectPhrase(string $id, string $value): void
    {
        $this->setData('project.' . $id . '.value', $value);
    }

    // Get menu phrase
    public function menuPhrase(string $menuId, string $id): string
    {
        return $this->getData('project.menu.' . $menuId . '.' . $id . '.value');
    }

    // Set menu phrase
    public function setMenuPhrase(string $menuId, string $id, string $value): void
    {
        $this->setData('project.menu.' . $menuId . '.' . $id . '.value', $value);
    }

    // Get table phrase
    public function tablePhrase(string $tblVar, string $id): string
    {
        return $this->getData('project.table.' . $tblVar .  '.' . $id . '.value');
    }

    // Set table phrase
    public function setTablePhrase(string $tblVar, string $id, string $value): void
    {
        $this->setData('project.table.' . $tblVar .  '.' . $id . '.value', $value);
    }

    // Get chart phrase
    public function chartPhrase(string $tblVar, string $chtVar, string $id): string
    {
        return $this->getData('project.table.' . $tblVar .  '.chart.' . $chtVar . '.' . $id . '.value');
    }

    // Set chart phrase
    public function setChartPhrase(string $tblVar, string $chtVar, string $id, string $value): void
    {
        $this->setData('project.table.' . $tblVar .  '.chart.' . $chtVar . '.' . $id . '.value', $value);
    }

    // Get field phrase
    public function fieldPhrase(string $tblVar, string $fldVar, string $id): string
    {
        return $this->getData('project.table.' . $tblVar .  '.field.' . $fldVar . '.' . $id . '.value');
    }

    // Set field phrase
    public function setFieldPhrase(string $tblVar, string $fldVar, string $id, string $value): void
    {
        $this->setData('project.table.' . $tblVar .  '.field.' . $fldVar . '.' . $id . '.value', $value);
    }

    // Get phrase attribute
    protected function phraseAttr(string $id, string $name): string
    {
        return $this->getData('global.' . $id . '.' . $name);
    }

    // Set phrase attribute
    protected function setPhraseAttr(string $id, string $name, string $value): void
    {
        $this->setData('global.' . $id . '.' . $name, $value);
    }

    // Get phrase class
    public function phraseClass(string $id): string
    {
        return $this->phraseAttr($id, 'class');
    }

    // Set phrase attribute
    public function setPhraseClass(string $id, string $value): void
    {
        $this->setPhraseAttr($id, 'class', $value);
    }

    // Output array as JSON
    public function arrayToJson(): string
    {
        $ar = $this->Data->get('global');
        $keys = array_keys($ar);
        $res = array_combine($keys, array_map(fn($id) => $this->phrase($id, true), $keys));
        return json_encode($res, JSON_FORCE_OBJECT);
    }

    // Output phrases to client side as JSON
    public function toJson(): string
    {
        return 'ew.language.phrases = ' . $this->arrayToJson() . ';';
    }

    // Output languages as array
    protected function getLanguages(): array
    {
        global $httpContext;
        $ar = [];
        $languages = Config('LANGUAGES');
        if (is_array($languages) && count($languages) > 1) {
            $finder = new Finder();
            $finder->files()->in($this->langFolder)->name(Config('LANGUAGES_FILE')); // Find languages.xml
            foreach ($finder as $file) {
                $data = $this->loadXml($file->getContents());
                foreach ($languages as $langId) {
                    $lang = array_merge(
                        ['id' => $langId, 'selected' => $langId == $httpContext['CurrentLanguage']],
                        $data->has('global.' . strtolower($langId)) ? $data->get('global.' . strtolower($langId)) : ['desc' => $this->phrase($langId)]
                    );
                    $ar[] = $lang;
                }
                break; // Only one file
            }
        }
        return $ar;
    }

    // Set template
    public function setTemplate(string $value): void
    {
        $this->Template = $value;
    }

    // Get template
    public function getTemplate(): string
    {
        if ($this->Template == '') {
            if (SameText($this->Type, 'LI')) { // LI template (for used with top Navbar)
                return '{{for languages}}<li class="nav-item"><a class="nav-link{{if selected}} active{{/if}} ew-tooltip" title="{{>desc}}" data-ew-action="language" data-language="{{:id}}">{{:id}}</a></li>{{/for}}';
            } elseif (SameText($this->Type, 'DROPDOWN')) { // DROPDOWN template (for used with top Navbar)
                return '<li class="nav-item dropdown"><a class="nav-link" data-bs-toggle="dropdown"><i class="fa-solid fa-globe ew-icon"></i></span></a><div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">{{for languages}}<a class="dropdown-item{{if selected}} active{{/if}}" data-ew-action="language" data-language="{{:id}}">{{>desc}}</a>{{/for}}</div></li>';
            } elseif (SameText($this->Type, 'SELECT')) { // SELECT template (NOT for used with top Navbar)
                return '<div class="ew-language-option"><select class="form-select" id="ew-language" name="ew-language" data-ew-action="language">{{for languages}}<option value="{{:id}}"{{if selected}} selected{{/if}}>{{:desc}}</option>{{/for}}</select></div>';
            } elseif (SameText($this->Type, 'RADIO')) { // RADIO template (NOT for used with top Navbar)
                return '<div class="ew-language-option"><div class="btn-group" data-bs-toggle="buttons">{{for languages}}<input type="radio" name="ew-language" id="ew-Language-{{:id}}" data-ew-action="language"{{if selected}} checked{{/if}} value="{{:id}}"><label class="btn btn-default ew-tooltip" for="ew-language-{{:id}}" data-container="body" data-bs-placement="bottom" title="{{>desc}}">{{:id}}</label>{{/for}}</div></div>';
            }
        }
        return $this->Template;
    }
}
