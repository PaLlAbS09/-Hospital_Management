{{-- Marketing / landing page components. --}}
<style>
    .hms-hero {
        background:
            radial-gradient(1000px 400px at 12% -10%, rgba(99, 102, 241, .45), transparent 60%),
            radial-gradient(800px 380px at 88% 110%, rgba(6, 182, 212, .35), transparent 60%),
            linear-gradient(135deg, #101828, #312e81 55%, #0e7490);
        color: #fff;
        border-radius: 24px;
        padding: 3rem 2.5rem;
        box-shadow: 0 30px 60px -30px rgba(49, 46, 129, .55);
    }

    .hms-hero h1 { font-weight: 800; letter-spacing: -.02em; }
    .hms-hero .lead { color: rgba(255, 255, 255, .86); }

    .hms-hero__stat {
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 14px;
        padding: .85rem 1rem;
        height: 100%;
    }

    .hms-hero__stat strong { font-size: 1.5rem; display: block; line-height: 1.1; }
    .hms-hero__stat span { font-size: .78rem; text-transform: uppercase; letter-spacing: .08em; opacity: .8; }

    .hms-feature {
        display: flex;
        gap: .9rem;
        align-items: flex-start;
        padding: 1rem 1.1rem;
        border-radius: var(--hms-radius);
        background: #fff;
        border: 1px solid var(--hms-line);
        height: 100%;
    }

    .hms-feature i {
        font-size: 1.35rem;
        color: var(--hms-primary);
        line-height: 1;
        margin-top: .18rem;
    }

    .hms-feature h6 { font-weight: 700; margin-bottom: .2rem; }
    .hms-feature p { margin: 0; font-size: .87rem; color: var(--hms-muted); }

    .hms-role-card {
        border: 1px solid var(--hms-line);
        border-radius: var(--hms-radius);
        background: #fff;
        padding: 1.4rem;
        height: 100%;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .hms-role-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 38px -18px rgba(15, 23, 42, .28);
        border-color: rgba(79, 70, 229, .35);
    }

    .hms-role-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 13px;
        display: grid;
        place-items: center;
        font-size: 1.4rem;
        background: var(--hms-primary-soft);
        color: var(--hms-primary-dark);
        margin-bottom: .85rem;
    }

    .hms-public-nav {
        background: rgba(255, 255, 255, .88);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-bottom: 1px solid var(--hms-line);
        position: sticky;
        top: 0;
        z-index: 30;
    }

    .hms-public-nav .nav-link { color: var(--hms-ink); font-weight: 500; }
    .hms-public-nav .nav-link:hover { color: var(--hms-primary); }
</style>
