<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', static function (): void {
    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        // Hot Reload route - for framework use on the hot reloader.
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
});

/*
 * --------------------------------------------------------------------
 * Auto-delete expired archived appointments (runs once per day)
 * Deletes archived appointments whose appointment_date has passed.
 * --------------------------------------------------------------------
 */
Events::on('post_controller_constructor', static function (): void {
    if (is_cli()) {
        return;
    }

    // Only run once per day using a cache flag
    $cacheKey  = 'archived_appt_cleanup_last_run';
    $lastRun   = cache($cacheKey);
    $today     = date('Y-m-d');

    if ($lastRun === $today) {
        return;
    }

    try {
        $db = \Config\Database::connect();
        $db->query(
            "DELETE FROM appointments
             WHERE archived_at IS NOT NULL
               AND appointment_date < ?",
            [$today]
        );
        cache()->save($cacheKey, $today, 86400); // cache for 24 hours
    } catch (\Throwable $e) {
        log_message('error', 'Archived appointment cleanup failed: ' . $e->getMessage());
    }
});
