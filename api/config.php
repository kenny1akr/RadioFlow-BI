<?php
/**
 * config/config.php
 * Configuration centralisée - RadioFlow BI
 * Plateforme de Gestion Globale de Radio et Contrôle de Caisse en Temps Réel
 * PHP pur - Sans framework, sans .env
 */

// ============================================================
// SÉCURITÉ : empêche l'accès direct au fichier via URL
// ============================================================
if (!defined('RADIOFLOW_APP')) {
    define('RADIOFLOW_APP', true);
}

// ============================================================
// ENVIRONNEMENT
// ============================================================
define('APP_ENV',   'development');     // 'development' | 'production'
define('APP_DEBUG', true);              // true en dev, false en prod
define('APP_NAME',  'RadioFlow BI');
define('APP_URL',   'http://localhost/radioflow-Bi');
define('APP_VERSION', '1.0');

// ============================================================
// LANGUES SUPPORTÉES (Français, Kirundi, Anglais)
// ============================================================
define('DEFAULT_LANG', 'fr');
define('SUPPORTED_LANGS', ['fr', 'rn', 'en']);

// ============================================================
// BASE DE DONNÉES (MariaDB / MySQL)
// ============================================================
define('DB_HOST',    '127.0.0.1');
define('DB_PORT',    3306);
define('DB_NAME',    'radioflow');
define('DB_USER',    'root');
define('DB_PASS',    '');                       // Vide sous XAMPP par défaut
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// ============================================================
// JWT (Sécurité)
// ============================================================
define('JWT_SECRET',     'radioflow_secret_key_change_me_2026_a7f8b9c0d1e2f3a4b5c6d7e8f9a0b1c2');
define('JWT_ALGO',       'HS256');
define('JWT_EXPIRATION', 86400);                // 24 heures

// ============================================================
// SÉCURITÉ LOGIN
// ============================================================
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCK_TIME',    15 * 60);          // 15 minutes

// ============================================================
// LOGS
// ============================================================
define('LOG_PATH',  dirname(__DIR__) . '/logs/error.log');
define('LOG_LEVEL', 'debug');                   // debug | info | warning | error

// ============================================================
// EMAIL (Notifications & reset password)
// ============================================================
define('MAIL_HOST',      'smtp.gmail.com');
define('MAIL_PORT',      587);
define('MAIL_USER',      '');
define('MAIL_PASS',      '');
define('MAIL_FROM',      'noreply@radioflow.bi');
define('MAIL_FROM_NAME', APP_NAME);

// ============================================================
// PARAMÈTRES MÉTIER RADIO
// ============================================================
define('CURRENCY',        'FBu');
define('DATE_FORMAT',     'd/m/Y');
define('TIMEZONE',        'Africa/Bujumbura');

// Contrôle de caisse (Module 6)
define('PLAFOND_ENCAISSEMENT_DEFAULT', 500000);   // 500 000 FBu
define('ALERTE_OUVERTURE_CAISSE',      true);
define('ALERTE_CLOTURE_CAISSE',        true);

// Publicité (Modules 2 & 3)
define('QUOTA_MAX_SPOTS_PAR_PAUSE',    8);
define('QUOTA_MAX_PUB_PAR_HEURE',      12);
define('NIVEAU_PRIORITE_DEFAULT',      1);

// Promotions (Module 5)
define('ALERTE_BIRTHDAY_DAYS',         7);
define('ALERTE_CONCOURS_FIN_JOURS',    3);

// Facturation
define('TVA_DEFAULT',                  18);         // 18% TVA
define('ALERT_INVOICE_OVERDUE',        14);         // Facture impayée > 14 jours

// ============================================================
// UPLOAD SPOTS AUDIO (MP3/WAV)
// ============================================================
define('UPLOAD_MAX_SIZE',  20 * 1024 * 1024);   // 20 Mo (spots audio)
define('UPLOAD_ALLOWED',   'mp3,wav,pdf,jpg,jpeg,png,docx,xlsx');
define('UPLOAD_PATH',      dirname(__DIR__) . '/uploads');

// ============================================================
// APPLICATION DES PARAMÈTRES
// ============================================================

if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', LOG_PATH);
}

date_default_timezone_set(TIMEZONE);
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Créer les dossiers nécessaires
foreach ([dirname(LOG_PATH), UPLOAD_PATH, UPLOAD_PATH . '/spots'] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}