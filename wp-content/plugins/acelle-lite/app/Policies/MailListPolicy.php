<?php

namespace Acelle\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class MailListPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
    }

    public function read(\Acelle\Model\User $user, \Acelle\Model\MailList $item)
    {
        return $item->user_id == $user->id || $user->userGroup->backend_access;
    }

    public function create(\Acelle\Model\User $user, \Acelle\Model\MailList $item)
    {
        $max = $user->getOption('frontend', 'list_max');

        return $max > $user->lists()->count() || $max == -1;
    }

    public function update(\Acelle\Model\User $user, \Acelle\Model\MailList $item)
    {
        return $item->user_id == $user->id;
    }

    public function delete(\Acelle\Model\User $user, \Acelle\Model\MailList $item)
    {
        return $item->user_id == $user->id;
    }
}
