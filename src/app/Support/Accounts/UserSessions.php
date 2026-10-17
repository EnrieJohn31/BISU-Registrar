<?php

namespace App\Support\Accounts;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UserSessions
{
    /**
     * End every stored session of the user. Only the database session driver can
     * do this server-side; open sessions on other drivers are ended by the
     * account-status middleware on their next request.
     */
    public static function endAll(User $user): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $connection = config('session.connection');
        $table = config('session.table', 'sessions');

        DB::connection(is_string($connection) ? $connection : null)
            ->table(is_string($table) ? $table : 'sessions')
            ->where('user_id', $user->id)
            ->delete();
    }
}
