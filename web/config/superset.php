<?php

return [

    'url' => env('SUPERSET_URL', 'http://localhost:8088'),
        'public_url' => env('SUPERSET_PUBLIC_URL', env('SUPERSET_URL', 'http://localhost:8088')),
        'internal_url' => env('SUPERSET_INTERNAL_URL', env('SUPERSET_URL', 'http://localhost:8088')),
    'admin_username' => env('SUPERSET_ADMIN_USERNAME', 'admin'),
    'admin_password' => env('SUPERSET_ADMIN_PASSWORD', 'admin'),

    // Single dashboard template per role. Agents share one dashboard;
    // row isolation comes from guest-token RLS (agent_id = <id>).
    'dashboards' => [
        'agent' => env('SUPERSET_AGENT_DASHBOARD_ID', env('SUPERSET_DASHBOARD_ID')),
        'pimpinan' => env('SUPERSET_PIMPINAN_DASHBOARD_ID', env('SUPERSET_DASHBOARD_ID')),
        'superadmin' => env('SUPERSET_SUPERADMIN_DASHBOARD_ID', env('SUPERSET_DASHBOARD_ID')),
    ],

];
