<?php
namespace sessionManager;

if (!class_exists('sessionManager\sessionManager')) {
    class sessionManager {
        public function Set($key, $value) {
            $_SESSION[$key] = $value;
            $_SESSION['start'] = time();
            $_SESSION['expire'] = $_SESSION['start'] + (30 * 60);
        }

        public function Get($key) {
            return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        }

        public function isExpired() {
            $now = time();
            if (isset($_SESSION['expire']) && $now > $_SESSION['expire']) {
                session_unset();
                session_destroy();
                return true;
            }
            return false;
        }

        public function remove($key) {
            unset($_SESSION[$key]);
        }

        public function start() {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['start'] = time();
            $_SESSION['expire'] = $_SESSION['start'] + (30 * 60);
        }
    }
}