<?php

namespace PHPMaker2026\Project1;

/**
 * Breadcrumb class
 */
class Breadcrumb
{
    public array $Links = [];
    public array $SessionLinks = []; // History
    public bool $Visible = true;
    public static string $CssClass = "breadcrumb float-sm-end ew-breadcrumbs";
    public static int $MaxSessionLinks = 20;

    // Constructor
    public function __construct(?string $homePage = null)
    {
        $homePage ??= Config("HOME_PAGE");
        $fn = PROJECT_NAMESPACE . $homePage;
        if (is_callable($fn)) {
            $homePage = $fn();
        }
        $this->Links[] = ["home", "HomePage", $homePage, "ew-home", "", false]; // Home
    }

    // Create a new instance
    public static function create(?string $homePage = null): static
    {
        return new static($homePage);
    }

    // Check if link exists
    public function exists(string $pageid, string $table, string $pageurl): bool
    {
        if (is_array($this->Links)) {
            $cnt = count($this->Links);
            for ($i = 0; $i < $cnt; $i++) {
                list($id, , $url, , $tablevar) = $this->Links[$i];
                if ($pageid == $id && $table == $tablevar && $pageurl == $url) {
                    return true;
                }
            }
        }
        return false;
    }

    // Check if two links are the same
    public function sameLink(array $link1, array $link2): bool
    {
        list($id, , $url, , $tablevar) = $link1;
        list($id2, , $url2, , $tablevar2) = $link2;
        return $id == $id2 && $url == $url2 && $tablevar == $tablevar2;
    }

    // Find master table link in session links
    public function findSessionLink(string $pageid, string $table): ?array
    {
        $cnt = count($this->SessionLinks);
        for ($i = $cnt - 1; $i >= 0; $i--) { // Find from the last
            list($id, , $url, , $tablevar) = $this->SessionLinks[$i];
            if ($pageid == $id && $table == $tablevar) {
                return $this->SessionLinks[$i];
            }
        }
        return null;
    }

    // Add breadcrumb
    public function add(string $pageid, string $pagetitle, string $pageurl, string $pageurlclass = "", string $table = "", bool $current = false): static
    {
        // Load session links
        $this->loadSession();

        // Get list of master tables
        $mastertables = [];
        if ($table != "") {
            $tableVar = $table;
            while ($tableVar = Session(AddTabId(PROJECT_NAME . "_" . $tableVar . "_" . Config("TABLE_MASTER_TABLE")))) {
                if (in_array($tableVar, $mastertables)) {
                    break;
                }
                $mastertables[] = $tableVar;
            }
        }

        // Add master links first
        $cnt = count($this->SessionLinks);
        $mastertables = array_reverse($mastertables);
        foreach ($mastertables as $mastertable) {
            if ($link = $this->findSessionLink("list", $mastertable)) {
                list($id, $title, $url, $cls, $tbl) = $link;
                if ($url == $pageurl) {
                    break;
                }
                if (!$this->exists($id, $tbl, $url)) {
                    $this->Links[] = [$id, $title, $url, $cls, $tbl, false];
                }
            }
        }

        // Add links
        if (!$this->exists($pageid, $table, $pageurl)) {
            $link = [$pageid, $pagetitle, $pageurl, $pageurlclass, $table, $current];
            $this->Links[] = $link;
            if ($pageid == "list" && !preg_match('/(\?|&)action=/', $pageurl) && (count($this->SessionLinks) == 0 || !$this->sameLink($link, end($this->SessionLinks)))) {
                $link[5] = false; // Set current as false
                $this->SessionLinks[] = $link;
            }
        }

        // Save session links
        $this->saveSession();
        return $this;
    }

    // Save links to Session
    public function saveSession(): void
    {
        $links = $this->SessionLinks;
        if (count($links) > self::$MaxSessionLinks) {
            $links = array_slice($links, self::$MaxSessionLinks * -1, self::$MaxSessionLinks); // Only keep last n links
        }
        Session(SESSION_BREADCRUMB, $links);
    }

    // Load links from Session
    public function loadSession(): void
    {
        $this->SessionLinks = is_array(Session(SESSION_BREADCRUMB)) ? Session(SESSION_BREADCRUMB) : [];
    }

    // Load language phrase
    public function languagePhrase(string $title, string $table, bool $current): string
    {
        $wrktitle = ($title == $table) ? Language()->tablePhrase($title, "TblCaption") : Language()->phrase($title);
        if ($current) {
            $wrktitle = "<span id=\"ew-page-caption\">" . $wrktitle . "</span>";
        }
        return $wrktitle;
    }

    // Render
    public function render(): string
    {
        if (!$this->Visible || Config("PAGE_TITLE_STYLE") == "" || Config("PAGE_TITLE_STYLE") == "None" || Config("PAGE_TITLE_STYLE") == "Caption") {
            return "";
        }
        $nav = "<ol class=\"" . self::$CssClass . "\">";
        if (is_array($this->Links)) {
            $cnt = count($this->Links);
            for ($i = 0; $i < $cnt; $i++) {
                list($id, $title, $url, $cls, $table, $cur) = $this->Links[$i];
                if ($i < $cnt - 1) {
                    $nav .= "<li class=\"breadcrumb-item\" id=\"ew-breadcrumb" . ($i + 1) . "\">";
                } else { // Last => Current page
                    $nav .= "<li class=\"breadcrumb-item active\" id=\"ew-breadcrumb" . ($i + 1) . "\">";
                    $url = ""; // No need to show URL for current page
                }
                $text = $this->languagePhrase($title, $table, $cur);
                $title = HtmlTitle($text);
                if ($url != "") {
                    $nav .= "<a href=\"" . GetUrl($url) . "\"";
                    if ($title != "" && $title != $text) {
                        $nav .= " title=\"" . HtmlEncode($title) . "\"";
                    }
                    if ($cls != "") {
                        $nav .= " class=\"" . $cls . "\"";
                    }
                    $nav .= ">" . $text . "</a>";
                } else {
                    $nav .= $text;
                }
                $nav .= "</li>";
            }
        }
        $nav .= "</ol>";
        return $nav;
    }
}
