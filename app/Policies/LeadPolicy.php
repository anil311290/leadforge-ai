<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function view(User $user, Lead $lead): bool
    {
        return $user->isAdmin() || ($lead->owner_id === $user->id) || $lead->owner_id === null;
    }

    public function update(User $user, Lead $lead): bool
    {
        return $this->view($user, $lead);
    }

    public function claim(User $user, Lead $lead): bool
    {
        return $lead->owner_id === null;
    }
}