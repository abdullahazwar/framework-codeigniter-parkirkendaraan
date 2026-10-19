<?php

namespace Config;

use App\Filters\AdminFilter;
use App\Filters\AuthFilter;
use App\Filters\PenggunaFilter;
use App\Filters\StaffFilter;
use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    public array $aliases = [
        'csrf' => CSRF::class,
        'forcehttps' => ForceHTTPS::class,
        'invalidchars' => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'auth' => AuthFilter::class,
        'staff' => StaffFilter::class,
        'pengguna' => PenggunaFilter::class,
        'admin' => AdminFilter::class,
    ];
    public array $required = ['before' => ['forcehttps'], 'after' => []];
    public array $globals = [
        'before' => ['invalidchars', 'csrf'],
        'after' => ['secureheaders'],
    ];
    public array $methods = [];
    public array $filters = [];
}
