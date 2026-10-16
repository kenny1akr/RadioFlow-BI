<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Simuler un utilisateur caissier
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['logged_in'] = true;
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'caissier';
$_SESSION['csrf_token'] = 'test';

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/dash/template/permissions.php';
require_once __DIR__ . '/dash/template/sidebar_config.php';

$db = Database::getInstance();

// Récupérer le vrai user depuis la DB
$userRecord = $db->fetch(
    "SELECT u.*, r.code AS role_code FROM utilisateurs u
     JOIN roles r ON u.id_role = r.id_role
     WHERE u.id_utilisateur = 1 LIMIT 1"
);

if (!$userRecord) {
    die("Utilisateur #1 introuvable en base.");
}

$role = 'caissier';
$user = [
    'utilisateur_id' => 1,
    'nom_complet'    => $userRecord['nom_complet'],
    'role_system'    => $role,
    'lang'           => 'fr',
];

echo "<pre style='font:13px monospace;padding:16px;'>";

echo "=== Rôle simulé : $role ===\n\n";

echo "=== Permissions caissier ===\n";
global $ROLES;
foreach ($ROLES['caissier']['permissions'] as $p) {
    echo "  $p\n";
}

echo "\n=== Test userCan() pour les contrats/campagnes/spots ===\n";
foreach ([
    'contrats.view', 'contrats.create', 'contrats.edit',
    'campagnes.view', 'campagnes.create', 'campagnes.edit',
    'spots.view', 'spots.upload',
] as $perm) {
    $ok = userCan($user, $perm) ? '✅ OUI' : '❌ NON';
    echo str_pad($perm, 25) . " : $ok\n";
}

echo "\n=== Sections de la sidebar ===\n";
foreach ($SIDEBAR_CONFIG as $sectionKey => $section) {
    echo "\n[$sectionKey] label = " . ($section['label'] ?? '(null)') . "\n";
    foreach ($section['items'] as $item) {
        $perm = $item['permission'] ?? '(none)';
        $visible = empty($perm) || $perm === '(none)' || userCan($user, $perm);
        echo "  • " . str_pad($item['label'] ?? '?', 25)
           . " perm=" . str_pad($perm, 18)
           . " → " . ($visible ? '✅ VISIBLE' : '❌ MASQUÉ') . "\n";
    }
}

echo "\n=== Ce que renderSidebarItem doit afficher pour contrats_campagnes ===\n";
$cc = $SIDEBAR_CONFIG['contrats_campagnes'] ?? null;
if (!$cc) {
    echo "❌ Section 'contrats_campagnes' INTROUVABLE dans \$SIDEBAR_CONFIG !\n";
} else {
    echo "✅ Section trouvée avec " . count($cc['items']) . " item(s)\n";
    foreach ($cc['items'] as $item) {
        $perm = $item['permission'] ?? '';
        $visible = userCan($user, $perm);
        echo "  • " . $item['label'] . " (perm=$perm) → " . ($visible ? '✅' : '❌ masqué') . "\n";
    }
}

echo "\n</pre>";