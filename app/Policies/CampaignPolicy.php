<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    public function view(User $user, Campaign $campaign): bool
    {
        return $user->isAdmin() || $user->id === $campaign->user_id;
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return $this->view($user, $campaign);
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $user->isAdmin();
    }
}