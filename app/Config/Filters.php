<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    // Makes reading things below nicer,
    // and simpler to change out script that's used.
    public $aliases = [
        // 'csrf'       => \CodeIgniter\Filters\CSRF::class,
        // 'toolbar'    => \CodeIgniter\Filters\DebugToolbar::class,
        // 'honeypot'   => \CodeIgniter\Filters\Honeypot::class,
        'jwt'        => \App\Filters\JSONWebTokenFilter::class,
        'rate_limit' => \App\Filters\RateLimitFilter::class,
    ];

    // Always applied before every request
    // Filters are run in order top-bottom
    public $globals = [
        'before' => [
            'jwt' => ['except' => 'healthy'],
            'rate_limit' => ['except' => 'healthy'],
            //'honeypot'
            // 'csrf',
        ],
        'after'  => [
            // 'toolbar',
            //'honeypot'
        ],
    ];

    // Works on all of a particular HTTP method
    // (GET, POST, etc) as BEFORE filters only
    //     like: 'post' => ['CSRF', 'throttle'],
    public $methods = [];

    // List filter aliases and any before/after uri patterns
    // that they should run on, like:
    //    'isLoggedIn' => ['before' => ['account/*', 'profiles/*']],
    public $filters = [];
}
