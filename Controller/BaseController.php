<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\Event;
use Newscoop\EventDispatcher\Events\GenericEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Base Controller.
 */
abstract class BaseController extends Controller
{
    /**
     * Dispatch event.
     *
     * @param string $name
     * @param Event  $event
     */
    protected function dispatchEvent($name, Event $event)
    {
        $this->get('event_dispatcher')->dispatch($name, $event);
    }

    /**
     * Dispatch notification event.
     *
     * @param string $name
     * @param mixed  $subscription
     */
    protected function dispatchNotificationEvent($name, $subscription)
    {
        $this->dispatchEvent($name, new GenericEvent($subscription));
    }

    /**
     * Checks if user has permission.
     *
     * @param string $permission Permission name
     *
     * @return bool
     */
    protected function hasPermission($permission)
    {
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();
        if (!$user->hasPermission($permission)) {
            throw new AccessDeniedException();
        }
    }
}
