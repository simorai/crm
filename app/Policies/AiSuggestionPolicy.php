<?php

namespace App\Policies;

use App\Models\AiSuggestion;
use App\Models\User;

class AiSuggestionPolicy
{
    public function viewAny(User $user): bool
    {
        return app()->bound('current.tenant');
    }

    public function view(User $user, AiSuggestion $suggestion): bool
    {
        if (! app()->bound('current.tenant')) {
            return false;
        }
        if ($suggestion->tenant_id !== app('current.tenant')->id) {
            abort(404);
        }
        return true;
    }

    public function accept(User $user, AiSuggestion $suggestion): bool
    {
        return $this->view($user, $suggestion);
    }

    public function dismiss(User $user, AiSuggestion $suggestion): bool
    {
        return $this->view($user, $suggestion);
    }

    public function postpone(User $user, AiSuggestion $suggestion): bool
    {
        return $this->view($user, $suggestion);
    }
}