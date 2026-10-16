<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Connexion | RadioFlow BI</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Configuration Tailwind -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        radio: {
                            primary:  '#2563eb',   // Bleu radio
                            dark:     '#1e40af',
                            accent:   '#f59e0b',   // Jaune/or antenne
                            background: '#f0f3f9'
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="min-h-screen bg-radio-background flex flex-col
             items-center justify-center px-4 py-8
             font-sans text-gray-700">

    <!-- Conteneur principal -->
    <main class="w-full max-w-sm">

        <!-- Sélecteur de langue -->
        <div class="flex justify-end mb-3 gap-1">
            <button data-lang="fr" class="lang-btn px-2.5 py-1 text-xs rounded-md
                    font-medium transition">FR</button>
            <button data-lang="rn" class="lang-btn px-2.5 py-1 text-xs rounded-md
                    font-medium transition">RN</button>
            <button data-lang="en" class="lang-btn px-2.5 py-1 text-xs rounded-md
                    font-medium transition">EN</button>
        </div>

        <!-- Carte de connexion -->
        <section class="bg-white rounded-3xl shadow-lg
                        px-7 sm:px-8 py-8">

            <!-- Logo et nom -->
            <div class="text-center mb-5">

                <div class="flex items-center justify-center gap-2
                            text-radio-dark">

                    <!-- Icône ondes radio -->
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-7 h-7"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                        stroke-linecap="round"
                    >
                        <circle cx="12" cy="12" r="2" fill="currentColor"></circle>
                        <path d="M16.24 7.76a6 6 0 0 1 0 8.49"></path>
                        <path d="M7.76 16.24a6 6 0 0 1 0-8.49"></path>
                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
                        <path d="M4.93 19.07a10 10 0 0 1 0-14.14"></path>
                    </svg>

                    <h1 class="text-2xl font-bold tracking-tight">
                        RadioFlow BI
                    </h1>
                </div>

                <p id="subtitle" class="text-sm text-gray-500 mt-4">
                    Plateforme de Gestion de Radio
                </p>
            </div>

            <!-- Message d'erreur ou de succès -->
            <div
                id="message"
                class="hidden mb-4 rounded-lg p-3 text-sm"
                role="status"
                aria-live="polite"
            ></div>

            <!-- Formulaire -->
            <form id="loginForm" novalidate>

                <!-- Identifiant -->
                <div class="mb-4">
                    <label
                        for="identifier"
                        id="labelIdentifier"
                        class="block text-sm font-medium mb-2"
                    >
                        Identifiant ou Email
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        id="identifier"
                        name="identifier"
                        autocomplete="username"
                        required
                        class="w-full h-10 border border-gray-300
                               rounded-sm px-3 text-sm
                               outline-none transition
                               focus:border-radio-primary
                               focus:ring-2 focus:ring-radio-primary/20"
                    >
                </div>

                <!-- Mot de passe -->
                <div class="mb-4">
                    <label
                        for="password"
                        id="labelPassword"
                        class="block text-sm font-medium mb-2"
                    >
                        Mot de passe
                        <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            required
                            class="w-full h-10 border border-gray-300
                                   rounded-sm pl-3 pr-10 text-sm
                                   outline-none transition
                                   focus:border-radio-primary
                                   focus:ring-2 focus:ring-radio-primary/20"
                        >

                        <!-- Bouton afficher / masquer -->
                        <button
                            type="button"
                            id="togglePassword"
                            aria-label="Afficher le mot de passe"
                            aria-pressed="false"
                            class="absolute inset-y-0 right-0 px-3
                                   flex items-center text-gray-500
                                   hover:text-radio-dark"
                        >
                            <svg
                                id="eyeIcon"
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M2 12s3.5-7 10-7
                                         10 7 10 7-3.5 7-10 7
                                         -10-7-10-7Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Options -->
                <div class="flex items-start justify-between
                            gap-3 mb-7 text-xs">

                    <!-- Se souvenir de moi -->
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            class="w-3.5 h-3.5 accent-radio-primary
                                   rounded cursor-pointer"
                        >

                        <span id="labelRemember">Se souvenir de moi</span>
                    </label>

                    <!-- Mot de passe oublié -->
                    <a
                        href="forgot-password.html"
                        id="linkForgot"
                        class="flex items-start gap-1 text-radio-primary
                               hover:text-radio-dark font-medium"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-3.5 h-3.5 shrink-0"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                        >
                            <circle cx="12" cy="12" r="10"></circle>
                            <path
                                d="M12 7v5m0 4h.01"
                                stroke="white"
                                stroke-width="2"
                                stroke-linecap="round"
                            ></path>
                        </svg>

                        <span id="labelForgot">
                            Mot de passe oublié ?
                        </span>
                    </a>
                </div>

                <!-- Bouton de connexion -->
                <button
                    type="submit"
                    id="loginButton"
                    class="w-full h-11 rounded-md
                           bg-radio-primary text-white
                           font-semibold text-sm
                           hover:bg-radio-dark
                           focus:outline-none focus:ring-4
                           focus:ring-radio-primary/25
                           transition duration-200
                           disabled:opacity-60"
                >
                    Se connecter
                </button>

            </form>
        </section>

        <!-- Copyright -->
        <p class="text-center text-[10px] text-gray-500 mt-5">
            &copy; <span id="year"></span> RadioFlow BI — Tous droits réservés
        </p>

    </main>

<!-- JavaScript -->
<script>
    // ============================================================
    // INTERNATIONALISATION (FR / RN / EN)
    // ============================================================
    const translations = {
        fr: {
            title:       'Connexion | RadioFlow BI',
            subtitle:    'Plateforme de Gestion de Radio',
            identifier:  'Identifiant ou Email',
            password:    'Mot de passe',
            remember:    'Se souvenir de moi',
            forgot:      'Mot de passe oublié ?',
            login:       'Se connecter',
            connecting:  'Connexion en cours...',
            required:    'Veuillez remplir tous les champs obligatoires.',
            error:       'Erreur de connexion',
            welcome:     'Bienvenue',
            redirecting: 'Redirection...',
        },
        rn: {
            title:       'Injira | RadioFlow BI',
            subtitle:    'Urubuga rw\'Ubuyobozi bw\'Radito',
            identifier:  'Izina ry\'ukoresha cyangwa Email',
            password:    'Ijambobanga',
            remember:    'Nyibuka',
            forgot:      'Wibagiye ijambobanga?',
            login:       'Injira',
            connecting:  'Kwinjira...',
            required:    'Uzuza imyanya yose isabwa.',
            error:       'Ikibazo cyo kwinjira',
            welcome:     'Murakaza neza',
            redirecting: 'Kujya ahandi...',
        },
        en: {
            title:       'Login | RadioFlow BI',
            subtitle:    'Radio Management Platform',
            identifier:  'Username or Email',
            password:    'Password',
            remember:    'Remember me',
            forgot:      'Forgot password?',
            login:       'Sign in',
            connecting:  'Signing in...',
            required:    'Please fill in all required fields.',
            error:       'Login error',
            welcome:     'Welcome',
            redirecting: 'Redirecting...',
        }
    };

    let currentLang = localStorage.getItem('radioflow_lang') || 'fr';

    function applyLanguage(lang) {
        if (!translations[lang]) lang = 'fr';
        currentLang = lang;
        localStorage.setItem('radioflow_lang', lang);
        document.documentElement.lang = lang;

        const t = translations[lang];
        document.title = t.title;
        document.getElementById('subtitle').textContent       = t.subtitle;
        document.getElementById('labelIdentifier').childNodes[0].nodeValue = t.identifier + ' ';
        document.getElementById('labelPassword').childNodes[0].nodeValue   = t.password + ' ';
        document.getElementById('labelRemember').textContent   = t.remember;
        document.getElementById('labelForgot').textContent     = t.forgot;
        document.getElementById('loginButton').textContent     = t.login;

        // Style des boutons de langue
        document.querySelectorAll('.lang-btn').forEach(btn => {
            const active = btn.dataset.lang === lang;
            btn.className = 'lang-btn px-2.5 py-1 text-xs rounded-md font-medium transition ' +
                (active
                    ? 'bg-radio-primary text-white'
                    : 'bg-white text-gray-500 hover:bg-gray-100 border border-gray-200');
        });
    }

    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.addEventListener('click', () => applyLanguage(btn.dataset.lang));
    });

    applyLanguage(currentLang);

    // ============================================================
    // ANNÉE AUTOMATIQUE
    // ============================================================
    document.getElementById('year').textContent = new Date().getFullYear();

    // ============================================================
    // ÉLÉMENTS DU DOM
    // ============================================================
    const loginForm      = document.getElementById('loginForm');
    const passwordInput  = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const eyeIcon        = document.getElementById('eyeIcon');
    const message        = document.getElementById('message');
    const loginButton    = document.getElementById('loginButton');

    // ============================================================
    // AFFICHER / MASQUER MOT DE PASSE
    // ============================================================
    togglePassword.addEventListener('click', function () {
        const isHidden = passwordInput.type === 'password';
        passwordInput.type = isHidden ? 'text' : 'password';
        togglePassword.setAttribute('aria-label', isHidden ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        togglePassword.setAttribute('aria-pressed', String(isHidden));
        eyeIcon.innerHTML = isHidden
            ? `<path d="M3 3l18 18"></path><path d="M10.6 10.6a2 2 0 002.8 2.8"></path><path d="M9.9 5.2A10.8 10.8 0 0112 5c6.5 0 10 7 10 7a15 15 0 01-4 4.8"></path><path d="M6.2 6.2C3.5 8 2 12 2 12s3.5 7 10 7a10 10 0 004-.8"></path>`
            : `<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle>`;
    });

    // ============================================================
    // MESSAGES
    // ============================================================
    function showMessage(text, type = 'error') {
        message.textContent = text;
        message.className = 'mb-4 rounded-lg p-3 text-sm ' +
            (type === 'success'
                ? 'bg-green-50 text-green-700 border border-green-200'
                : 'bg-red-50 text-red-700 border border-red-200');
        message.classList.remove('hidden');
    }

    function hideMessage() {
        message.classList.add('hidden');
    }

    // ============================================================
    // REDIRECTION SELON LE RÔLE (RadioFlow BI)
    // ============================================================
    function redirectUser(role, fullName) {
        const t = translations[currentLang];
        showMessage(`✅ ${t.welcome} ${fullName} — ${t.redirecting}`, 'success');

        const roleRoutes = {
            admin:       'admin/dashboard',
            caissier:    'caissier/dashboard',
            commercial:  'commercial/dashboard',
            programmes:  'programmes/dashboard',
            animateur:   'animateur/dashboard',
        };

        const roleRoute = roleRoutes[role] || 'dashboard';

        setTimeout(() => {
            window.location.href = `../router.php?route=${encodeURIComponent(roleRoute)}`;
        }, 800);
    }

    // ============================================================
    // SOUMISSION DU FORMULAIRE
    // ============================================================
    loginForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        hideMessage();

        const t = translations[currentLang];
        const identifier = document.getElementById('identifier').value.trim();
        const password   = passwordInput.value;
        const remember   = document.getElementById('remember').checked;

        if (!identifier || !password) {
            showMessage(t.required);
            return;
        }

        loginButton.disabled = true;
        loginButton.textContent = t.connecting;

        try {
            const response = await fetch('../api/endpoints/auth.php?action=login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Accept-Language': currentLang,
                },
                body: JSON.stringify({ identifier, password })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                if (data.errors) {
                    showMessage(Object.values(data.errors)[0] || data.message);
                } else {
                    showMessage(data.message || t.error);
                }
                loginButton.disabled = false;
                loginButton.textContent = t.login;
                return;
            }

            // ============================================================
            // SUCCÈS : stockage du token
            // ============================================================
            if (remember) {
                // Persistant (LocalStorage) — uniquement si "Se souvenir de moi"
                localStorage.setItem('auth_token', data.data.token);
                localStorage.setItem('auth_user', JSON.stringify(data.data.user));
                localStorage.setItem('radioflow_lang', data.data.user.lang || currentLang);
            } else {
                // Session courante uniquement (SessionStorage)
                sessionStorage.setItem('auth_token', data.data.token);
                sessionStorage.setItem('auth_user', JSON.stringify(data.data.user));
            }

            redirectUser(data.data.user.role, data.data.user.full_name);

        } catch (error) {
            console.error('Erreur réseau :', error);
            showMessage(
                currentLang === 'en' ? 'Server unreachable. Check your connection.' :
                currentLang === 'rn' ? 'Seriveri ntishobora kugerwaho. Reba interineti.' :
                'Serveur injoignable. Vérifiez votre connexion.'
            );
            loginButton.disabled = false;
            loginButton.textContent = t.login;
        }
    });
</script>

</body>
</html>