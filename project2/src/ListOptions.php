<?php

namespace PHPMaker2026\Project1;

use DiDom\Document;
use DiDom\Element;

/**
 * List option collection class
 */
class ListOptions implements \ArrayAccess, \IteratorAggregate
{
    public array $Items = [];
    public string $CustomItem = "";
    public string $RowCnt = "";
    public string $TemplateType = "block";
    public string $TemplateId = "";
    public string $TemplateClassName = "";
    public int $RowSpan = 1;
    public string $ButtonClass = "";
    public string $GroupOptionName = "button";
    public bool $DropDownButtonFallback = true; // Fall back to button group/links if no need to use dropdown

    // Constructor
    public function __construct(
        public string $Tag = "div",
        public string $TagClassName = "",
        public string $TableVar = "",
        public bool $UseDropDownButton = false,
        public bool $UseButtonGroup = false,
        public string $DropDownButtonPhrase = "",
        public string $ButtonGroupClass = "",
        public string $DropDownAutoClose = "true", // true/inside/outside/false (see https://getbootstrap.com/docs/5.3/components/dropdowns/#auto-close-behavior)
    ) {
    }

    // Implements offsetSet
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->Items[] = $value;
        } else {
            $this->Items[$offset] = $value;
        }
    }

    // Implements offsetExists
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->Items[$offset]);
    }

    // Implements offsetUnset
    public function offsetUnset(mixed $offset): void
    {
        unset($this->Items[$offset]);
    }

    // Implements offsetGet
    public function offsetGet(mixed $offset): mixed
    {
        return $this->Items[$offset] ?? null;
    }

    // Implements IteratorAggregate
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->Items);
    }

    // Check visible
    public function visible(): bool
    {
        return array_any($this->Items, fn($item) => $item->Visible);
    }

    // Check group option visible
    public function groupOptionVisible(): bool
    {
        $cnt = 0;
        foreach ($this->Items as $item) {
            if (
                $item->Name != $this->GroupOptionName
                && ($item->Visible && $item->ShowInDropDown && $this->UseDropDownButton
                || $item->Visible && $item->ShowInButtonGroup && $this->UseButtonGroup)
            ) {
                $cnt += 1;
                if ($this->UseDropDownButton && $cnt > 1 || $this->UseButtonGroup) {
                    return true;
                }
            }
        }
        return false;
    }

    // Add and return the new option
    public function add(string $name): ListOption
    {
        $item = new ListOption($name);
        $item->Parent = $this;
        $this->Items[$name] = $item;
        return $item;
    }

    // Add group option and return the new option
    public function addGroupOption(): ListOption
    {
        $item = $this->add($this->GroupOptionName);
        return $item;
    }

    // Load default settings
    public function loadDefault(): void
    {
        $this->CustomItem = "";
        foreach ($this->Items as $key => $item) {
            $this->Items[$key]->Body = "";
        }
    }

    // Hide all options
    public function hideAllOptions(array $lists = []): void
    {
        foreach ($this->Items as $key => $item) {
            if (!in_array($key, $lists)) {
                $this->Items[$key]->Visible = false;
            }
        }
    }

    // Show all options
    public function showAllOptions(): void
    {
        foreach ($this->Items as $key => $item) {
            $this->Items[$key]->Visible = true;
        }
    }

    /**
     * Get item by name (same as offsetGet)
     *
     * @param string $name Predefined names: view/edit/copy/delete/detail_<DetailTable>/userpermission/checkbox
     * @return ?ListOption
     */
    public function getItem(string $name): ?ListOption
    {
        return $this->Items[$name] ?? null;
    }

    // Get item position
    public function itemPos(string $name): int|bool
    {
        $pos = 0;
        foreach ($this->Items as $item) {
            if ($item->Name == $name) {
                return $pos;
            }
            $pos++;
        }
        return false;
    }

    // Get count
    public function count(): int
    {
        return count($this->Items);
    }

    // Get visible item count
    public function visibleCount(): int
    {
        return $this->UseDropDownButton || $this->UseButtonGroup
            ? 1
            : array_reduce($this->Items, fn($cnt, $item) => $cnt + ($item->Visible ? 1 : 0), 0);
    }

    // Move item to position
    public function moveItem(string $name, int $pos): void
    {
        $cnt = $this->count();
        if ($pos < 0) { // If negative, count from the end
            $pos = $cnt + $pos;
        }
        if ($pos < 0) {
            $pos = 0;
        }
        if ($pos >= $cnt) {
            $pos = $cnt - 1;
        }
        $item = $this->getItem($name);
        if ($item) {
            unset($this->Items[$name]);
            $this->Items = array_merge(
                array_slice($this->Items, 0, $pos),
                [$name => $item],
                array_slice($this->Items, $pos)
            );
        }
    }

    // Render list options
    public function render(
        string $part,
        string $pos = "",
        string $rowCnt = "",
        string $templateType = "block",
        string $templateId = "",
        string $templateClassName = "",
        bool $output = true): string
    {
        if (!$this->visible()) {
            return "";
        }
        if ($this->CustomItem == "" && ($groupItem = $this->getItem($this->GroupOptionName)) && $this->showPos($groupItem->OnLeft, $pos)) {
            $useDropDownButton = $this->UseDropDownButton;
            $useButtonGroup = $this->UseButtonGroup;
            if ($useDropDownButton) { // Render dropdown
                $buttonValue = "";
                $cnt = 0;
                foreach ($this->Items as $item) {
                    if ($item->Name != $this->GroupOptionName && $item->Visible) {
                        if ($item->ShowInDropDown) {
                            $buttonValue .= $item->Body;
                            $cnt += 1;
                        } elseif ($item->Name == "listactions") { // Show listactions as button group
                            $item->Body = $this->renderButtonGroup($item->Body, $pos);
                        }
                    }
                }
                if ($this->DropDownButtonFallback && ($cnt < 1 || $cnt == 1 && !ContainsString($buttonValue, "dropdown-menu"))) { // No item to show in dropdown or only one item without dropdown menu
                    $useDropDownButton = false; // No need to use drop down button
                } else {
                    $dropdownButtonClass = !ContainsString($this->TagClassName, "ew-multi-column-list-option-card") ? "btn-default" : "";
                    AppendClass($dropdownButtonClass, "btn dropdown-toggle");
                    $groupItem->Body = $this->renderDropDownButton($buttonValue, $pos, $dropdownButtonClass);
                    $groupItem->Visible = true;
                }
            }
            if (!$useDropDownButton) {
                if ($useButtonGroup) { // Render button group
                    $visible = false;
                    $buttonGroups = [];
                    foreach ($this->Items as $item) {
                        if ($item->Name != $this->GroupOptionName && $item->Visible && $item->Body != "") {
                            if ($item->ShowInButtonGroup) {
                                $visible = true;
                                $buttonValue = $item->Body;
                                if (!array_key_exists($item->ButtonGroupName, $buttonGroups)) {
                                    $buttonGroups[$item->ButtonGroupName] = "";
                                }
                                $buttonGroups[$item->ButtonGroupName] .= $buttonValue;
                            } elseif ($item->Name == "listactions") { // Show listactions as button group
                                $item->Body = $this->renderButtonGroup($item->Body, $pos);
                            }
                        }
                    }
                    $groupItem->Body = implode(array_map(fn($buttonValue) => $this->renderButtonGroup($buttonValue, $pos), array_values($buttonGroups)));
                    if ($visible) {
                        $groupItem->Visible = true;
                    }
                } else { // Render links as button links
                    foreach ($this->Items as $item) {
                        if (in_array($item->Name, ["view", "edit", "copy", "delete"])) { // Show actions as button links
                            $item->Body = $this->renderButtonLinks($item->Body);
                        }
                    }
                }
            }
        }
        if ($templateId != "") {
            $html = "";
            if ($pos == "right" || StartsText("bottom", $pos)) { // Show all options script tags on the right/bottom (ignore left to avoid duplicate)
                $html = $this->write($part, "", $rowCnt, "block", $templateId, $templateClassName) .
                    $this->write($part, "", $rowCnt, "inline", $templateId) .
                    $this->write($part, "", $rowCnt, "single", $templateId);
            }
        } else {
            $html = $this->write($part, $pos, $rowCnt, $templateType, $templateId, $templateClassName);
        }
        if ($output) {
            echo $html;
            return "";
        } else {
            return $html;
        }
    }

    // Get custom template tag
    protected function customTemplateTag(string $templateId, string $templateType, string $templateClass, string $rowCnt = ""): string
    {
        $id = "_" . $templateId;
        if (!IsEmpty($rowCnt)) {
            $id = $rowCnt . $id;
        }
        $id = "tp" . $templateType . $id;
        return "<template id=\"" . $id . "\"" . (!IsEmpty($templateClass) ? " class=\"" . $templateClass . "\"" : "") . ">";
    }

    // Write list options
    protected function write(string $part, string $pos = "", string $rowCnt = "", string $templateType = "block", string $templateId = "", string $templateClass = ""): string
    {
        $this->RowCnt = $rowCnt;
        $this->TemplateType = $templateType;
        $this->TemplateId = $templateId;
        $this->TemplateClassName = $templateClass;
        $res = "";
        $tag = $this->Tag; // Save Tag
        if ($templateId != "") {
            if ($templateType != "block") {
                AppendClass($this->TagClassName, "d-inline-block");
            } else {
                RemoveClass($this->TagClassName, "d-inline-block");
            }
            if ($templateType == "block") {
                if ($part == "header") {
                    $res .= $this->customTemplateTag($templateId, "oh", $templateClass);
                } elseif ($part == "body") {
                    $res .= $this->customTemplateTag($templateId, "ob", $templateClass, $rowCnt);
                } elseif ($part == "footer") {
                    $res .= $this->customTemplateTag($templateId, "of", $templateClass);
                }
            } elseif ($templateType == "inline") {
                $this->Tag = "div"; // Use div
                if ($part == "header") {
                    $res .= $this->customTemplateTag($templateId, "o2h", $templateClass);
                } elseif ($part == "body") {
                    $res .= $this->customTemplateTag($templateId, "o2b", $templateClass, $rowCnt);
                } elseif ($part == "footer") {
                    $res .= $this->customTemplateTag($templateId, "o2f", $templateClass);
                }
            }
        } else {
            if (!$pos || StartsText("top", $pos) || StartsText("bottom", $pos) || $templateType != "block") { // Use inline tag for multi-column
                AppendClass($this->TagClassName, "d-inline-block");
            }
        }
        if ($this->CustomItem != "") {
            $cnt = 0;
            $opt = null;
            foreach ($this->Items as $item) {
                if ($this->showItem($item, $templateId, $pos)) {
                    $cnt++;
                }
                if ($item->Name == $this->CustomItem) {
                    $opt = $item;
                }
            }
            $useButtonGroup = $this->UseButtonGroup; // Backup options
            $this->UseButtonGroup = true; // Show button group for custom item
            if (is_object($opt) && $cnt > 0) {
                if ($templateId != "" || $this->showPos($opt->OnLeft, $pos)) {
                    $res .= $opt->render($part, $cnt, $pos);
                } else {
                    $res .= $opt->render("", $cnt, $pos);
                }
            }
            $this->UseButtonGroup = $useButtonGroup; // Restore options
        } else {
            foreach ($this->Items as $item) {
                if ($this->showItem($item, $templateId, $pos)) {
                    $res .= $item->render($part, 1, $pos);
                }
            }
        }
        if (in_array($templateType, ["block", "inline"]) && $templateId != "") {
            $res .= "</template>"; // End <template id="...">
            $this->Tag = $tag; // Restore Tag
        }
        return $res;
    }

    // Show item
    protected function showItem(ListOption $item, string $templateId, string $pos): bool
    {
        $show = $item->Visible && $this->showPos($item->OnLeft, $pos);
        if ($show) {
            $groupItemVisible = $this->getItem($this->GroupOptionName)?->Visible ?? false;
            if ($this->UseDropDownButton) { // Group item / Item not in dropdown / Item in dropdown + Group item not visible
                $show = $item->Name == $this->GroupOptionName && $groupItemVisible || !$item->ShowInDropDown || $item->ShowInDropDown && !$groupItemVisible;
            } elseif ($this->UseButtonGroup) { // Group item / Item not in button group / Item in button group + Group item not visible
                $show = $item->Name == $this->GroupOptionName && $groupItemVisible || !$item->ShowInButtonGroup || $item->ShowInButtonGroup && !$groupItemVisible;
            }
        }
        return $show;
    }

    // Show position
    protected function showPos(bool $onLeft, string $pos): bool
    {
        return $onLeft && $pos == "left" || !$onLeft && $pos == "right" || $pos == "" || StartsText("top", $pos) || StartsText("bottom", $pos);
    }

    /**
     * Concat options and return concatenated HTML
     *
     * @param string $pattern Regular expression pattern for matching the option names, e.g. '/^detail_/'
     * @param string $separator optional Separator
     * @return string
     */
    public function concat(string $pattern, string $separator = ""): string
    {
        $ar = [];
        $keys = array_keys($this->Items);
        foreach ($keys as $key) {
            if (preg_match($pattern, $key) && trim($this->Items[$key]->Body) != "") {
                $ar[] = $this->Items[$key]->Body;
            }
        }
        return implode($separator, $ar);
    }

    /**
     * Merge options to the first option and return it
     *
     * @param string $pattern Regular expression pattern for matching the option names, e.g. '/^detail_/'
     * @param string $separator optional Separator
     * @return ?ListOption
     */
    public function merge(string $pattern, string $separator = ""): ?ListOption
    {
        $keys = array_keys($this->Items);
        $first = null;
        foreach ($keys as $key) {
            if (preg_match($pattern, $key)) {
                if (!$first) {
                    $first = $this->Items[$key];
                    $first->Body = $this->concat($pattern, $separator);
                } else {
                    $this->Items[$key]->Visible = false;
                }
            }
        }
        return $first;
    }

    // Get button links
    public function renderButtonLinks(string $body): string
    {
        if (IsEmpty($body)) {
            return $body;
        }
        $doc = new Document(null, false, PROJECT_ENCODING);
        @$doc->load($body);

        // Get and remove <input type="hidden"> and <div class="btn-group">
        $html = array_reduce($doc->find('div.btn-group, input[type=hidden]'), function ($res, $el) {
            $res .= $el->toDocument()->format()->html();
            $el->remove();
            return $res;
        }, '');

        // Get <a> and <button>
        $btnClass = $this->ButtonClass;
        $links = array_reduce($doc->find('a, button'), function ($res, $button) use ($btnClass) {
            if ($button->tagName() == 'a') {
                $attrs = $button->attributes();
                $attrs['type'] = 'button';
                $action = $attrs['data-ew-action'] ?? '';
                $href = $attrs['href'] ?? '';
                if (IsEmpty($action) && !IsEmpty($href)) {
                    if (str_starts_with($href, 'javascript:')) {
                        if (AllowInlineScript()) {
                            $attrs['onclick'] = preg_replace('/^javascript:/', '', $attrs['href']);
                        }
                    } else {
                        $attrs['data-ew-action'] = 'redirect';
                        $attrs['data-url'] = $attrs['href'];
                    }
                    unset($attrs['href']);
                    $element = new Element('button', '', $attrs); // Change links to button
                    $element->appendChild($button->children());
                    $button = $element;
                }
            }
            $class = $button->getAttribute('class');
            PrependClass($class, 'btn btn-xs btn-link');
            $button->setAttribute('class', AppendClass($class, $btnClass)); // Add button classes
            return $res . $button->toDocument()->format()->html();
        }, '');
        return $links . $html;
    }

    // Get button group link
    public function renderButtonGroup(string $body, string $pos): string
    {
        if (IsEmpty($body)) {
            return $body;
        }
        $doc = new Document(null, false, PROJECT_ENCODING);
        @$doc->load($body);

        // Get and remove <input type="hidden"> and <div class="btn-group">
        $html = array_reduce($doc->find('div.btn-group, input[type=hidden]'), function ($res, $el) {
            $res .= $el->toDocument()->format()->html();
            $el->remove();
            return $res;
        }, '');

        // Get <a> and <button>
        $btnClass = $this->ButtonClass;
        $links = array_reduce($doc->find('a, button'), function ($res, $button) use ($btnClass) {
            $class = $button->getAttribute('class');
            PrependClass($class, 'btn btn-default');
            $button->setAttribute('class', AppendClass($class, $btnClass)); // Add button classes
            return $res . $button->toDocument()->format()->html();
        }, '');
        $btngroupClass = 'btn-group btn-group-sm ew-btn-group ew-list-options' . (StartsText('bottom', $pos) ? ' dropup' : '');
        $btngroup = $links ? '<div class="' . $btngroupClass . '">' . $links . '</div>' : '';
        return $btngroup . $html;
    }

    // Render drop down button
    public function renderDropDownButton(string $body, string $pos, string $dropdownButtonClass): string
    {
        if (IsEmpty($body)) {
            return $body;
        }
        $doc = new Document(null, false, PROJECT_ENCODING);
        @$doc->load($body);

        // Get and remove <div class="d-none"> and <input type="hidden">
        $html = array_reduce($doc->find('div.d-none, input[type=hidden]'), function ($res, $el) {
            $res .= $el->toDocument()->format()->html();
            $el->remove();
            return $res;
        }, '');

        // Get <a> and <button> without data-bs-toggle attribute
        $buttons = $doc->find('a:not([data-bs-toggle]), button:not([data-bs-toggle])');
        $links = '';
        $submenu = false;
        $submenulink = '';
        $submenulinks = '';
        foreach ($buttons as $button) {
            $action = $button->getAttribute('data-action');
            $classes = $button->getAttribute('class') ?? "";
            if (!preg_match('/\bdropdown-item\b/', $classes ?: "")) { // Skip if already dropdown-item
                $classes = preg_replace('/btn[\S]*\s+/i', '', $classes); // Remove btn classes
                $button->removeAttribute('title'); // Remove title
                $caption = $button->text();
                $htmlTitle = HtmlTitle($caption); // Match data-caption='caption' or span.visually-hidden
                $caption = ($htmlTitle != $caption) ? $htmlTitle : $caption;
                $button->setAttribute('class', AppendClass($classes, 'dropdown-item'));
                if (SameText($button->tagName(), 'a') && !$button->getAttribute('href')) { // Add href for <a>
                    $button->setAttribute('href', '#');
                }
                $icon = $button->find('i.ew-icon')[0] ?? null; // Icon classes contains 'ew-icon'
                $badge = $button->find('span.badge');
                if (!$badge) { // Skip span.badge
                    if ($caption !== "" && $icon) { // Has both caption and icon
                        $classes = $icon->getAttribute('class');
                        $icon->setAttribute('class', AppendClass($classes, 'me-2')); // Add margin-right to icon
                    }
                    $children = $button->children();
                    foreach ($children as $child) {
                        $child->remove();
                    }
                    if ($icon) {
                        $button->appendChild($icon);
                    }
                    if ($caption !== "") { // Has caption
                        $button->appendChild($doc->createTextNode($caption));
                    }
                }
            }
            $link = $button->toDocument()->format()->html();
            if ($action == 'list') { // Start new submenu
                if ($submenu) { // End previous submenu
                    if ($submenulinks != '') { // Set up submenu
                        $links .= '<li class="dropdown-submenu dropdown-hover">' . str_replace('dropdown-item', 'dropdown-item dropdown-toggle', $submenulink) . '<ul class="dropdown-menu">' . $submenulinks . '</ul></li>';
                    } else {
                        $links .= '<li>' . $submenulink . '</li>';
                    }
                }
                $submenu = true;
                $submenulink = $link;
                $submenulinks = '';
            } else {
                if ($action == '' && $submenu) { // End previous submenu
                    if ($submenulinks != '') { // Set up submenu
                        $links .= '<li class="dropdown-submenu dropdown-hover">' . $submenulink . '<ul class="dropdown-menu">' . $submenulinks . '</ul></li>';
                    } else {
                        $links .= '<li>' . $submenulink . '</li>';
                    }
                    $submenu = false;
                }
                if ($submenu) {
                    $submenulinks .= '<li>' . $link . '</li>';
                } else {
                    $links .= '<li>' . $link . '</li>';
                }
            }
        }
        $btndropdown = '';
        if ($links != '' || $submenu && $submenulinks != '') {
            if ($submenu) { // End previous submenu
                if ($submenulinks != '') { // Set up submenu
                    $links .= '<li class="dropdown-submenu dropdown-hover">' . $submenulink . '<ul class="dropdown-menu">' . $submenulinks . '</ul></li>';
                } else {
                    $links .= '<li>' . $submenulink . '</li>';
                }
            }
            $btnclass = $dropdownButtonClass;
            AppendClass($btnclass, $this->ButtonClass);
            $btngrpclass = 'btn-group btn-group-sm ew-btn-dropdown' . (StartsText('bottom', $pos) ? ' dropup' : '');
            AppendClass($btngrpclass, $this->ButtonGroupClass);
            $buttontitle = HtmlTitle($this->DropDownButtonPhrase);
            $buttontitle = ($this->DropDownButtonPhrase != $buttontitle) ? $buttontitle : "";
            $button = '<button type="button" class="' . $btnclass . '" data-title="' . $buttontitle . '" data-bs-toggle="dropdown" data-bs-auto-close="' . $this->DropDownAutoClose . '">' . $this->DropDownButtonPhrase . '</button>' .
                '<ul class="dropdown-menu ' . ($pos == 'right' || EndsText('end', $pos) ? 'dropdown-menu-end ' : '') . 'ew-dropdown-menu ew-list-options">' . $links . '</ul>';
            $btndropdown = '<div class="' . $btngrpclass . '" data-table="' . $this->TableVar . '">' . $button . '</div>'; // Use dropup for bottom
        }
        return $btndropdown . $html;
    }

    // Hide detail items for dropdown
    public function hideDetailItemsForDropDown(): void
    {
        $showDetails = false;
        if ($this->UseDropDownButton) {
            foreach ($this->Items as $item) {
                if ($item->Name != $this->GroupOptionName && $item->Visible && $item->ShowInDropDown && !StartsString("detail_", $item->Name)) {
                    $showDetails = true;
                    break;
                }
            }
        }
        if (!$showDetails) {
            $this->hideDetailItems();
        }
    }

    // Hide detail items
    public function hideDetailItems(): void
    {
        foreach ($this->Items as $item) {
            if (StartsString("detail_", $item->Name)) {
                $item->Visible = false;
            }
        }
    }

    // Detail items is visible
    public function detailVisible(): bool
    {
        return array_any($this->Items, fn($item) => StartsString("detail_", $item->Name) && $item->Visible);
    }
}
