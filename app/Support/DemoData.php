<?php

namespace App\Support;

/**
 * Whether fabricated demo data may be used in this environment.
 *
 * The demo accounts share one public password (`secret123`) and the login page
 * offers them on a one-click quick-fill. That is fine on a developer machine
 * and unacceptable in production, so both the seeder and the login page ask
 * the same question and get the same answer.
 */
final class DemoData
{
    public static function allowed(): bool
    {
        return app()->environment('local') || app()->runningUnitTests();
    }
}
