<?php

namespace App\Policies;

use App\Models\User;
use App\Models\QuoteRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuoteRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the quote request.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\QuoteRequest  $quoteRequest
     * @return mixed
     */
    public function view(User $user, QuoteRequest $quoteRequest)
    {
        // Cho phép admin hoặc người dùng sở hữu yêu cầu
        return $user->hasRole('admin') || $user->hasRole('quote manager') || $user->id === $quoteRequest->user_id;
    }

    /**
     * Determine whether the user can create a quote for the quote request.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\QuoteRequest  $quoteRequest
     * @return mixed
     */
    public function createQuote(User $user, QuoteRequest $quoteRequest)
    {
        // Cho phép admin hoặc người dùng sở hữu yêu cầu
        return $user->hasRole('admin') || $user->hasRole('quote manager') ||  $user->id === $quoteRequest->user_id;
    }

    // Các phương thức khác nếu cần...
}
