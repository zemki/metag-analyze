<?php

namespace App\Http\Middleware;

use App\Exceptions\DatabaseBoundaryViolation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Keeps MeTag and MART apart at request level.
 *
 * `boundary:metag` guards the MeTag API. Any use of the MART database throws,
 * or is only logged while `database.metag_boundary_enforced` is off.
 *
 * `boundary:mart` watches the MART routes. They may use `users`, `projects` and
 * `cases` in the main database, and anything else they touch there is logged.
 *
 * Reports name tables only, never SQL, so no participant data reaches the log.
 */
class DatabaseBoundary
{
    private const MAIN_TABLES_MART_MAY_USE = ['users', 'projects', 'cases'];

    private const TRANSACTION = '(transaction)';

    public function handle(Request $request, Closure $next, string $side)
    {
        $isMetag = $side === 'metag';
        $watching = true;
        $tables = [];

        $record = function (array $touched) use (&$watching, &$tables, $isMetag, $request): void {
            if (! $watching) {
                return;
            }

            $tables = array_values(array_unique([...$tables, ...$touched]));

            if ($isMetag && config('database.metag_boundary_enforced')) {
                throw new DatabaseBoundaryViolation(
                    "{$this->routeName($request)} tried to use the MART database: " . implode(', ', $touched)
                );
            }
        };

        // Callbacks can't be removed from a connection, so $watching ends them
        // with the request. beforeExecuting doesn't see beginTransaction, which
        // has its own hook.
        $connection = DB::connection($isMetag ? 'mart' : 'mysql');
        $connection->beforeExecuting(fn (string $query) => $record($this->tablesIn($query)));
        $connection->beforeStartingTransaction(fn () => $record([self::TRANSACTION]));

        try {
            return $next($request);
        } finally {
            $watching = false;
            $this->report($request, $isMetag, $tables);
        }
    }

    private function report(Request $request, bool $isMetag, array $tables): void
    {
        $reportable = $isMetag ? $tables : array_values(array_diff($tables, self::MAIN_TABLES_MART_MAY_USE));

        if ($reportable === []) {
            return;
        }

        $context = ['route' => $this->routeName($request), 'tables' => $reportable];

        if ($isMetag) {
            Log::error('MeTag route used the MART database', $context);
        } else {
            Log::warning('MART route used unexpected main database tables', $context);
        }
    }

    /** The URI pattern, not the URL: a URL can hold a case name or other participant data. */
    private function routeName(Request $request): string
    {
        return $request->method() . ' ' . $request->route()?->uri();
    }

    /** @return string[] */
    private function tablesIn(string $query): array
    {
        preg_match_all('/\b(?:from|into|update|join)\s+`?(\w+)`?/i', $query, $matches);

        return $matches[1] ?: ['(unknown)'];
    }
}
