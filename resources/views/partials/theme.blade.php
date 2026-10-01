{{--
    Shared design tokens and component styles.
    Included by layouts.app, layouts.guest and layouts.public.
--}}
<style>
    :root {
        --hms-primary: #4f46e5;
        --hms-primary-dark: #4338ca;
        --hms-primary-soft: #eef2ff;
        --hms-accent: #0891b2;
        --hms-accent-soft: #ecfeff;
        --hms-ink: #0f172a;
        --hms-muted: #64748b;
        --hms-line: #e5e9f0;
        --hms-canvas: #f4f6fb;
        --hms-sidebar: #101828;
        --hms-sidebar-soft: #1d2939;
        --hms-radius: 16px;
        --hms-radius-sm: 10px;
        --hms-shadow-sm: 0 1px 2px rgba(16, 24, 40, .05), 0 8px 24px -12px rgba(16, 24, 40, .18);
        --hms-font: "Inter", "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial, sans-serif;
    }

    * { -webkit-tap-highlight-color: transparent; }

    body {
        font-family: var(--hms-font);
        color: var(--hms-ink);
        background: var(--hms-canvas);
        -webkit-font-smoothing: antialiased;
    }

    a { text-decoration: none; }

    /* ---------------------------------------------------------------
     | Brand
     | ------------------------------------------------------------- */
    .hms-brand {
        display: inline-flex;
        align-items: center;
        gap: .7rem;
        font-weight: 800;
        letter-spacing: -.02em;
    }

    .hms-brand__mark {
        width: 40px;
        height: 40px;
        border-radius: 13px;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #6366f1, #06b6d4);
        color: #fff;
        font-size: 1.2rem;
        box-shadow: 0 10px 22px -8px rgba(99, 102, 241, .7);
        flex: 0 0 auto;
    }

    .hms-brand__mark--logo {
        background: none;
        box-shadow: none;
        padding: 0;
    }

    .hms-brand__mark--logo img {
        width: 40px;
        height: 40px;
        border-radius: 13px;
        display: block;
        box-shadow: 0 10px 22px -8px rgba(99, 102, 241, .7);
    }

    .hms-brand__text { line-height: 1.05; font-size: 1.02rem; }
    .hms-brand__text small {
        display: block;
        font-size: .66rem;
        font-weight: 600;
        letter-spacing: .14em;
        text-transform: uppercase;
        opacity: .65;
    }

    /* ---------------------------------------------------------------
     | Layout shell
     | ------------------------------------------------------------- */
    .hms-shell { display: flex; min-height: 100vh; }

    .hms-sidebar {
        width: 272px;
        flex: 0 0 272px;
        background:
            radial-gradient(420px 220px at 0% 0%, rgba(99, 102, 241, .35), transparent 65%),
            linear-gradient(180deg, #101828 0%, #16223a 60%, #101828 100%);
        color: #cbd5e1;
        padding: 1.2rem 1rem 1.5rem;
        position: sticky;
        top: 0;
        align-self: flex-start;
        height: 100vh;
        overflow-y: auto;
        z-index: 1040;
    }

    .hms-sidebar::-webkit-scrollbar { width: 6px; }
    .hms-sidebar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, .14); border-radius: 99px; }

    .hms-sidebar .hms-brand { color: #fff; padding: .3rem .45rem 1.1rem; }
    .hms-sidebar .hms-brand__text small { opacity: .55; }

    .hms-sidebar__close {
        display: none;
        position: absolute;
        top: .9rem;
        right: .9rem;
        background: rgba(255, 255, 255, .08);
        border: 0;
        color: #fff;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        place-items: center;
        cursor: pointer;
    }

    .hms-nav-label {
        font-size: .66rem;
        letter-spacing: .16em;
        text-transform: uppercase;
        color: #7c8ea6;
        margin: 1.3rem .6rem .5rem;
        font-weight: 700;
    }

    .hms-nav-link {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .66rem .8rem;
        border-radius: var(--hms-radius-sm);
        color: #cbd5e1;
        font-size: .93rem;
        font-weight: 500;
        transition: background .16s ease, color .16s ease, transform .16s ease;
    }

    .hms-nav-link i { font-size: 1.08rem; width: 1.3rem; text-align: center; }

    .hms-nav-link:hover {
        background: rgba(255, 255, 255, .07);
        color: #fff;
        transform: translateX(3px);
    }

    .hms-nav-link.active {
        background: linear-gradient(90deg, #6366f1, #06b6d4);
        color: #fff;
        font-weight: 600;
        box-shadow: 0 10px 22px -10px rgba(6, 182, 212, .8);
    }

    .hms-sidebar__footer {
        margin-top: 1.6rem;
        padding: .95rem 1rem;
        border-radius: 14px;
        background: rgba(255, 255, 255, .06);
        border: 1px solid rgba(255, 255, 255, .08);
        font-size: .8rem;
        color: #94a3b8;
    }

    .hms-main {
        flex: 1 1 auto;
        min-width: 0;
        display: flex;
        flex-direction: column;
    }

    .hms-topbar {
        background: rgba(255, 255, 255, .86);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-bottom: 1px solid var(--hms-line);
        padding: .8rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        position: sticky;
        top: 0;
        z-index: 1030;
    }

    .hms-burger {
        display: none;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        border: 1px solid var(--hms-line);
        background: #fff;
        color: var(--hms-ink);
        font-size: 1.15rem;
        place-items: center;
        cursor: pointer;
        flex: 0 0 auto;
    }

    .hms-topbar__title { font-weight: 800; font-size: 1.06rem; margin: 0; letter-spacing: -.01em; }
    .hms-topbar__title small {
        display: block;
        font-weight: 400;
        font-size: .78rem;
        color: var(--hms-muted);
        letter-spacing: 0;
    }

    .hms-content { padding: 1.6rem 1.5rem 2.75rem; flex: 1 1 auto; }

    .hms-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--hms-line);
        color: var(--hms-muted);
        font-size: .8rem;
        background: #fff;
    }

    .hms-sidebar__backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .5);
        backdrop-filter: blur(2px);
        z-index: 1035;
    }

    /* Mobile: the sidebar becomes an off-canvas drawer */
    @media (max-width: 991.98px) {
        .hms-burger { display: grid; }

        .hms-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            height: 100dvh;
            transform: translateX(-105%);
            transition: transform .25s ease;
            box-shadow: 24px 0 60px rgba(15, 23, 42, .35);
        }

        .hms-shell.nav-open .hms-sidebar { transform: translateX(0); }
        .hms-shell.nav-open .hms-sidebar__backdrop { display: block; }
        .hms-sidebar__close { display: grid; }
        .hms-content { padding: 1.15rem 1rem 2.25rem; }
        .hms-topbar { padding: .7rem 1rem; }
    }

    @media print {
        .hms-sidebar, .hms-topbar, .hms-footer, .no-print { display: none !important; }
        .hms-content { padding: 0; }
        body { background: #fff; }
    }
</style>
