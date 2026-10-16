<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Log in as every demo account and crawl the internal links on their desks.
 *
 * This runs against whatever database the app is configured to use (the dev
 * MySQL database by default), not a throw-away test database, so it exercises
 * the real seeded data. It reports every 5xx and every link-level 403 (a link
 * the UI offers that the viewer cannot open) and exits non-zero when it finds
 * one.
 */
#[Signature('app:crawl
            {--email= : Crawl only this account email}
            {--max=150 : Maximum links to request per account}')]
#[Description('Log in as each demo account and crawl their internal links')]
class CrawlDemoCommand extends Command
{
    /**
     * Links that are never crawled (destructive, external or non-GET).
     */
    private const SKIP_FRAGMENTS = [
        'logout', 'download', 'delete', 'destroy', '#', 'mailto:', 'tel:',
        'javascript:', 'unsubscribe', '/export',
    ];

    public function handle(): int
    {
        $kernel = app(Kernel::class);

        $query = User::query()->where('is_demo', true);

        if ($email = $this->option('email')) {
            $query->where('email', $email);
        }

        $users = $query->orderBy('id')->get();

        if ($users->isEmpty()) {
            $this->error('No demo accounts found for the current database.');

            return self::FAILURE;
        }

        $max = (int) $this->option('max');

        /** @var list<array{user:string,url:string,status:int,reason:string}> $failures */
        $failures = [];
        $requestCount = 0;

        foreach ($users as $user) {
            $start = $user->dashboard_url;

            Auth::setUser($user);

            $seen = [];
            $queue = [$start];
            $visited = 0;
            $startStatus = null;

            while ($queue !== [] && $visited < $max) {
                $url = array_shift($queue);
                $path = $this->pathOf($url);

                if ($path === null || isset($seen[$path])) {
                    continue;
                }

                $seen[$path] = true;
                $visited++;
                $requestCount++;

                $isStatic = str_starts_with($path, '/storage/');

                if ($isStatic) {
                    // Public storage is served by the web server in front of the
                    // app, never by the framework router, so simulate that: a file
                    // on disk is a 200, a missing file is a dead link.
                    $status = $this->staticFileStatus($path);
                    $content = null;
                } else {
                    [$status, $content] = $this->request($kernel, $path);
                }

                if ($startStatus === null) {
                    $startStatus = $status;
                }

                if ($status >= 500) {
                    $failures[] = ['user' => $user->email, 'url' => $path, 'status' => $status, 'reason' => 'server error'];
                } elseif ($status === 403) {
                    $failures[] = ['user' => $user->email, 'url' => $path, 'status' => 403, 'reason' => 'link offered but forbidden'];
                } elseif ($isStatic && $status === 404) {
                    $failures[] = ['user' => $user->email, 'url' => $path, 'status' => 404, 'reason' => 'referenced file missing'];
                }

                if ($status === 200 && is_string($content)) {
                    foreach ($this->internalLinks($content) as $link) {
                        if (! isset($seen[$this->pathOf($link) ?? ''])) {
                            $queue[] = $link;
                        }
                    }
                }
            }

            if ($startStatus !== 200) {
                $failures[] = ['user' => $user->email, 'url' => $this->pathOf($start) ?? $start, 'status' => (int) $startStatus, 'reason' => 'desk did not load'];
            }

            $this->line(sprintf(
                '%s (%s): start %d, %d page(s), %s',
                $user->email,
                $user->role,
                (int) $startStatus,
                $visited,
                $start,
            ));
        }

        Auth::logout();

        $this->newLine();
        $this->info("Crawled {$requestCount} request(s) across {$users->count()} account(s).");

        if ($failures === []) {
            $this->info('No 5xx and no link-level 403 found.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->error(count($failures).' problem(s) found:');
        $this->table(
            ['account', 'url', 'status', 'reason'],
            array_map(fn (array $f): array => [$f['user'], $f['url'], $f['status'], $f['reason']], $failures)
        );

        return self::FAILURE;
    }

    /**
     * Whether a `/storage/...` link maps to a file the web server would serve.
     *
     * Public storage lives under `storage/app/public` and is exposed through the
     * `public/storage` symlink, so the framework router never sees it. Returns
     * 200 when the file exists, 404 when the link is dead.
     */
    private function staticFileStatus(string $path): int
    {
        $relative = ltrim(substr($path, strlen('/storage/')), '/');

        return is_file(storage_path('app/public/'.$relative)) ? 200 : 404;
    }

    /**
     * @return array{0:int,1:?string}
     */
    private function request(Kernel $kernel, string $path): array
    {
        try {
            $request = Request::create($path, 'GET');
            $response = $kernel->handle($request);
            $status = $response->getStatusCode();
            $content = $response->getContent();

            $kernel->terminate($request, $response);

            return [$status, $content];
        } catch (Throwable $e) {
            return [500, $e->getMessage()];
        }
    }

    /**
     * @return list<string>
     */
    private function internalLinks(string $html): array
    {
        preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\']/i', $html, $matches);

        $links = [];

        foreach (array_unique($matches[1] ?? []) as $link) {
            if ($this->pathOf($link) !== null) {
                $links[] = $link;
            }
        }

        return $links;
    }

    /**
     * The same-origin path for a link, or null when it must be skipped.
     */
    private function pathOf(string $url): ?string
    {
        if (str_starts_with($url, '#')) {
            return null;
        }

        foreach (self::SKIP_FRAGMENTS as $fragment) {
            if (str_contains($url, $fragment)) {
                return null;
            }
        }

        $parts = parse_url($url);

        if ($parts === false) {
            return null;
        }

        if (isset($parts['host'])) {
            $allowedHosts = array_filter([
                parse_url((string) config('app.url'), PHP_URL_HOST),
                'localhost',
                '127.0.0.1',
            ]);

            if (! in_array($parts['host'], $allowedHosts, true)) {
                return null;
            }
        }

        $path = $parts['path'] ?? '';

        if ($path === '' || ! str_starts_with($path, '/')) {
            return null;
        }

        return $path;
    }
}
