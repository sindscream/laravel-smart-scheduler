<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Run In The Testing Environment
    |--------------------------------------------------------------------------
    |
    | By default, environment-aware commands are NOT scheduled while running in
    | the "testing" environment, to avoid side effects during test runs. Set
    | this to true if you explicitly want them scheduled under testing.
    |
    */
    'run_in_tests' => env('ENV_SCHEDULER_RUN_IN_TESTS', false),
];
