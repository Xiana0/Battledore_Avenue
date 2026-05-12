<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter as BaseVoter;

/**
 * API Permission Voter
 */
class ApiPermissionVoter extends BaseVoter
{

    public function __construct(
        protected RequestStack $requestStack,
        protected AdvancedSecurity $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return false;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        $routeParams = $request->attributes->get('_route_params');
        $action = RouteAction($request);
        $table = '';
        $shouldVote = match ($action) {
            Config('API_LOOKUP_ACTION'),
            Config('API_EXPORT_CHART_ACTION'),
            Config('API_2FA_ACTION') => true,
            Config('API_JQUERY_UPLOAD_ACTION') => $request->isMethod('POST'),
            default => false,
        };

        // Check user levels
        if ($shouldVote) {
            if (empty($this->security->UserLevelIDs) || !array_any($this->security->UserLevelIDs, fn (int $id) => $id >= AdvancedSecurity::ANONYMOUS_USER_LEVEL_ID)) {
                $vote?->addReason('Access denied: User levels not set or insufficient');
                return false; // Not authorized
            }
        }

        // Actions for table
        $apiTableActions = [
            Config('API_EXPORT_ACTION'),
            Config('API_LIST_ACTION'),
            Config('API_VIEW_ACTION'),
            Config('API_ADD_ACTION'),
            Config('API_EDIT_ACTION'),
            Config('API_DELETE_ACTION'),
            Config('API_FILE_ACTION')
        ];
        if (in_array($action, $apiTableActions)) {
            $table = $routeParams['table'] ?? Param(Config('API_OBJECT_NAME')); // Get from route or Get/Post
        }

        // No security
        return true;
    }
}
