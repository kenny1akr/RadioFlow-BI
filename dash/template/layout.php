<?php
/**
 * dash/template/layout.php
 * Layout RadioFlow BI avec sidebar filtrée selon le rôle
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/permissions.php";
require_once __DIR__ . "/sidebar_config.php";

// ============================================================
// ROUTE ACTIVE
// ============================================================
if (!isset($route)) {
    $route = $_GET['route'] ?? 'accueil';
}
$route = trim(strtolower($route), '/');
if ($route === '' || $route === 'index.php') $route = 'accueil';

// ============================================================
// UTILISATEUR (fallback dev si absent)
// ============================================================
if (!isset($user) || !is_array($user)) {
    $TEST_ROLE = $_GET['role'] ?? 'admin';
    global $ROLES;
    if (!isset($ROLES[$TEST_ROLE])) $TEST_ROLE = 'admin';
    $user = [
        "utilisateur_id"  => 1,
        "nom_complet"     => "Administrateur RadioFlow",
        "nom_utilisateur" => "admin",
        "role_system"     => $TEST_ROLE,
        "statut"          => "actif",
        "lang"            => "fr",
        "avatar"          => "",
    ];
}

$lang = $user['lang'] ?? (defined('DEFAULT_LANG') ? DEFAULT_LANG : 'fr');

// ============================================================
// ENTREPRISE
// ============================================================
if (!isset($company) || !is_array($company)) {
    $company = [
        "company_name" => "RadioFlow BI",
        "country"      => "Burundi",
        "logo"         => "",
    ];
}

// ============================================================
// VARIABLES DÉRIVÉES
// ============================================================
$escName    = htmlspecialchars($user["nom_complet"] ?? "Utilisateur", ENT_QUOTES, 'UTF-8');
$escRole    = htmlspecialchars(getRoleLabel($user["role_system"] ?? "", $lang), ENT_QUOTES, 'UTF-8');
$roleColor  = getRoleColor($user["role_system"] ?? "");
$escAvatar  = !empty($user["avatar"]) ? htmlspecialchars($user["avatar"], ENT_QUOTES, 'UTF-8') : null;
$rolePrefix = !empty($user["role_system"]) ? $user["role_system"] . "/" : "";
$activeRoute = $rolePrefix !== "" && strpos($route, $rolePrefix) === 0
    ? substr($route, strlen($rolePrefix))
    : $route;
$profileUrl = app_route_url("profile", $user["role_system"] ?? null);

// ============================================================
// ICON MAP — Feather → Font Awesome
// ============================================================
if (!function_exists('faIcon')) {
    function faIcon(?string $feather): string
    {
        $map = [
            'home'           => 'fa-solid fa-house',
            'radio'          => 'fa-solid fa-tower-broadcast',
            'database'       => 'fa-solid fa-database',
            'dollar-sign'    => 'fa-solid fa-dollar-sign',
            'credit-card'    => 'fa-solid fa-credit-card',
            'shopping-cart'  => 'fa-solid fa-cart-shopping',
            'file-text'      => 'fa-solid fa-file-lines',
            'repeat'         => 'fa-solid fa-repeat',
            'truck'          => 'fa-solid fa-truck',
            'trending-up'    => 'fa-solid fa-arrow-trend-up',
            'book-open'      => 'fa-solid fa-book-open',
            'clipboard'      => 'fa-solid fa-clipboard-list',
            'briefcase'      => 'fa-solid fa-briefcase',
            'users'          => 'fa-solid fa-users',
            'package'        => 'fa-solid fa-box',
            'gift'           => 'fa-solid fa-gift',
            'cake'           => 'fa-solid fa-cake-candles',
            'activity'       => 'fa-solid fa-chart-line',
            'bar-chart-2'    => 'fa-solid fa-chart-column',
            'message-circle' => 'fa-solid fa-comments',
            'bell'           => 'fa-solid fa-bell',
            'shield'         => 'fa-solid fa-shield-halved',
            'settings'       => 'fa-solid fa-gear',
            'user'           => 'fa-solid fa-user',
            'log-out'        => 'fa-solid fa-right-from-bracket',
            'chevrons-left'  => 'fa-solid fa-angles-left',
            'chevron-down'   => 'fa-solid fa-chevron-down',
            'menu'           => 'fa-solid fa-bars',
            'map-pin'        => 'fa-solid fa-location-dot',
            'power'          => 'fa-solid fa-power-off',
            'search'         => 'fa-solid fa-magnifying-glass',
            'plus'           => 'fa-solid fa-plus',
            'check'          => 'fa-solid fa-check',
            'x'              => 'fa-solid fa-xmark',
            'eye'            => 'fa-solid fa-eye',
            'printer'        => 'fa-solid fa-print',
            'edit-2'         => 'fa-solid fa-pen-to-square',
            'trash-2'        => 'fa-solid fa-trash-can',
            'save'           => 'fa-solid fa-floppy-disk',
            'calendar'       => 'fa-solid fa-calendar',
            'clock'          => 'fa-solid fa-clock',
            'list'           => 'fa-solid fa-list',
            'mic'            => 'fa-solid fa-microphone',
            'headphones'     => 'fa-solid fa-headphones',
            'music'          => 'fa-solid fa-music',
            'play'           => 'fa-solid fa-play',
            'upload'         => 'fa-solid fa-upload',
            'download'       => 'fa-solid fa-download',
        ];
        return $map[$feather] ?? 'fa-solid fa-circle';
    }
}

// ============================================================
// RENDER SIDEBAR ITEM
// ============================================================
if (!function_exists('renderSidebarItem')) {
    function renderSidebarItem(array $item, string $currentRoute, array $user): void
    {
        if (!empty($item['permission']) && !userCan($user, $item['permission'])) return;

        $itemHref  = $item['href'] ?? '';
        $itemRoute = ltrim($itemHref, '/');
        $isActive  = ($itemRoute === $currentRoute);

        if ($item['type'] === 'link') {
            $activeClass = $isActive
                ? 'bg-[#FCCD2A] text-slate-900 font-semibold'
                : 'text-white hover:bg-[#FCCD2A] hover:text-slate-900';
            ?>
            <li>
                <a href="<?= htmlspecialchars(app_route_url($itemHref, $user['role_system'] ?? null), ENT_QUOTES, 'UTF-8') ?>"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition <?= $activeClass ?>">
                    <i class="<?= htmlspecialchars(faIcon($item['icon'] ?? '')) ?> w-4 text-center flex-shrink-0"></i>
                    <span class="menu-text"><?= htmlspecialchars($item['label']) ?></span>
                    <?php if (!empty($item['badge'])): ?>
                        <span class="ml-auto text-[10px] px-2 py-0.5 rounded-full bg-<?= htmlspecialchars($item['badge']['color']) ?>-500 text-white">
                            <?= htmlspecialchars($item['badge']['text']) ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
            <?php
        } elseif ($item['type'] === 'dropdown') {
            $visibleChildren = array_filter($item['items'] ?? [], function ($sub) use ($user) {
                if (empty($sub['permission'])) return true;
                return userCan($user, $sub['permission']);
            });
            if (empty($visibleChildren)) return;

            $hasActiveChild = false;
            foreach ($visibleChildren as $sub) {
                if (ltrim($sub['href'] ?? '', '/') === $currentRoute) { $hasActiveChild = true; break; }
            }
            ?>
            <li>
                <a href="#"
                   class="sidebar-dropdown-btn flex items-center w-full gap-3 px-3 py-2 rounded text-white hover:bg-[#FCCD2A] hover:text-slate-900 transition"
                   aria-expanded="<?= $hasActiveChild ? 'true' : 'false' ?>">
                    <i class="<?= htmlspecialchars(faIcon($item['icon'] ?? '')) ?> w-4 text-center flex-shrink-0"></i>
                    <span class="menu-text text-left flex-1"><?= htmlspecialchars($item['label']) ?></span>
                    <i class="fa-solid fa-chevron-down w-3 text-xs transition-transform sidebar-chevron"></i>
                </a>
                <ul class="sidebar-submenu mt-1 ml-2 <?= $hasActiveChild ? '' : 'hidden' ?> text-sm space-y-1 py-1 border-l border-white/20">
                    <?php foreach ($visibleChildren as $sub): ?>
                        <?php
                        $subHref   = $sub['href'] ?? '';
                        $subRoute  = ltrim($subHref, '/');
                        $subActive = ($subRoute === $currentRoute);
                        ?>
                        <li>
                            <a href="<?= htmlspecialchars(app_route_url($subHref, $user['role_system'] ?? null), ENT_QUOTES, 'UTF-8') ?>"
                               class="block w-full text-left px-3 py-2 rounded <?= $subActive ? 'bg-[#FCCD2A] text-slate-900 font-semibold' : 'text-white hover:bg-[#FCCD2A] hover:text-slate-900' ?>">
                                <?= htmlspecialchars($sub['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <?php
        }
    }
}
?>
<!doctype html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>RadioFlow BI — <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        window.tailwind = {
            config: {
                theme: {
                    extend: {
                        colors: {
                            radio: {
                                primary: '#2563eb',
                                dark:    '#1e40af',
                                accent:  '#f59e0b',
                                light:   '#dbeafe',
                            },
                            muco: { dark: '#0f5132', DEFAULT: '#16a34a', light: '#bbf7d0' },
                        },
                        boxShadow: { card: '0 6px 20px rgba(37,99,235,0.08)' }
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .table-row-hover tbody tr:hover { background-color: rgba(37,99,235,0.03); }
        .scrollbar-thin::-webkit-scrollbar { height: 8px; width: 8px; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 8px; }
        .anim-smooth { transition: all 180ms ease-in-out; }
        .sidebar-dropdown-btn[aria-expanded="true"] .sidebar-chevron { transform: rotate(180deg); }
        #sidebar nav::-webkit-scrollbar { width: 5px; }
        #sidebar nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 8px; }
    </style>
</head>

<body class="min-h-screen bg-[#f0f3f9] text-slate-800 antialiased">

<div id="app" class="relative flex h-screen">

    <div id="mobileOverlay" class="fixed inset-0 z-40 bg-black/40 hidden lg:hidden"></div>

    <!-- ==================== SIDEBAR ==================== -->
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-50 w-72 transform -translate-x-0 lg:translate-x-0
                  bg-gradient-to-b from-[#1e40af] to-[#1e3a8a] border-r border-slate-200 p-4 flex flex-col anim-smooth">

        <div class="flex items-center gap-3 pb-3 border-b border-white/10">
            <div class="w-10 h-10 rounded-md bg-radio-accent flex items-center justify-center shadow overflow-hidden flex-shrink-0">
                <i class="fa-solid fa-tower-broadcast text-white"></i>
            </div>
            <div class="sidebar-labels min-w-0">
                <div class="font-bold text-sm text-[#FCCD2A] leading-tight">RadioFlow BI</div>
                <div class="text-[11px] text-white/80 truncate">Gestion Radio & Caisse</div>
            </div>
        </div>

        <div class="mt-3 px-2">
            <div class="sidebar-labels flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/10">
                <span class="w-2 h-2 rounded-full bg-<?= $roleColor ?>-400"></span>
                <span class="text-[11px] text-white/90 font-medium"><?= $escRole ?></span>
            </div>
        </div>

        <nav class="flex-1 overflow-auto scrollbar-thin mt-3 -mr-2 pr-2">
            <ul class="space-y-1 text-sm">
                <?php if (!empty($SIDEBAR_CONFIG)): ?>
                    <?php foreach ($SIDEBAR_CONFIG as $sectionKey => $section): ?>
                        <?php
                        $visibleItems = array_filter($section['items'], function ($item) use ($user) {
                            if (empty($item['permission'])) return true;
                            return userCan($user, $item['permission']);
                        });
                        if (empty($visibleItems)) continue;
                        ?>

                        <?php if (!empty($section['label'])): ?>
                            <li class="mt-4 first:mt-0 text-[10px] text-white/50 uppercase px-3 tracking-wider font-semibold">
                                <?= htmlspecialchars($section['label']) ?>
                            </li>
                        <?php endif; ?>

                        <?php foreach ($visibleItems as $item) renderSidebarItem($item, $activeRoute, $user); ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <li class="mt-4 pt-3 border-t border-white/10">
                    <a href="#" id="logoutBtn"
                       class="flex items-center gap-3 px-3 py-2 rounded text-white hover:bg-red-500/80 transition">
                        <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                        <span class="menu-text">Déconnexion</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="mt-3 px-2">
            <button id="collapseBtn"
                    class="w-full py-2 text-sm rounded-lg text-white bg-radio-accent hover:bg-amber-600 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-angles-left w-4"></i>
                <span class="menu-text">Réduire</span>
            </button>
        </div>
    </aside>

    <!-- ==================== MAIN ==================== -->
    <div id="mainArea" class="w-0 min-w-0 flex-1 flex flex-col min-h-0 ml-0 lg:ml-72 anim-smooth">

        <!-- TOP NAV -->
        <header class="flex items-center justify-between gap-4 px-4 py-3 border-b bg-white shadow-sm">
            <div class="flex items-center gap-3">
                <button id="mobileMenuBtn" class="lg:hidden p-2 rounded-md hover:bg-slate-100">
                    <i class="fa-solid fa-bars w-5 text-lg"></i>
                </button>

                <div class="hidden sm:flex items-center gap-2 text-sm text-slate-500">
                    <i class="fa-solid fa-tower-broadcast text-radio-primary"></i>
                    <span class="font-medium text-slate-700"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></span>
                </div>
            </div>

            <div class="flex items-center gap-3">

                <!-- Indicateur LIVE -->
                <span class="hidden sm:flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-600 border border-red-200">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                    LIVE
                </span>

                <!-- Sélecteur de langue -->
                <div class="hidden sm:flex items-center gap-1 text-xs">
                    <?php foreach (['fr'=>'FR','rn'=>'RN','en'=>'EN'] as $code=>$label): ?>
                        <a href="?lang=<?= $code ?>"
                           class="px-2 py-1 rounded <?= $lang === $code
                               ? 'bg-radio-primary text-white'
                               : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- User dropdown -->
                <div class="relative">
                    <button id="userBtn" type="button" aria-haspopup="true" aria-expanded="false"
                            class="flex items-center gap-2 p-1 rounded hover:bg-slate-100 focus:outline-none">
                        <?php if ($escAvatar): ?>
                            <img class="h-9 w-9 rounded-full object-cover" src="<?= $escAvatar ?>" alt="<?= $escName ?>">
                        <?php else: ?>
                            <span class="inline-flex h-9 w-9 rounded-full bg-<?= $roleColor ?>-100 text-<?= $roleColor ?>-700 items-center justify-center font-semibold text-sm">
                                <?= strtoupper(substr($escName, 0, 1)) ?>
                            </span>
                        <?php endif; ?>
                        <span class="hidden sm:inline text-sm font-medium"><?= $escName ?></span>
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </button>

                    <div id="userDropdown"
                         class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50 transform transition duration-150 ease-out scale-95 opacity-0 origin-top-right">
                        <div class="px-4 py-3 border-b flex items-center gap-3">
                            <span class="inline-flex w-10 h-10 rounded-full bg-<?= $roleColor ?>-100 text-<?= $roleColor ?>-700 items-center justify-center font-semibold">
                                <?= strtoupper(substr($escName, 0, 1)) ?>
                            </span>
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-slate-800 truncate"><?= $escName ?></div>
                                <div class="text-xs text-<?= $roleColor ?>-600 font-medium"><?= $escRole ?></div>
                            </div>
                        </div>
                        <a href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') ?>" class="flex items-center px-4 py-2 text-sm hover:bg-slate-50">
                            <i class="fa-solid fa-user w-4 mr-2"></i> Mon profil
                        </a>
                        <a href="<?= htmlspecialchars(app_route_url('messagerie', $user['role_system'] ?? null), ENT_QUOTES, 'UTF-8') ?>" class="flex items-center px-4 py-2 text-sm hover:bg-slate-50">
                            <i class="fa-solid fa-comments w-4 mr-2"></i> Messagerie
                        </a>
                        <a href="<?= htmlspecialchars(app_route_url('rappels', $user['role_system'] ?? null), ENT_QUOTES, 'UTF-8') ?>" class="flex items-center px-4 py-2 text-sm hover:bg-slate-50">
                            <i class="fa-solid fa-bell w-4 mr-2"></i> Rappels & alertes
                        </a>
                        <?php if (userCan($user, 'utilisateurs.roles')): ?>
                            <a href="<?= htmlspecialchars(app_route_url('parametres', $user['role_system'] ?? null), ENT_QUOTES, 'UTF-8') ?>" class="flex items-center px-4 py-2 text-sm hover:bg-slate-50">
                                <i class="fa-solid fa-gear w-4 mr-2"></i> Paramètres
                            </a>
                        <?php endif; ?>
                        <div class="border-t"></div>
                        <a href="#" id="logoutBtn2" class="flex items-center px-4 py-2 text-sm hover:bg-slate-50 text-rose-600">
                            <i class="fa-solid fa-power-off w-4 mr-2"></i> Se déconnecter
                        </a>
                    </div>
                </div>

                <!-- Notifications -->
                <button id="notificationBell" class="p-2 rounded hover:bg-slate-100 relative" title="Notifications">
                    <i class="fa-solid fa-bell text-lg"></i>
                    <span id="notificationBadge"
                          class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full hidden"></span>
                </button>
            </div>
        </header>

        <!-- CONTENT -->
        <main id="content" class="min-w-0 flex-1 overflow-x-hidden overflow-y-auto p-4">
            <?php
            if (isset($content) && $content !== '') {
                echo $content;
            } else {
                echo '<div class="p-4 bg-yellow-50 text-yellow-800 rounded">Aucun contenu à afficher.</div>';
            }
            ?>
        </main>

        <footer class="px-4 py-3 border-t bg-white text-sm text-slate-500">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>© <span id="year"></span> <strong>RadioFlow BI</strong> — Tous droits réservés</div>
                <div class="text-xs text-slate-400">Version 1.0 — <?= $escRole ?></div>
            </div>
        </footer>
    </div>
</div>

<!-- TOAST -->
<div id="toast" class="fixed right-4 bottom-4 z-50 hidden">
    <div class="px-4 py-3 bg-radio-primary text-white rounded shadow-lg">Action réalisée</div>
</div>

<!-- LOGOUT FORM -->
<form id="logoutForm" method="post" action="<?= htmlspecialchars(app_url('api/endpoints/auth.php?action=logout'), ENT_QUOTES, 'UTF-8') ?>" class="hidden">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
</form>

<script>
    const $  = (s) => document.querySelector(s);
    const $$ = (s) => Array.from(document.querySelectorAll(s));

    const sidebar         = $('#sidebar');
    const mainArea        = $('#mainArea');
    const mobileOverlayEl = $('#mobileOverlay');

    // Mobile toggle
    $('#mobileMenuBtn')?.addEventListener('click', () => {
        if (sidebar.classList.contains('-translate-x-72')) {
            sidebar.classList.remove('-translate-x-72');
            mobileOverlayEl.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-72');
            mobileOverlayEl.classList.add('hidden');
        }
    });

    mobileOverlayEl?.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-72');
        mobileOverlayEl.classList.add('hidden');
    });

    // Collapse desktop
    $('#collapseBtn')?.addEventListener('click', () => {
        sidebar.classList.toggle('w-20');
        if (sidebar.classList.contains('w-20')) {
            sidebar.classList.remove('w-72');
            $$('.menu-text, .sidebar-labels').forEach(n => n.classList.add('hidden'));
            mainArea.classList.remove('lg:ml-72');
            mainArea.classList.add('lg:ml-20');
        } else {
            sidebar.classList.remove('w-20');
            sidebar.classList.add('w-72');
            $$('.menu-text, .sidebar-labels').forEach(n => n.classList.remove('hidden'));
            mainArea.classList.remove('lg:ml-20');
            mainArea.classList.add('lg:ml-72');
        }
    });

    if (window.innerWidth < 1024) sidebar.classList.add('-translate-x-72');

    // Sidebar dropdowns
    $$('.sidebar-dropdown-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const submenu  = btn.parentElement.querySelector('.sidebar-submenu');
            const expanded = btn.getAttribute('aria-expanded') === 'true';

            $$('.sidebar-dropdown-btn').forEach(other => {
                if (other !== btn) {
                    other.setAttribute('aria-expanded', 'false');
                    other.parentElement.querySelector('.sidebar-submenu')?.classList.add('hidden');
                }
            });

            submenu.classList.toggle('hidden', expanded);
            btn.setAttribute('aria-expanded', !expanded);
        });
    });

    // User dropdown
    (function() {
        const btn  = document.getElementById('userBtn');
        const menu = document.getElementById('userDropdown');
        if (!btn || !menu) return;

        const openMenu = () => {
            menu.classList.remove('hidden', 'scale-95', 'opacity-0');
            menu.classList.add('scale-100', 'opacity-100');
            btn.setAttribute('aria-expanded', 'true');
        };
        const closeMenu = () => {
            menu.classList.add('scale-95', 'opacity-0');
            btn.setAttribute('aria-expanded', 'false');
            setTimeout(() => menu.classList.add('hidden'), 120);
        };

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            menu.classList.contains('hidden') ? openMenu() : closeMenu();
        });

        document.addEventListener('click', (e) => {
            if (!menu.contains(e.target) && !btn.contains(e.target)) closeMenu();
        });
    })();

    // Toast
    function toast(msg = 'Ok') {
        const t = $('#toast');
        if (!t) return;
        t.querySelector('div').textContent = msg;
        t.classList.remove('hidden');
        setTimeout(() => t.classList.add('hidden'), 2600);
    }

    // Logout
    function handleLogout(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Se déconnecter ?',
            text: 'Votre session sera fermée.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, se déconnecter',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#2563eb',
            reverseButtons: true
        }).then((r) => {
            if (r.isConfirmed) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('auth_user');
                sessionStorage.removeItem('auth_token');
                sessionStorage.removeItem('auth_user');
                fetch('<?= htmlspecialchars(app_url('api/endpoints/auth.php?action=logout'), ENT_QUOTES, 'UTF-8') ?>', {
                    method: 'POST',
                    credentials: 'same-origin'
                }).finally(() => {
                    window.location.href = '<?= htmlspecialchars(app_url('index.php'), ENT_QUOTES, 'UTF-8') ?>';
                });
            }
        });
    }
    document.getElementById('logoutBtn')?.addEventListener('click', handleLogout);
    document.getElementById('logoutBtn2')?.addEventListener('click', handleLogout);

    // Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (window.innerWidth < 1024) {
                sidebar.classList.add('-translate-x-72');
                mobileOverlayEl.classList.add('hidden');
            }
        }
    });

    // Resize
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            mobileOverlayEl.classList.add('hidden');
            sidebar.classList.remove('-translate-x-72');
            mainArea.classList.add('lg:ml-72');
        } else {
            sidebar.classList.add('-translate-x-72');
            mainArea.classList.remove('lg:ml-72');
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const y = $('#year'); if (y) y.textContent = new Date().getFullYear();
        if (window.innerWidth >= 1024) mainArea.classList.add('lg:ml-72');
    });
</script>

</body>
</html>