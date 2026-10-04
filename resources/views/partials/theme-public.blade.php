{{-- Marketing / landing page components. --}}
<style>
    /* ---------------------------------------------------------------
     | Hero (premium layout, original colour palette unchanged)
     | ------------------------------------------------------------- */
    .hms-hero {
        background:
            radial-gradient(1000px 400px at 12% -10%, rgba(99, 102, 241, .45), transparent 60%),
            radial-gradient(800px 380px at 88% 110%, rgba(6, 182, 212, .35), transparent 60%),
            linear-gradient(135deg, #101828, #312e81 55%, #0e7490);
        color: #fff;
        border-radius: 24px;
        padding: 3rem 2.5rem;
        box-shadow: 0 30px 60px -30px rgba(49, 46, 129, .55);
        position: relative;
        overflow: hidden;
        isolation: isolate;
    }

    /* Soft decorative glows layered behind the hero content. */
    .hms-hero::before,
    .hms-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
        z-index: -1;
    }

    .hms-hero::before {
        width: 340px;
        height: 340px;
        top: -160px;
        right: -80px;
        background: radial-gradient(circle, rgba(255, 255, 255, .16), transparent 65%);
    }

    .hms-hero::after {
        width: 280px;
        height: 280px;
        bottom: -150px;
        left: 32%;
        background: radial-gradient(circle, rgba(6, 182, 212, .28), transparent 68%);
    }

    .hms-hero h1 { font-weight: 800; letter-spacing: -.02em; }
    .hms-hero .lead { color: rgba(255, 255, 255, .86); }

    .hms-hero__badge {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        font-size: 1.55rem;
        background: rgba(255, 255, 255, .14);
        border: 1px solid rgba(255, 255, 255, .22);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .25);
    }

    .hms-hero__trust {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        font-size: .8rem;
        font-weight: 600;
        color: rgba(255, 255, 255, .78);
    }

    .hms-hero__trust i { color: #6ee7f5; }

    .hms-hero__stat {
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 14px;
        padding: .85rem 1rem;
        height: 100%;
        transition: transform .18s ease, background .18s ease, border-color .18s ease;
    }

    .hms-hero__stat:hover {
        transform: translateY(-3px);
        background: rgba(255, 255, 255, .18);
        border-color: rgba(255, 255, 255, .3);
    }

    .hms-hero__stat strong { font-size: 1.5rem; display: block; line-height: 1.1; }
    .hms-hero__stat span { font-size: .78rem; text-transform: uppercase; letter-spacing: .08em; opacity: .8; }

    .hms-hero__panel {
        background: rgba(255, 255, 255, .1);
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 18px;
        padding: 1.15rem 1.25rem;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }

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

    /* ---------------------------------------------------------------
     | Landing page promo slider (new doctors + discount offers)
     | ------------------------------------------------------------- */
    .hms-slider { position: relative; padding: 2px 0 42px; }

    .hms-slider .swiper { overflow: hidden; border-radius: 22px; }
    .hms-slider .swiper-slide { height: auto; display: flex; }

    .hms-slider .swiper-pagination-bullet { width: 9px; height: 9px; background: #cbd5e1; opacity: 1; }

    .hms-slider .swiper-pagination-bullet-active {
        width: 26px;
        border-radius: 99px;
        background: var(--hms-primary);
    }

    .hms-slider .swiper-button-next,
    .hms-slider .swiper-button-prev {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #fff;
        color: var(--hms-primary);
        box-shadow: var(--hms-shadow-sm);
        border: 1px solid var(--hms-line);
        transition: transform .16s ease;
    }

    .hms-slider .swiper-button-next:hover,
    .hms-slider .swiper-button-prev:hover { transform: scale(1.06); }

    .hms-slider .swiper-button-next::after,
    .hms-slider .swiper-button-prev::after { font-size: 1rem; font-weight: 800; }

    .hms-promo {
        display: flex;
        width: 100%;
        border-radius: 22px;
        border: 1px solid var(--hms-line);
        background: #fff;
        box-shadow: var(--hms-shadow-sm);
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .hms-promo:hover { transform: translateY(-4px); box-shadow: 0 22px 46px -22px rgba(15, 23, 42, .32); }

    .hms-promo__media {
        flex: 0 0 40%;
        min-height: 210px;
        display: grid;
        place-items: center;
        padding: 1.5rem;
        color: #fff;
        text-align: center;
    }

    .hms-promo--doctor .hms-promo__media {
        background:
            radial-gradient(320px 180px at 20% 0%, rgba(99, 102, 241, .55), transparent 65%),
            linear-gradient(135deg, #1e1b4b, #312e81 60%, #4338ca);
    }

    .hms-promo--offer .hms-promo__media {
        background:
            radial-gradient(320px 180px at 80% 10%, rgba(251, 191, 36, .45), transparent 65%),
            linear-gradient(135deg, #7c2d12, #b45309 60%, #d97706);
    }

    .hms-promo__body { flex: 1 1 auto; padding: 1.5rem 1.6rem; display: flex; flex-direction: column; }

    .hms-promo__eyebrow {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--hms-muted);
    }

    .hms-promo__title { font-size: 1.3rem; font-weight: 800; letter-spacing: -.01em; margin: .35rem 0 .5rem; }
    .hms-promo__text { color: var(--hms-muted); font-size: .9rem; }

    .hms-promo__meta { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: auto; padding-top: 1rem; }

    .hms-promo__discount { font-size: 2.6rem; font-weight: 800; line-height: 1; letter-spacing: -.03em; }

    .hms-promo__doctor-initial {
        width: 84px;
        height: 84px;
        border-radius: 26px;
        display: grid;
        place-items: center;
        margin: 0 auto .75rem;
        font-size: 1.9rem;
        font-weight: 800;
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .3);
    }

    /* ---------------------------------------------------------------
     | Best doctor cards
     | ------------------------------------------------------------- */
    .hms-best {
        border: 1px solid var(--hms-line);
        border-radius: var(--hms-radius);
        background: #fff;
        padding: 1.35rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        gap: .7rem;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .hms-best:hover {
        transform: translateY(-4px);
        border-color: rgba(79, 70, 229, .3);
        box-shadow: 0 18px 38px -20px rgba(15, 23, 42, .3);
    }

    .hms-best__avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-weight: 800;
        font-size: 1.05rem;
        background: var(--hms-primary-soft);
        color: var(--hms-primary-dark);
        border: 1px solid rgba(79, 70, 229, .18);
    }

    .hms-best__badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .28rem .65rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .02em;
    }

    .hms-best__badge--gold { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .hms-best__badge--indigo { background: var(--hms-primary-soft); color: var(--hms-primary-dark); border: 1px solid rgba(79, 70, 229, .2); }

    .hms-best__stars { color: #f59e0b; letter-spacing: .08em; font-size: .92rem; }
    .hms-best__score { font-weight: 800; }

    /* ---------------------------------------------------------------
     | Clinic about section
     | ------------------------------------------------------------- */
    .hms-about-banner {
        border-radius: var(--hms-radius);
        overflow: hidden;
        min-height: 220px;
        display: grid;
        place-items: center;
        background:
            radial-gradient(420px 200px at 15% 0%, rgba(99, 102, 241, .5), transparent 65%),
            linear-gradient(135deg, #101828, #312e81 60%, #0e7490);
        color: #fff;
        text-align: center;
        padding: 2rem;
    }

    .hms-about-banner img { width: 100%; height: 100%; min-height: 220px; object-fit: cover; }
</style>
