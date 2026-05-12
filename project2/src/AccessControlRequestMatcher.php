<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * Checks if the request requires login
 */
class AccessControlRequestMatcher implements RequestMatcherInterface
{
    /**
     * Matches request for firewall
     *
     * @param $request Request
     * @return bool Return true to handle the request by the firewall, return false to exclude the request
     */
    public function matches(Request $request): bool
    {
        $routeName = $request->attributes->get('_route', '');
        [$pageAction, $table] = explode('.', $routeName) + [null, null]; // Make sure at least two elements

        // Check permission
        $match = false;
        $security = Security();
        if ($table != '') { // Table
            $security->loadTablePermissions($table); // For anonymous user
            if (
                $pageAction == Config('VIEW_ACTION') && !$security->canView()
                || in_array($pageAction, [Config('EDIT_ACTION'), Config('UPDATE_ACTION')]) && !$security->canEdit()
                || $pageAction == Config('ADD_ACTION') && !$security->canAdd()
                || $pageAction == Config('DELETE_ACTION') && !$security->canDelete()
                || in_array($pageAction, [Config('SEARCH_ACTION'), Config('QUERY_ACTION')]) && !$security->canSearch()
            ) {
                $match = !$security->canList();
            } elseif (
                in_array($pageAction, [
                    Config('LIST_ACTION'),
                    Config('CUSTOM_REPORT_ACTION'),
                    Config('SUMMARY_REPORT_ACTION'),
                    Config('CROSSTAB_REPORT_ACTION'),
                    Config('DASHBOARD_REPORT_ACTION'),
                    Config('CALENDAR_REPORT_ACTION')
                ]) && !$security->canList()
            ) {
                $match = true;
            }
        } else { // Others
            if (in_array($pageAction, ['changepassword', 'personaldata', 'userpriv', 'login2fa'])) { // Change password / Personal data / User priv
                $match = true;
            }
        }
        return $match;
    }
}
