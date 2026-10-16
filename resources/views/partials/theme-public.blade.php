{{-- Marketing / landing page components. --}}
<style>
    /* Hero - Light clean medical theme (white + teal) */
    .hms-hero--light {
        background: #ffffff;
        color: var(--hms-ink);
        border: 1px solid #e0f2f1;
        border-radius: 24px;
        padding: 3rem 2.5rem;
        box-shadow: 0 24px 60px -28px rgba(15, 118, 110, .28);
        position: relative;
        overflow: hidden;
    }
    .hms-hero--light::before {
        content: ""; position: absolute; top: -140px; right: -120px;
        width: 420px; height: 420px; border-radius: 50%;
        background: radial-gradient(circle, rgba(20,184,166,.16), transparent 65%);
        pointer-events: none;
    }
    .hms-hero--light::after {
        content: ""; position: absolute; bottom: -160px; left: -100px;
        width: 380px; height: 380px; border-radius: 50%;
        background: radial-gradient(circle, rgba(29,78,216,.08), transparent 65%);
        pointer-events: none;
    }

    .hms-hero--bg { position: relative; overflow: hidden; min-height: 560px; }
    .hms-hero__bg-img {
        position: absolute; inset: 0; width: 100%; height: 100%;
        object-fit: cover; object-position: center right;
        z-index: 0; pointer-events: none; opacity: 1;
    }
    .hms-hero__bg-overlay {
        position: absolute; inset: 0; z-index: 1; pointer-events: none;
       
        background: linear-gradient(95deg, rgba(255,255,255,.94) 0%, rgba(255,255,255,.78) 32%, rgba(255,255,255,.20) 58%, rgba(255,255,255,0) 75%);
    }
    .hms-hero--bg::before, .hms-hero--bg::after { display: none; }
    .hms-hero--bg > .row { position: relative; z-index: 2; }
    .hms-hero--light h1 {
        font-weight: 800; letter-spacing: -.02em;
        font-size: 2.6rem; line-height: 1.12; color: #0f172a;
    }
    .hms-hero__highlight { color: var(--hms-primary); }
    .hms-hero__eyebrow {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .32rem .8rem; border-radius: 999px;
        font-size: .76rem; font-weight: 700;
        background: #ccfbf1; color: #115e59;
    }
    .hms-hero--light .hms-hero__badge {
        width: 54px; height: 54px; border-radius: 16px;
        display: grid; place-items: center; font-size: 1.55rem;
        color: #fff; background: linear-gradient(135deg, #0f766e, #14b8a6);
        box-shadow: 0 12px 24px -10px rgba(15,118,110,.55); flex: 0 0 auto;
    }
    .hms-hero--light .hms-hero__trust {
        display: inline-flex; align-items: center; gap: .45rem;
        font-size: .8rem; font-weight: 600;
        color: var(--hms-muted); margin-top: .35rem;
    }
    .hms-hero--light .hms-hero__trust i { color: var(--hms-primary); }
    .hms-hero__lead { color: #475569; font-size: 1.06rem; line-height: 1.65; max-width: 560px; }

    /* Landing shared bits */
    .hms-landing h2 { font-weight: 800; letter-spacing: -.02em; font-size: 1.7rem; color: #0f172a; margin: .5rem 0 .3rem; }
    .hms-landing .hms-section-head p { color: #64748b; margin: 0; font-size: .95rem; }
    .hms-section-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; }
    .hms-eyebrow {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .32rem .8rem; border-radius: 999px;
        font-size: .74rem; font-weight: 700;
        background: #ccfbf1; color: #115e59;
    }
    .hms-eyebrow--on-teal { background: rgba(255,255,255,.16); color: #fff; border: 1px solid rgba(255,255,255,.3); }

    /* Step cards */
    .hms-step-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 20px;
        padding: 1.5rem; height: 100%; position: relative; overflow: hidden;
    }
    .hms-step-card__num { position: absolute; top: .6rem; right: 1rem; font-size: 2.6rem; font-weight: 800; color: #f0fdfa; line-height: 1; }
    .hms-step-card__icon {
        width: 46px; height: 46px; border-radius: 14px; display: grid; place-items: center;
        font-size: 1.3rem; color: #fff; background: linear-gradient(135deg, #0f766e, #14b8a6); margin-bottom: .9rem;
    }
    .hms-step-card h6 { font-weight: 800; margin-bottom: .3rem; color: #0f172a; }
    .hms-step-card p { margin: 0; font-size: .88rem; color: #64748b; }

    /* Clinic cards */
    .hms-clinic-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 20px;
        padding: 1.25rem; display: flex; flex-direction: column; gap: .9rem;
    }
    .hms-clinic-card__top { display: flex; align-items: flex-start; gap: .8rem; }
    .hms-clinic-card__icon {
        width: 46px; height: 46px; border-radius: 14px; display: grid; place-items: center; flex: 0 0 auto;
        font-size: 1.25rem; background: #f0fdfa; color: #0f766e; border: 1px solid #ccfbf1;
    }
    .hms-clinic-card h3 { font-size: 1rem; font-weight: 800; margin: 0; color: #0f172a; }
    .hms-clinic-card__area { font-size: .82rem; color: #64748b; }
    .hms-clinic-card__contact { font-size: .84rem; color: #64748b; display: grid; gap: .2rem; }
    .hms-clinic-card__contact i { color: #0f766e; }
    .hms-clinic-card__foot { margin-top: auto; display: flex; align-items: center; justify-content: space-between; gap: .6rem; }

    .hms-hero__search {
        display: flex; gap: .6rem; align-items: stretch;
        max-width: 620px; background: #f8fafc;
        border: 1.5px solid #e2e8f0; border-radius: 16px; padding: .45rem;
    }
    .hms-hero__search:focus-within { border-color: var(--hms-primary); background: #fff; }
    .hms-hero__search-field { flex: 1 1 auto; display: flex; align-items: center; gap: .6rem; padding: 0 .4rem 0 1rem; min-width: 0; }
    .hms-hero__search-field > i { color: var(--hms-primary); font-size: 1.15rem; }
    .hms-hero__search-field .form-control { box-shadow: none !important; padding-left: 0; font-size: 1rem; }
    .hms-hero__search-btn { border-radius: 12px !important; padding-left: 1.6rem; padding-right: 1.6rem; white-space: nowrap; }
    .hms-hero--light .hms-hero__steps { display: flex; align-items: center; gap: .7rem; flex-wrap: wrap; }
    .hms-hero--light .hms-hero__step {
        display: inline-flex; align-items: center; gap: .65rem;
        padding: .55rem 1.1rem .55rem .55rem; border-radius: 14px;
        background: #f0fdfa; border: 1px solid #ccfbf1;
    }
    .hms-hero--light .hms-hero__step strong { display: block; font-size: .88rem; line-height: 1.2; color: #0f172a; }
    .hms-hero--light .hms-hero__step small { display: block; font-size: .74rem; color: #64748b; }
    .hms-hero--light .hms-hero__step-num {
        width: 34px; height: 34px; border-radius: 10px;
        display: grid; place-items: center; font-size: .9rem; font-weight: 800;
        background: linear-gradient(135deg, #0f766e, #14b8a6); color: #fff; flex: 0 0 auto;
    }
    .hms-hero--light .hms-hero__step-arrow { color: #99f6e4; font-size: .95rem; }
    @media (max-width: 575.98px) {
        .hms-hero--light { padding: 1.75rem 1.25rem; }
        .hms-hero--light h1 { font-size: 1.85rem; }
        .hms-hero__search { flex-direction: column; }
        .hms-hero__search-btn { width: 100%; }
        .hms-hero--light .hms-hero__step-arrow { display: none; }
    }
    .hms-hero--light .hms-hero__panel {
        background: linear-gradient(160deg, #f0fdfa, #ecfeff);
        border: 1px solid #ccfbf1; border-radius: 20px; padding: 1.4rem;
    }
    .hms-hero__live {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .74rem; font-weight: 700; color: #047857;
        background: #d1fae5; border: 1px solid #a7f3d0;
        border-radius: 999px; padding: .28rem .75rem;
    }
    .hms-hero--light .hms-hero__stat {
        background: #fff; border: 1px solid #e2e8f0;
        border-radius: 16px; padding: 1rem; height: 100%;
    }
    .hms-hero--light .hms-hero__stat:hover { border-color: #99f6e4; }
    .hms-hero--light .hms-hero__stat i {
        display: grid; place-items: center; width: 36px; height: 36px;
        border-radius: 11px; background: #ccfbf1; color: #115e59;
        font-size: 1.05rem; margin-bottom: .6rem;
    }
    .hms-hero--light .hms-hero__stat--accent i { background: #dbeafe; color: #1e40af; }
    .hms-hero--light .hms-hero__stat strong { font-size: 1.6rem; display: block; line-height: 1.1; color: #0f172a; }
    .hms-hero--light .hms-hero__stat span { font-size: .74rem; text-transform: uppercase; letter-spacing: .07em; color: #64748b; font-weight: 600; }

    /* Portals + contact */
    .hms-portals {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 22px; padding: 1.75rem;
    }
    .hms-portals h2 { font-size: 1.5rem; }
    .hms-portals p { color: #64748b; }
    .hms-portal-link {
        display: flex; flex-direction: column; gap: .1rem; align-items: flex-start;
        background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1rem;
    }
    .hms-portal-link i { font-size: 1.3rem; color: #0f766e; margin-bottom: .3rem; }
    .hms-portal-link strong { color: #0f172a; }
    .hms-portal-link small { color: #64748b; font-size: .76rem; }
    .hms-contact {
        border-radius: 24px; overflow: hidden; border: 1px solid #0f766e;
        background: #fff;
    }
    .hms-contact__side {
        background: linear-gradient(160deg, #0f766e, #115e59); color: #fff; padding: 2.5rem;
    }
    .hms-contact__side h2 { color: #fff; }
    .hms-contact__side p { color: rgba(255,255,255,.82); font-size: .92rem; }
    .hms-contact__side ul { list-style: none; padding: 0; margin: 1.25rem 0 0; display: grid; gap: .6rem; font-size: .86rem; color: rgba(255,255,255,.88); }
    .hms-contact__side ul i { margin-right: .5rem; }
    .hms-contact__form { padding: 2.5rem; }

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
