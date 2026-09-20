<?php
// Zorvex Pro - Safe Helper Compatibility Layer
// Prevents any legacy require from breaking the admin panel.

if (!function_exists('faoxima_db_ping')) {
    function faoxima_db_ping() {
        return true;
    }
}
