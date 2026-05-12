<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\Type;
use Spatie\Cloneable\Cloneable;
use Illuminate\Contracts\Support\Htmlable;
use DateTimeImmutable;
use DateTime;
use Closure;
use Exception;

/**
 * Field class
 */
class DbField
{
    use Cloneable;
    public string $FieldVar; // Field variable name
    public string $Expression = ""; // Field expression (used in SQL)
    public string $BasicSearchExpression = ""; // Field expression (used in basic search SQL)
    public int $Type = 202; // Field type
    public DataType $DataType = DataType::STRING; // PHPMaker data type (DataType::XXX)
    public ParameterType $ParameterType = ParameterType::STRING; // Doctrine DBAL parameter type
    public ?Type $CustomDataType = null; // Custom type (e.g. Geometry) overriding ParameterType
    public int $Size = 255; // Field size
    public int $DateTimeFormat = -1; // Date time format
    public string $VirtualExpression = ""; // Virtual field expression (used in ListSQL)
    public bool $IsVirtual = false; // Virtual field
    public bool $ForceSelection = false; // Autosuggest force selection
    public bool $VirtualSearch = false; // Search as virtual field
    public string $ViewTag = ""; // View Tag
    public string $HtmlTag = ""; // HTML Tag
    protected ?DbField $clone = null;
    protected array $methods = [];
    protected array $boundMethods = [];
    public ?BaseDbTable $Table = null; // Table object
    public string $TableName = ""; // Table name
    public string $TableVar = ""; // Table variable name
    public string $Param = ""; // Field parameter name (Table class property name)
    public string $PropertyName = ""; // Field entity property name
    public string $LookupExpression = ""; // Lookup expression
    public bool $IsCustom = false; // Custom field
    public bool $IsNativeSelect = false; // Native Select Tag
    public bool $SelectMultiple = false; // Select multiple
    public string $ErrorMessage = ""; // Error message
    public string $DefaultErrorMessage = ""; // Default error message
    public bool $IsInvalid = false; // Invalid
    public mixed $TooltipValue = ""; // Field tooltip value
    public int $TooltipWidth = 0; // Field tooltip width
    public string $InputTextType = "text"; // Field input text type
    public bool $IsDetailKey = false; // Field is detail key
    public bool $IsAutoIncrement = false; // Autoincrement field (FldAutoIncrement)
    public bool $IsPrimaryKey = false; // Primary key (FldIsPrimaryKey)
    public bool $IsForeignKey = false; // Foreign key (Master/Detail key)
    public bool $IsEncrypt = false; // Field is encrypted
    public bool $Raw = false; // Raw value (save without removing XSS)
    public bool $Nullable = true; // Nullable
    public bool $Required = false; // Required
    public array $SearchOperators = []; // Search operators
    public ?AdvancedSearch $AdvancedSearch = null; // AdvancedSearch Object
    public array $AdvancedFilters = []; // Advanced Filters
    public ?HttpUpload $Upload = null; // Upload Object
    public string $FormatPattern = ""; // Format pattern
    public string $DefaultNumberFormat = ""; // Default number format
    public string $CssStyle = ""; // CSS style
    public string $CssClass = ""; // CSS class
    public string $ImageAlt = ""; // Image alt
    public string $ImageCssClass = ""; // Image CSS Class
    public ?int $ImageWidth = null; // Image width
    public ?int $ImageHeight = null; // Image height
    public bool $ImageResize = false; // Image resize
    public bool $IsBlobImage = false; // Is blob image
    public string $CellCssClass = ""; // Cell CSS class
    public string $CellCssStyle = ""; // Cell CSS style
    public string $RowCssClass = ""; // Row CSS class
    public string $RowCssStyle = ""; // Row CSS style
    public int $Count = 0; // Count
    public float $Total = 0; // Total
    public mixed $TrueValue = "1";
    public mixed $FalseValue = "0";
    public bool $Visible = true; // Visible
    public bool $Disabled = false; // Disabled
    public bool $ReadOnly = false; // Read only
    public int $MemoMaxLength = 0; // Maximum length for memo field in list page
    public bool $TruncateMemoRemoveHtml = false; // Remove HTML from memo field
    public string $CustomMessage = ""; // Custom message
    public string $HeaderCellCssClass = ""; // Header cell (<th>) CSS class
    public string $FooterCellCssClass = ""; // Footer cell (<td> in <tfoot>) CSS class
    public string $MultiUpdate = ""; // Multi update
    public mixed $OldValue = null; // Old Value
    public mixed $DefaultValue = null; // Default Value
    public mixed $ConfirmValue = null; // Confirm value
    public mixed $CurrentValue = null; // Current value
    public mixed $ViewValue = null; // View value (string|Object)
    public mixed $EditValue = null; // Edit value
    public mixed $EditValue2 = null; // Edit value 2 (for search)
    public string $HrefValue = ""; // Href value
    public string $ExportHrefValue = ""; // Href value for export
    public ?string $FormValue = null; // Form value
    public ?string $QueryStringValue = null; // QueryString value
    public mixed $DbValue = null; // Database value
    public bool $Sortable = true; // Sortable
    public ?string $UploadPath = null; // Upload path
    public ?string $OldUploadPath = null; // Old upload path (for deleting old image)
    public string $UploadAllowedFileExt; // Allowed file extensions
    public int $UploadMaxFileSize; // Upload max file size
    public ?int $UploadMaxFileCount = null; // Upload max file count
    public bool $ImageCropper = false; // Upload cropper
    public bool $UploadMultiple = false; // Multiple Upload
    public bool $UseColorbox = false; // Use Colorbox
    public string|array $DisplayValueSeparator = ", ";
    public bool $AutoFillOriginalValue = false;
    public ?string $RequiredErrorMessage = null;
    public ?Lookup $Lookup = null;
    public int $OptionCount = 0;
    public bool $UseLookupCache = false; // Use lookup cache
    public int $LookupCacheCount; // Lookup cache count
    public string $PlaceHolder = "";
    public string $Caption = "";
    public bool $UsePleaseSelect = true;
    public string $PleaseSelectText = "";
    public bool $Exportable = true;
    public bool $ExportOriginalValue = false;
    public bool $ExportFieldCaption = false;
    public bool $ExportFieldImage = false;
    public array $Options = [];
    public bool $UseFilter = false; // Use table header filter

    // Constructor
    public function __construct(
        BaseDbTable|string $table,
        public readonly string $Name, // Field name
        array $config,
        protected Language $language,
        protected Request $request,
        public Attributes $CellAttrs = new Attributes(),
        public Attributes $EditAttrs = new Attributes(),
        public Attributes $LinkAttrs = new Attributes(),
        public Attributes $RowAttrs = new Attributes(),
        public Attributes $ViewAttrs = new Attributes(),
        public string|array|Attributes $EditCustomAttributes = new Attributes(), // Edit custom attributes
        public string|array|Attributes $LinkCustomAttributes = new Attributes(), // Link custom attributes
        public string|array|Attributes $ViewCustomAttributes = new Attributes(), // View custom attributes
    ) {
        $this->FieldVar = $this->Name; // To be updated by configure()
        $this->setTable($table);
        $this->configure($config);
        $this->RequiredErrorMessage = $this->language->phrase("EnterRequiredField");
        $this->Upload = $config['IsUpload'] ?? false ? new HttpUpload($this->request, $this->language, $this) : null;
        $this->AdvancedSearch = new AdvancedSearch($this, $this->request->getSession());
    }

    // Get table object
    public function getTable(): ?BaseDbTable
    {
        return $this->Table;
    }

    // Set table
    public function setTable(BaseDbTable|string $table): void
    {
        if ($table instanceof BaseDbTable) {
            $this->Table = $table;
            $this->TableVar = $table->TableVar;
            $this->TableName = $table->TableName;
            $this->UploadPath = $table->UploadPath;
            $this->OldUploadPath = $table->OldUploadPath;
            $this->UploadAllowedFileExt = $table->UploadAllowedFileExt;
            $this->UploadMaxFileSize = $table->UploadMaxFileSize;
            $this->UploadMaxFileCount = $table->UploadMaxFileCount;
            $this->ImageCropper = $table->ImageCropper;
            $this->UseColorbox = $table->UseColorbox;
            $this->AutoFillOriginalValue = $table->AutoFillOriginalValue;
            $this->UseLookupCache = $table->UseLookupCache;
            $this->LookupCacheCount = $table->LookupCacheCount;
            $this->ExportOriginalValue = $table->ExportOriginalValue;
            $this->ExportFieldCaption = $table->ExportFieldCaption;
            $this->ExportFieldImage = $table->ExportFieldImage;
            $this->DefaultNumberFormat = $table->DefaultNumberFormat;
        } elseif (is_string($table)) { // For page without table
            $this->TableVar = $table;
            $this->TableName = $table;
        }
    }

    /**
     * Configure
     *
     * @param array $config Field settings
     * @return void
     */
    protected function configure(array $config): void
    {
        foreach ($config as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }

    /**
     * Fluent interface for chaining property setups
     */
    public function setProperty(string $name, $value): self
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
        return $this;
    }

    /**
     * Set multiple properties at once
     */
    public function setProperties(array $properties): self
    {
        foreach ($properties as $name => $value) {
            $this->setProperty($name, $value);
        }
        return $this;
    }

    /**
     * Add a dynamic method
     *
     * @param string $methodName
     * @param Closure $methodCallable
     */
    public function addMethod(string $methodName, Closure $methodCallable): void
    {
        // Bind the closure to the Table object if available
        if ($this->Table !== null) {
            $this->methods[$methodName] = $methodCallable->bindTo($this->Table, $this->Table::class);
        } else {
            $this->methods[$methodName] = $methodCallable;
        }

        // Reset bound cache for this method
        unset($this->boundMethods[$methodName]);
    }

    /**
     * Check if dynamic method exists
     */
    public function hasMethod(string $methodName): bool
    {
        return isset($this->methods[$methodName]);
    }

    /**
     * Magic call to dynamic methods
     *
     * @param string $methodName
     * @param array $args
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call(string $methodName, array $args): mixed
    {
        if (!$this->hasMethod($methodName)) {
            throw new \BadMethodCallException(sprintf("Method '%s' not found on %s", $methodName, static::class));
        }

        // Cache bound closures to avoid rebinding every call
        if (!isset($this->boundMethods[$methodName])) {
            $callable = $this->methods[$methodName];
            if ($callable instanceof Closure && $this->Table !== null) {
                $this->boundMethods[$methodName] = $callable->bindTo($this->Table, $this->Table::class);
            } else {
                $this->boundMethods[$methodName] = $callable;
            }
        }
        return ($this->boundMethods[$methodName])(...$args);
    }

    // Get ICU format pattern
    public function formatPattern(): string
    {
        global $httpContext;
        $fmt = $this->FormatPattern;
        if (!$fmt) {
            if ($this->DataType == DataType::DATE) {
                $fmt = DateFormat($this->DateTimeFormat) ?: $httpContext["DATE_FORMAT"];
            } elseif ($this->DataType == DataType::TIME) {
                $fmt = DateFormat($this->DateTimeFormat) ?: $httpContext["TIME_FORMAT"];
            }
        }
        return $fmt;
    }

    // Get client side date/time format pattern
    public function clientFormatPattern(): string
    {
        return in_array($this->DataType, [DataType::DATE, DataType::TIME]) ? $this->formatPattern() : "";
    }

    // Is boolean field
    public function isBoolean(): bool
    {
        return $this->DataType == DataType::BOOLEAN || $this->DataType == DataType::BIT && $this->Size == 1;
    }

    // Is number field
    public function isNumber(): bool
    {
        return $this->DataType == DataType::NUMBER;
    }

    // Is selected for multiple update
    public function multiUpdateSelected(): bool
    {
        return $this->MultiUpdate == 1;
    }

    // Field encryption/decryption required
    public function isEncrypt(): bool
    {
        return $this->IsEncrypt;
    }

    // Get Custom Message (help text)
    public function getCustomMessage(): string
    {
        $msg = trim($this->CustomMessage);
        if (IsEmpty($msg)) {
            return "";
        }
        if (preg_match('/^<.+>$/', $msg)) { // Html content
            return $msg;
        } else {
            return '<div id="' . $this->FieldVar . '_help" class="form-text">' . $msg . '</div>';
        }
    }

    // Get Input type attribute (TEXT only)
    public function getInputTextType(): string
    {
        return isset($this->EditAttrs["type"]) ? $this->EditAttrs["type"] : $this->InputTextType;
    }

    // Get place holder
    public function getPlaceHolder(): string
    {
        return $this->ReadOnly || isset($this->EditAttrs["readonly"]) ? "" : $this->PlaceHolder;
    }

    // Search expression
    public function searchExpression(): string
    {
        return $this->Expression;
    }

    // Search data type
    public function searchDataType(): DataType
    {
        return $this->DataType;
    }

    // Get data type
    public function getDataType(): DataType
    {
        return $this->DataType;
    }

    // Set data type
    public function setDataType(DataType $value): void
    {
        $this->DataType = $value;
        $dbtype = $this->Table?->getDbType();
        $this->ParameterType = match ($value) {
            DataType::NUMBER => in_array($this->Type, [2, 3, 16, 17, 18]) ? ParameterType::INTEGER : ParameterType::STRING,
            DataType::BLOB => ($dbtype == "MYSQL" || $dbtype == "POSTGRESQL")
                ? ParameterType::BINARY
                : ParameterType::LARGE_OBJECT,
            DataType::BOOLEAN => ($dbtype == "MYSQL" || $dbtype == "POSTGRESQL")
                ? ParameterType::STRING // 'Y'|'N' or 'y'|'n' or '1'|'0' or 't'|'f'
                : ParameterType::BOOLEAN,
            DataType::BIT => ParameterType::INTEGER, // $dbtype == "MYSQL" || $dbtype == "POSTGRESQL"
            default => ParameterType::STRING
        };
    }

    // Get parameter type
    public function getParameterType(): ParameterType
    {
        return $this->ParameterType;
    }

    // Set field caption
    public function setParameterType(ParameterType $v): void
    {
        $this->ParameterType = $v;
    }

    // Field caption
    public function caption(): string
    {
        if ($this->Caption == "") {
            return $this->language->fieldPhrase($this->TableVar, $this->Param, "FldCaption");
        }
        return $this->Caption;
    }

    // Field title
    public function title(): string
    {
        return $this->language->fieldPhrase($this->TableVar, $this->Param, "FldTitle");
    }

    // Field image alt
    public function alt(): string
    {
        return $this->language->fieldPhrase($this->TableVar, $this->Param, "FldAlt");
    }

    // Clear error message
    public function clearErrorMessage(): void
    {
        $this->IsInvalid = false;
        $this->ErrorMessage = "";
    }

    // Add error message
    public function addErrorMessage(string $message): void
    {
        $this->IsInvalid = true;
        AddMessage($this->ErrorMessage, $message);
    }

    // Set error message
    public function setErrorMessage(string $message): void
    {
        $this->IsInvalid = true;
        $this->ErrorMessage = $message;
    }

    // Field error message
    public function getErrorMessage(bool $required = true): string
    {
        $err = $this->ErrorMessage;
        if ($err == "") {
            $err = $this->language->fieldPhrase($this->TableVar, $this->Param, "FldErrMsg");
            if ($err == "" && !IsEmpty($this->DefaultErrorMessage)) {
                $err = $this->DefaultErrorMessage . " - " . $this->caption();
            }
            if ($this->Required && $required && $this->RequiredErrorMessage) {
                AddMessage($err, sprintf($this->RequiredErrorMessage, $this->caption()));
            }
        }
        return $err;
    }

    // Get is-invalid class
    public function isInvalidClass(): string
    {
        return $this->IsInvalid ? " is-invalid" : "";
    }

    // Field option value
    public function tagValue(int $i): string
    {
        return $this->language->fieldPhrase($this->TableVar, $this->Param, "FldTagValue" . $i);
    }

    // Field option caption
    public function tagCaption(int $i): string
    {
        return $this->language->fieldPhrase($this->TableVar, $this->Param, "FldTagCaption" . $i);
    }

    // Set field visibility
    public function setVisibility(): void
    {
        $this->Visible = $this->Table->getFieldVisibility($this->Param);
    }

    // Check if multiple selection
    public function isMultiSelect(): bool
    {
        return $this->HtmlTag == "SELECT" && $this->SelectMultiple || $this->HtmlTag == "CHECKBOX" && !$this->isBoolean() && $this->DataType == DataType::STRING;
    }

    // Set native select
    public function setNativeSelect(bool $value): void
    {
        if ($value && $this->HtmlTag == "SELECT" && !$this->SelectMultiple) { // Select one
            $this->IsNativeSelect = true;
        } else {
            $this->IsNativeSelect = false;
        }
    }

    // Set select multiple
    public function setSelectMultiple(bool $value): void
    {
        $this->SelectMultiple = $value;
        if (!$this->SelectMultiple && Config("USE_NATIVE_SELECT_ONE")) { // Select one
            $this->setNativeSelect(true);
        }
    }

    // Field lookup cache option
    public function lookupCacheOption(mixed $val): mixed
    {
        if (IsFloatType($this->Type)) { // Handle float field
            $val = (float)$val;
        }
        $val = strval($val);
        if ($val == "" || $this->Lookup === null || !is_array($this->Lookup->Options) || count($this->Lookup->Options) == 0) {
            return null;
        }
        $res = null;
        if ($this->isMultiSelect()) { // Multiple options
            $res = new OptionValues();
            $arwrk = explode(Config("MULTIPLE_OPTION_SEPARATOR"), $val);
            foreach ($arwrk as $wrk) {
                $wrk = trim($wrk);
                if (array_key_exists($wrk, $this->Lookup->Options)) { // Lookup data found in cache
                    $ar = $this->Lookup->Options[$wrk];
                    $res->add($this->displayValue($ar));
                } else {
                    $res->add($val); // Not found, use original value
                }
            }
        } else {
            if (array_key_exists($val, $this->Lookup->Options)) { // Lookup data found in cache
                $ar = $this->Lookup->Options[$val];
                $res = $this->displayValue($ar);
            } else {
                $res = $val; // Not found, use original value
            }
        }
        return $res;
    }

    // Has field lookup options
    public function hasLookupOptions(): bool
    {
        if ($this->Lookup) {
            return $this->OptionCount > 0 // User values
                || is_array($this->Lookup->Options) && count($this->Lookup->Options) > 0; // Lookup table
        }
        return false;
    }

    // Field lookup options
    public function lookupOptions(): array
    {
        return $this->Lookup && is_array($this->Lookup->Options) ? array_values($this->Lookup->Options) : [];
    }

    // Field option caption by option value
    public function optionCaption(string $val): string
    {
        if ($this->Lookup && is_array($this->Lookup->Options) && count($this->Lookup->Options) > 0) { // Options already setup
            if (array_key_exists($val, $this->Lookup->Options)) { // Lookup data
                $ar = $this->Lookup->Options[$val];
                $val = $this->displayValue($ar);
            }
        } else { // Load default tag values
            for ($i = 0; $i < $this->OptionCount; $i++) {
                if ($val == $this->tagValue($i + 1)) {
                    $val = $this->tagCaption($i + 1) ?: $val;
                    break;
                }
            }
        }
        return $val;
    }

    // Get field user options as [ ["lf" => "value", "df" => "caption"], ... ]
    public function options(bool $pleaseSelect = false): array
    {
        $arwrk = [];
        if ($pleaseSelect) { // Add "Please Select"
            $arwrk[] = ["lf" => "", "df" => $this->language->phrase("PleaseSelect")];
        }
        if ($this->Lookup && is_array($this->Lookup->Options) && count($this->Lookup->Options) > 0) { // Options already setup
            $arwrk = array_values($this->Lookup->Options);
        } else { // Load default tag values
            for ($i = 0; $i < $this->OptionCount; $i++) {
                $value = $this->tagValue($i + 1);
                $caption = $this->tagCaption($i + 1) ?: $value;
                $arwrk[] = ["lf" => $value, "df" => $caption];
            }
        }
        return $arwrk;
    }

    // Upload path
    public function uploadPath(): string
    {
        return UploadPath(false, $this->UploadPath);
    }

    // Href path
    public function hrefPath(): string
    {
        $uploadPath = $this->uploadPath();
        return IncludeTrailingDelimiter(IsRemote($uploadPath) ? GetFilePublicUrl($uploadPath) : GetUrl($uploadPath), false);
    }

    // Physical upload path
    public function physicalUploadPath(): string
    {
        return PrefixDirectoryPath($this->UploadPath);
    }

    // Old Physical upload path
    public function oldPhysicalUploadPath(): string
    {
        return PrefixDirectoryPath($this->OldUploadPath);
    }

    // Get select options HTML
    public function selectOptionListHtml(string $name = "", ?bool $multiple = null): string
    {
        $empty = true;
        $isSearch = CurrentPage()->RowType == RowType::SEARCH;
        $curValue = $isSearch ? (StartsString("y", $name) ? $this->AdvancedSearch->SearchValue2 : $this->AdvancedSearch->SearchValue) : $this->CurrentValue;
        $useFilter = $this->UseFilter && $isSearch;
        $str = "";
        $multiple ??= $this->isMultiSelect();
        if ($multiple) {
            $armulti = (strval($curValue) != "")
                ? explode($useFilter ? Config("FILTER_OPTION_SEPARATOR") : Config("MULTIPLE_OPTION_SEPARATOR"), strval($curValue))
                : [];
            $cnt = count($armulti);
        }
        if (is_array($this->EditValue) && !$useFilter) { // Skip checking for filter fields
            $ar = $this->EditValue;
            if ($multiple) {
                $rowcnt = count($ar);
                $empty = true;
                for ($i = 0; $i < $rowcnt; $i++) {
                    $sel = false;
                    for ($j = 0; $j < $cnt; $j++) {
                        if (SameString($ar[$i]["lf"], $armulti[$j]) && $armulti[$j] != null) {
                            $armulti[$j] = null; // Marked for removal
                            $sel = true;
                            $empty = false;
                            break;
                        }
                    }
                    if (!$sel) {
                        continue;
                    }
                    foreach ($ar[$i] as $k => $v) {
                        $ar[$i][$k] = RemoveHtml(strval($ar[$i][$k]));
                    }
                    $str .= "<option value=\"" . HtmlEncode($ar[$i]["lf"]) . "\" selected>" . $this->displayValue($ar[$i]) . "</option>";
                }
            } else {
                if ($this->UsePleaseSelect) {
                    $str .= "<option value=\"\">" . ($this->IsNativeSelect ? $this->language->phrase("PleaseSelect") : $this->language->phrase("BlankOptionText")) . "</option>"; // Blank option
                }
                $rowcnt = count($ar);
                $empty = true;
                for ($i = 0; $i < $rowcnt; $i++) {
                    if (SameString($curValue, $ar[$i]["lf"])) {
                        $empty = false;
                    } else {
                        continue;
                    }
                    foreach ($ar[$i] as $k => $v) {
                        $ar[$i][$k] = RemoveHtml(strval($ar[$i][$k]));
                    }
                    $str .= "<option value=\"" . HtmlEncode($ar[$i]["lf"]) . "\" selected>" . $this->displayValue($ar[$i]) . "</option>";
                }
            }
        }
        if ($multiple) {
            for ($i = 0; $i < $cnt; $i++) {
                if ($armulti[$i] != null) {
                    $str .= "<option value=\"" . HtmlEncode($armulti[$i]) . "\" selected></option>";
                }
            }
        } else {
            if ($empty && strval($curValue) != "") {
                $str .= "<option value=\"" . HtmlEncode($curValue) . "\" selected></option>";
            }
        }
        if ($empty) {
            $this->OldValue = "";
        }
        return $str;
    }

    /**
     * Get display field value separator
     *
     * @param int $idx Display field index (1|2|3)
     * @return string
     */
    protected function getDisplayValueSeparator(int $idx): string
    {
        $sep = $this->DisplayValueSeparator;
        return is_array($sep) ? $sep[$idx - 1] ?? ", " : ($sep ?: ", ");
    }

    // Get display field value separator as attribute value
    public function displayValueSeparatorAttribute(): string
    {
        return HtmlEncode(is_array($this->DisplayValueSeparator) ? json_encode($this->DisplayValueSeparator) : $this->DisplayValueSeparator);
    }

    /**
     * Get display value (for lookup field)
     *
     * @param array $row Record to be displayed
     * @return string
     */
    public function displayValue(array $row): string
    {
        $ar = array_values($row);
        $val = strval(@$ar[1]); // Display field 1
        for ($i = 2; $i <= 4; $i++) { // Display field 2 to 4
            $sep = $this->getDisplayValueSeparator($i - 1);
            if ($sep === null) { // No separator, break
                break;
            }
            if (@$ar[$i] != "") {
                $val .= $sep . $ar[$i];
            }
        }
        return $val;
    }

    /**
     * Get display value from EditValue
     */
    public function getDisplayValue(mixed $value): string
    {
        if (is_array($value)) {
            $value = count($value) > 0 ? $this->displayValue($value[0]) : "";
        } else {
            $value = strval($value);
        }
        $value = RemoveHtml($value);
        if (!$this->Raw) { // Decode value first (e.g. &amp;)
            $value = HtmlDecode($value);
        }
        return HtmlEncode($value);
    }

    // Reset attributes for field object
    public function resetAttributes(): void
    {
        $this->CssStyle = "";
        $this->CssClass = "";
        $this->CellCssStyle = "";
        $this->CellCssClass = "";
        $this->RowCssStyle = "";
        $this->RowCssClass = "";
        $this->CellAttrs = new Attributes();
        $this->EditAttrs = new Attributes();
        $this->LinkAttrs = new Attributes();
        $this->RowAttrs = new Attributes();
        $this->ViewAttrs = new Attributes();
    }

    // Get closest width class
    public function getWidthClass(int $value) {
        return "w-" . (round($value / 25) * 25) . "px";
    }

    // Get closest height class
    public function getHeightClass(int $value) {
        return "h-" . (round($value / 25) * 25) . "px";
    }

    // View attributes
    public function viewAttributes(): string
    {
        $viewattrs = $this->ViewAttrs;
        if ($this->ViewTag == "IMAGE") {
            $viewattrs["alt"] = (trim($this->ImageAlt ?? "") != "") ? trim($this->ImageAlt ?? "") : ""; // IMG tag requires alt attribute
        }
        $attrs = $this->ViewCustomAttributes; // Custom attributes
        if ($attrs instanceof Attributes) {
            $attrs = $attrs->toArray();
        }
        if (is_array($attrs)) { // Custom attributes as array
            $ar = $attrs;
            $attrs = "";
            $aik = array_intersect_key($ar, $viewattrs->toArray());
            $viewattrs->merge($ar); // Combine attributes
            foreach ($aik as $k => $v) { // Duplicate attributes
                if ($k == "style" || StartsString("on", $k)) { // "style" and events
                    $viewattrs->append($k, $v, ";");
                } else { // "class" and others
                    $viewattrs->append($k, $v, " ");
                }
            }
        }
        $viewattrs->appendClass($this->CssClass);
        if ($this->ViewTag == "IMAGE" && !preg_match('/\bcard-img\b/', $viewattrs["class"])) {
            if ((int)$this->ImageWidth > 0 && (!$this->ImageResize || (int)$this->ImageHeight <= 0)) {
                $viewattrs->appendClass($this->getWidthClass((int)$this->ImageWidth));
            }
            if ((int)$this->ImageHeight > 0 && (!$this->ImageResize || (int)$this->ImageWidth <= 0)) {
                $viewattrs->appendClass($this->getHeightClass((int)$this->ImageHeight));
            }
        }
        $viewattrs->append("style", $this->CssStyle, ";");
        $att = $viewattrs->toString();
        if ($attrs != "") { // Custom attributes as string
            $att .= " " . $attrs;
        }
        return $att;
    }

    // Edit attributes
    public function editAttributes(): string
    {
        $editattrs = $this->EditAttrs;
        $attrs = $this->EditCustomAttributes; // Custom attributes
        if ($attrs instanceof Attributes) {
            $attrs = $attrs->toArray();
        }
        if (is_array($attrs)) { // Custom attributes as array
            $ar = $attrs;
            $attrs = "";
            $aik = array_intersect_key($ar, $editattrs->toArray());
            $editattrs->merge($ar); // Combine attributes
            foreach ($aik as $k => $v) { // Duplicate attributes
                if ($k == "style" || StartsString("on", $k)) { // "style" and events
                    $editattrs->append($k, $v, ";");
                } else { // "class" and others
                    $editattrs->append($k, $v, " ");
                }
            }
        }
        $editattrs->append("style", $this->CssStyle, ";");
        $editattrs->appendClass($this->CssClass);
        if ($this->Disabled) {
            $editattrs["disabled"] = true;
        }
        if ($this->IsInvalid && !($this->Table && property_exists($this->Table, "RowIndex") && $this->Table->RowIndex == '$rowindex$')) {
            $editattrs->appendClass("is-invalid");
        }
        if ($this->ReadOnly) {
            if (in_array($this->HtmlTag, ["TEXT", "PASSWORD", "TEXTAREA"])) { // Elements support readonly
                $editattrs["readonly"] = true;
            } else { // Elements do not support readonly
                // $editattrs["disabled"] = true;
                $editattrs["data-readonly"] = "1";
                $editattrs->appendClass("disabled");
            }
        }
        $att = $editattrs->toString();
        if ($attrs != "") { // Custom attributes as string
            $att .= " " . $attrs;
        }
        return $att;
    }

    // Cell styles (for export only)
    public function cellStyles(): string
    {
        $att = "";
        $style = Concat($this->CellCssStyle, $this->CellAttrs["style"], ";");
        $class = $this->CellCssClass;
        AppendClass($class, $this->CellAttrs["class"]);
        if ($style != "") {
             $att .= " style=\"" . $style . "\"";
        }
        if ($class != "") {
            $att .= " class=\"" . $class . "\"";
        }
        return $att;
    }

    // Cell attributes
    public function cellAttributes(): string
    {
        $cellattrs = $this->CellAttrs;
        $cellattrs->append("style", $this->CellCssStyle, ";");
        $cellattrs->appendClass($this->CellCssClass);
        return $cellattrs->toString();
    }

    // Row attributes
    public function rowAttributes(): string
    {
        $rowattrs = $this->RowAttrs;
        $rowattrs->append("style", $this->RowCssStyle, ";");
        $rowattrs->appendClass($this->RowCssClass);
        return $rowattrs->toString();
    }

    // Link attributes
    public function linkAttributes(): string
    {
        $linkattrs = $this->LinkAttrs;
        $attrs = $this->LinkCustomAttributes; // Custom attributes
        if ($attrs instanceof Attributes) {
            $attrs = $attrs->toArray();
        }
        if (is_array($attrs)) { // Custom attributes as array
            $ar = $attrs;
            $attrs = "";
            $aik = array_intersect_key($ar, $linkattrs->toArray());
            $linkattrs->merge($ar); // Combine attributes
            foreach ($aik as $k => $v) { // Duplicate attributes
                if ($k == "style" || StartsString("on", $k)) { // "style" and events
                    $linkattrs->append($k, $v, ";");
                } else { // "class" and others
                    $linkattrs->append($k, $v, " ");
                }
            }
        }
        $href = trim($this->HrefValue);
        if ($href != "") {
            $linkattrs["href"] = $href;
        }
        $att = $linkattrs->toString();
        if ($attrs != "") { // Custom attributes as string
            $att .= " " . $attrs;
        }
        return $att;
    }

    // Header cell CSS class
    public function headerCellClass(): string
    {
        $class = "ew-table-header-cell";
        return AppendClass($class, $this->HeaderCellCssClass);
    }

    // Footer cell CSS class
    public function footerCellClass(): string
    {
        $class = "ew-table-footer-cell";
        return AppendClass($class, $this->FooterCellCssClass);
    }

    // Add CSS class to all class properties
    public function addClass(string $class): void
    {
        AppendClass($this->CellCssClass, $class);
        AppendClass($this->RowCssClass, $class);
        AppendClass($this->HeaderCellCssClass, $class);
        AppendClass($this->FooterCellCssClass, $class);
    }

    // Remove CSS class from all class properties
    public function removeClass(string $class): void
    {
        RemoveClass($this->CellCssClass, $class);
        RemoveClass($this->RowCssClass, $class);
        RemoveClass($this->HeaderCellCssClass, $class);
        RemoveClass($this->FooterCellCssClass, $class);
    }

    /**
     * Set up edit attributes
     *
     * @param array $attrs CSS class names
     * @return void
     */
    public function setupEditAttributes(array $attrs = []): void
    {
        $classnames = match ($this->InputTextType) {
            "color" => "form-control form-control-color",
            "range" => "form-range",
            default => "form-control"
        };
        $this->EditAttrs->appendClass($classnames);
        $this->EditAttrs->merge($attrs);
    }

    // Get sorting order
    public function getSort(): string
    {
        return Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_SORT") . "_" . $this->FieldVar)) ?? "";
    }

    // Set sorting order
    public function setSort(string $v): void
    {
        if (Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_SORT") . "_" . $this->FieldVar)) != $v) {
            Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_SORT") . "_" . $this->FieldVar), $v);
        }
    }

    // Get next sorting order
    public function getNextSort(): string
    {
        $sort = $this->getSort();
        return match ($sort) {
            "ASC" => "DESC",
            "DESC" => SameText(Config("SORT_OPTION"), "Tristate") ? "NO" : "ASC",
            "NO" => "ASC",
            default => "ASC"
        };
    }

    // Get sorting order icon
    public function getSortIcon(): string
    {
        $sort = $this->getSort();
        return match ($sort) {
            "ASC" => $this->language->phrase("SortUp"),
            "DESC" => $this->language->phrase("SortDown"),
            default => ""
        };
    }

    // Sort URL
    public function sortUrl(): string
    {
        global $httpContext;
        $urlParm = "order=" . urlencode($this->Name) . "&ordertype=" . $this->getNextSort();
        if ($httpContext["DashboardReport"]) {
            $urlParm .= "&" . Config("PAGE_DASHBOARD") . "=" . $httpContext["DashboardReport"];
        }
        return CurrentPageName() . "?" . $urlParm;
    }

    // Get view value
    public function getViewValue(): string
    {
        return $this->ViewValue instanceof Htmlable ? $this->ViewValue->toHtml() : (is_array($this->ViewValue) ? JsonEncode($this->ViewValue) : strval($this->ViewValue));
    }

    // Get edit value
    public function getEditValue(): mixed
    {
        return is_string($this->EditValue) ? HtmlEncode($this->EditValue) : (is_array($this->EditValue) ? JsonEncode($this->EditValue) : $this->EditValue);
    }

    // Export caption
    public function exportCaption(): string
    {
        if (!$this->Exportable) {
            return "";
        }
        return $this->ExportFieldCaption ? $this->caption() : $this->Name;
    }

    // Export value
    public function exportValue(): mixed
    {
        return $this->ExportOriginalValue ? $this->CurrentValue : $this->ViewValue;
    }

    // Get temp image
    public function getTempImage(): array
    {
        $tmpimages = [];
        if ($this->DataType == DataType::BLOB) {
            $wrkdata = $this->Upload->DbValue;
            if (is_resource($wrkdata) && get_resource_type($wrkdata) == "stream") { // Byte array
                $wrkdata = stream_get_contents($wrkdata);
            }
            if (!empty($wrkdata)) {
                if ($this->ImageResize) {
                    $wrkwidth = $this->ImageWidth;
                    $wrkheight = $this->ImageHeight;
                    ResizeBinary($wrkdata, $wrkwidth, $wrkheight);
                }
                $tmpimages[] = TempImage($wrkdata);
            }
        } else {
            $wrkfile = $this->HtmlTag == "FILE" ? $this->Upload->DbValue : $this->DbValue;
            if (empty($wrkfile)) {
                $wrkfile = $this->CurrentValue;
            }
            if (!empty($wrkfile)) {
                if (!$this->UploadMultiple) {
                    $imagefn = $this->uploadPath() . $wrkfile;
                    if (FileExists($imagefn)) {
                        $wrkdata = ReadFile($imagefn);
                        if ($this->ImageResize) {
                            $wrkwidth = $this->ImageWidth;
                            $wrkheight = $this->ImageHeight;
                            ResizeBinary($wrkdata, $wrkwidth, $wrkheight);
                        }
                        $tmpimages[] = TempImage($wrkdata);
                    }
                } else {
                    $tmpfiles = explode(Config("MULTIPLE_UPLOAD_SEPARATOR"), $wrkfile);
                    foreach ($tmpfiles as $tmpfile) {
                        if ($tmpfile != "") {
                            $imagefn = $this->uploadPath() . $tmpfile;
                            if (!FileExists($imagefn)) {
                                continue;
                            }
                            $wrkdata = ReadFile($imagefn);
                            if ($this->ImageResize) {
                                $wrkwidth = $this->ImageWidth;
                                $wrkheight = $this->ImageHeight;
                                ResizeBinary($wrkdata, $wrkwidth, $wrkheight);
                            }
                            $tmpimages[] = TempImage($wrkdata);
                        }
                    }
                }
            }
        }
        return $tmpimages;
    }

    // Form value
    public function setFormValue(mixed $v, bool $current = true, bool $validate = true): static
    {
        if (!$this->Raw && $this->DataType != DataType::XML) {
            $v = RemoveXss($v);
        }
        return $this->setRawFormValue($v, $current, $validate);
    }

    // Form value (Raw)
    public function setRawFormValue(mixed $v, bool $current = true, bool $validate = true): static
    {
        if (is_array($v)) {
            $v = implode(Config("MULTIPLE_OPTION_SEPARATOR"), $v);
        }
        if ($validate && $this->DataType == DataType::NUMBER && !IsNumeric($v) && !IsEmpty($v)) { // Check data type if server validation disabled
            $this->FormValue = null;
        } else {
            $this->FormValue = $v;
        }
        if ($current) {
            $this->CurrentValue = $v; // Don't use FormValue which is already converted to ?string
        }
        return $this;
    }

    // Edit value
    public function setEditValue(mixed $v, bool $current = true): static
    {
        $this->EditValue = $v;
        if ($current) {
            $this->CurrentValue = $this->EditValue;
        }
        return $this;
    }

    // View value
    public function setViewValue(mixed $v, bool $current = true): static
    {
        $this->ViewValue = $v;
        if ($current) {
            $this->CurrentValue = $this->ViewValue;
        }
        return $this;
    }

    // Current value
    public function setCurrentValue(mixed $v): static
    {
        $this->CurrentValue = $v;
        return $this;
    }

    // Old value
    public function setOldValue(mixed $v): static
    {
        if (is_array($v)) {
            $v = implode(Config("MULTIPLE_OPTION_SEPARATOR"), $v);
        }
        if ($this->DataType == DataType::NUMBER && !IsNumeric($v) && !IsEmpty($v)) { // Check data type
            $this->OldValue = null;
        } else {
            $this->OldValue = $v;
        }
        return $this;
    }

    // QueryString value
    public function setQueryStringValue(mixed $v, bool $current = true): static
    {
        if (!$this->Raw) {
            $v = RemoveXss($v);
        }
        return $this->setRawQueryStringValue($v, $current);
    }

    // QueryString value (Raw)
    public function setRawQueryStringValue(mixed $v, bool $current = true): static
    {
        if (is_array($v)) {
            $v = implode(Config("MULTIPLE_OPTION_SEPARATOR"), $v);
        }
        if ($this->DataType == DataType::NUMBER && !IsNumeric($v) && !IsEmpty($v)) { // Check data type
            $this->QueryStringValue = null;
        } else {
            $this->QueryStringValue = $v;
        }
        if ($current) {
            $this->CurrentValue = $this->QueryStringValue;
        }
        return $this;
    }

    // Database value
    public function setDbValue(mixed $v): static
    {
        $v = $this->CustomDataType?->convertToPHPValue($v, $this->Table->getConnection()->getDatabasePlatform()) ?? $v; // Convert to PHP value for custom data type
        if (IsFloatType($this->Type) && $v !== null) {
            $v = (float)$v;
        }
        $this->DbValue = $v;
        if ($this->isEncrypt() && $v != null) {
            $v = PhpDecrypt($v);
        }
        $this->CurrentValue = $v;
        return $this;
    }

    // Default value for NOT NULL field
    public function dbNotNullValue(): mixed
    {
        return match ($this->DataType) {
            DataType::NUMBER, DataType::BIT => 0,
            DataType::STRING, DataType::MEMO, DataType::XML, DataType::BLOB => "",
            DataType::BOOLEAN => false,
            DataType::DATE, DataType::TIME => new DateTime(),
            DataType::GUID => "{00000000-0000-0000-0000-000000000000}",
            default => null // Unknown
        };
    }

    // Set database value with error default
    public function setDbValueDef(mixed $value, bool $skip = false): mixed
    {
        $value = $this->CustomDataType?->convertToDatabaseValue($value, $this->Table->getConnection()->getDatabasePlatform()) ?? $value; // Convert to database value for custom data type
        $default = $this->Nullable ? null : $this->dbNotNullValue();
        $type = $this->Type;
        if ($this->getDataType() == DataType::BOOLEAN) {
            $type = 11; // Boolean
        }
        switch ($type) {
            case 2:
            case 3:
            case 16:
            case 17:
            case 18: // Integer
                $value = trim($value ?? "");
                $value = $this->Lookup === null
                    ? ParseInteger($value, "", \NumberFormatter::TYPE_INT32)
                    : (IsFormatted($value) ? ParseInteger($value, "", \NumberFormatter::TYPE_INT32) : $value);
                $dbValue = $value !== false && $value !== "" ? $value : $default;
                break;
            case 19:
            case 20:
            case 21: // Big integer
                $value = trim($value ?? "");
                $value = $this->Lookup === null
                    ? ParseInteger($value)
                    : (IsFormatted($value) ? ParseInteger($value) : $value);
                $dbValue = $value !== false && $value !== "" ? $value : $default;
                break;
            case 5:
            case 6:
            case 14:
            case 131: // Double
            case 139:
            case 4: // Single
                $value = trim($value ?? "");
                $value = $this->Lookup === null
                    ? ParseNumber($value)
                    : (IsFormatted($value) ? ParseNumber($value) : $value);
                $dbValue = $value !== false && $value !== "" ? $value : $default;
                break;
            case 7:
            case 133:
            case 135: // Date
            case 146: // DateTimeOffset
            case 134: // Time
            case 145:
                $value = trim($value ?? "");
                $dbValue = $value == "" ? $default : new DateTime($value); // Use DateTime object for update
                break;
            case 141: // XML
            case 201:
            case 203:
            case 129:
            case 130:
            case 200:
            case 202: // String
                $value = trim($value ?? "");
                $dbValue = $value == "" ? $default : ($this->isEncrypt() ? PhpEncrypt($value) : $value);
                break;
            case 128:
            case 204:
            case 205: // Binary
                $dbValue = $value ?? $default;
                break;
            case 72: // GUID
                $value = trim($value ?? "");
                $dbValue = $value != "" && CheckGuid($value) ? $value : $default;
                break;
            case 11: // Boolean
                if ($this->Nullable && $value === null) {
                    $dbValue = null;
                } elseif (is_bool($value)) {
                    $dbValue = $value;
                } elseif (!IsEmpty($value)) {
                    $dbValue = ConvertToBool($value, $this->Nullable);
                } else {
                    $dbValue = $default;
                }
                break;
            default:
                $dbValue = $value;
        }
        //$this->setDbValue($DbValue); // Do not override CurrentValue
        $this->OldValue = $this->DbValue; // Save old DbValue in OldValue
        $this->DbValue = $dbValue;
        return $dbValue;
    }

    // Get session value
    public function getSessionValue(): mixed
    {
        return Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . $this->FieldVar . "_SessionValue"));
    }

    // Set session value
    public function setSessionValue(mixed $v): void
    {
        Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . $this->FieldVar . "_SessionValue"), $v);
    }

    // HTML encode
    public function htmlDecode(mixed $v): mixed
    {
        return $this->Raw ? $v : HtmlDecode($v);
    }

    /**
     * Allowed file types (for jQuery File Upload)
     *
     * @return ?string Regular expression
     */
    public function acceptFileTypes(): ?string
    {
        return $this->UploadAllowedFileExt ? '/\\.(' . str_replace(",", "|", $this->UploadAllowedFileExt) . ')$/i' : null;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions(): array
    {
        if (is_array($this->Options) && count($this->Options) > 0) {
            return $this->Options;
        } elseif ($this->OptionCount > 0) {
            return $this->options(false); // User values
        } else {
            return $this->lookupOptions(); // Lookup table
        }
    }

    /**
     * Set options
     *
     * @param array $options Options with format [ ["lf" => "lv", "df" => "dv", ...], ...]
     * @return void
     */
    public function setOptions(array $options): void
    {
        $this->Options = $options;
    }

    /**
     * Client side search operators
     *
     * @return array
     */
    public function clientSearchOperators(): array
    {
        return array_map(fn($opr) => Config("CLIENT_SEARCH_OPERATORS")[$opr], $this->SearchOperators);
    }

    /**
     * Output client side list as JSON
     *
     * @param PageInterface $page Page
     * @return string
     */
    public function toClientList(PageInterface $page): string
    {
        $ar = [];
        if ($this->Lookup) {
            $options = $this->Lookup->hasParentTable() ? [] : $this->getOptions();
            $ar = array_merge($this->Lookup->toClientList($page), [
                "lookupOptions" => $options,
                "multiple" => $this->HtmlTag == "SELECT" && $this->SelectMultiple || $this->HtmlTag == "CHECKBOX" && !$this->isBoolean() // Do not use isMultiSelect() since data type could be int
            ]);
        }
        return ArrayToJson($ar);
    }

    /**
     * Get clone
     */
    public function getClone(...$values): static
    {
        if ($this->clone) {
            unset($this->clone);
        }
        return $this->clone = $this->with($values);
    }
}
