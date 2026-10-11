{{-- Cards, tables, stats and small components. --}}
<style>
    .hms-card {
        background: #fff;
        border: 1px solid var(--hms-line);
        border-radius: var(--hms-radius);
        box-shadow: var(--hms-shadow-sm);
    }

    .hms-card__header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--hms-line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .hms-card__header h5 { margin: 0; font-size: 1rem; font-weight: 700; }
    .hms-card__body { padding: 1.25rem; }
    .hms-card__body--flush { padding: 0; }

    .hms-stat {
        border-radius: var(--hms-radius);
        padding: 1.15rem 1.25rem;
        color: #fff;
        position: relative;
        overflow: hidden;
        height: 100%;
    }

    .hms-stat__value { font-size: 2rem; font-weight: 800; line-height: 1; }
    .hms-stat__label { font-size: .84rem; text-transform: uppercase; letter-spacing: .07em; opacity: .9; }
    .hms-stat__icon {
        position: absolute;
        right: .85rem;
        top: .8rem;
        font-size: 2.6rem;
        opacity: .22;
    }

    .hms-stat--primary { background: linear-gradient(135deg, #0f766e, #14b8a6); }
    .hms-stat--accent  { background: linear-gradient(135deg, #1d4ed8, #3b82f6); }
    .hms-stat--amber   { background: linear-gradient(135deg, #b45309, #f59e0b); }
    .hms-stat--rose    { background: linear-gradient(135deg, #be123c, #fb7185); }
    .hms-stat--violet  { background: linear-gradient(135deg, #6d28d9, #a78bfa); }
    .hms-stat--slate   { background: linear-gradient(135deg, #334155, #64748b); }

    .hms-table { margin: 0; }
    .hms-table thead th {
        background: #f8fafc;
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--hms-muted);
        font-weight: 700;
        white-space: nowrap;
        border-bottom: 1px solid var(--hms-line);
    }

    .hms-table td { vertical-align: middle; }
    .hms-table tbody tr:hover { background: #f8fbfc; }

    .hms-empty {
        padding: 2.5rem 1rem;
        text-align: center;
        color: var(--hms-muted);
    }

    .hms-empty i { font-size: 2.2rem; display: block; margin-bottom: .6rem; opacity: .45; }

    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .hms-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--hms-primary-soft);
        color: var(--hms-primary-dark);
        border: 1px solid rgba(15, 118, 110, .25);
        display: grid;
        place-items: center;
        font-weight: 700;
        font-size: .85rem;
        flex: 0 0 auto;
    }

    .hms-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .22rem .6rem;
        border-radius: 999px;
        font-size: .74rem;
        font-weight: 600;
        background: var(--hms-accent-soft);
        color: #1e40af;
    }

    .hms-slot {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .35rem .7rem;
        border-radius: 9px;
        border: 1px solid var(--hms-line);
        background: #fff;
        font-size: .85rem;
        font-weight: 600;
        cursor: pointer;
        margin: 0 .35rem .35rem 0;
    }

    .hms-slot input { display: none; }

    .hms-slot:has(input:checked) {
        background: var(--hms-primary);
        border-color: var(--hms-primary);
        color: #fff;
    }

    .hms-slot--disabled {
        opacity: .45;
        cursor: not-allowed;
        text-decoration: line-through;
    }

    .hms-rating {
        display: inline-flex;
        align-items: flex-start;
        gap: .3rem;
        flex-wrap: wrap;
    }

    .hms-rating__star {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        gap: .15rem;
        cursor: pointer;
        padding: .25rem .4rem;
        border-radius: var(--hms-radius-sm);
        transition: background .15s ease, transform .15s ease;
    }

    .hms-rating__star:hover { background: var(--hms-primary-soft); transform: translateY(-2px); }

    .hms-rating__icon {
        font-size: 2rem;
        line-height: 1;
        color: #cbd5e1;
        transition: color .15s ease, transform .15s ease;
    }

    .hms-rating__star.is-active .hms-rating__icon { color: #f59e0b; transform: scale(1.08); }

    .hms-rating__label { font-size: .72rem; color: var(--hms-muted); font-weight: 600; }

    .hms-rating__star:focus-within { outline: 2px solid var(--hms-primary); outline-offset: 2px; }

    .btn-hms {
        background: var(--hms-primary);
        border-color: var(--hms-primary);
        color: #fff;
        font-weight: 600;
    }

    .btn-hms:hover, .btn-hms:focus {
        background: var(--hms-primary-dark);
        border-color: var(--hms-primary-dark);
        color: #fff;
    }

    .btn-outline-hms {
        border-color: var(--hms-primary);
        color: var(--hms-primary-dark);
        font-weight: 600;
    }

    .btn-outline-hms:hover { background: var(--hms-primary); color: #fff; }

    /* Google Maps embed --------------------------------------------------- */
    .hms-map__frame {
        overflow: hidden;
        border-radius: var(--hms-radius);
        border: 1px solid var(--hms-line);
        background: #f1f5f9;
    }

    .hms-map__frame iframe { display: block; width: 100%; height: 100%; border: 0; }

    .hms-map__list {
        list-style: none;
        margin: .85rem 0 0;
        padding: 0;
        display: grid;
        gap: .5rem;
    }

    .hms-map__item {
        display: flex;
        align-items: center;
        gap: .65rem;
        padding: .6rem .75rem;
        border: 1px solid var(--hms-line);
        border-radius: 12px;
        background: #fff;
    }

    .hms-map__item > i { flex: 0 0 auto; color: var(--hms-primary); }

    .hms-map__text { flex: 1 1 auto; min-width: 0; display: grid; gap: .1rem; }

    .hms-map__name {
        font-size: .88rem;
        font-weight: 700;
        color: #1e293b;
        text-decoration: none;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .hms-map__name:hover { color: var(--hms-primary); }

    .hms-map__address {
        font-size: .78rem;
        color: var(--hms-muted);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .hms-map__link { flex: 0 0 auto; white-space: nowrap; }

    .table > :not(caption) > * > * { padding: .7rem .85rem; }

    .pagination { margin: 0; }

    /* ---------------------------------------------------------------
     | Clinical UI standard: badges, responsive tables, forms, loaders
     | ------------------------------------------------------------- */
    .hms-badge { font-weight: 700; letter-spacing: .02em; border: 1px solid transparent; }
    .hms-badge--success { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }
    .hms-badge--warning { background: #fffbeb; color: #92400e; border-color: #fde68a; }
    .hms-badge--danger { background: #fff1f2; color: #9f1239; border-color: #fecdd3; }
    .hms-badge--primary { background: #eef2ff; color: #3730a3; border-color: #c7d2fe; }
    .hms-badge--dark { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }
    .hms-badge--secondary { background: #f8fafc; color: #475569; border-color: #e2e8f0; }

    .hms-field { margin-bottom: 0; }
    .hms-fieldset { border: 1px solid var(--hms-line); border-radius: var(--hms-radius-sm); padding: 1rem 1.1rem 1.1rem; margin: 0 0 1rem; background: #fbfdff; }
    .hms-fieldset > legend { float: none; width: auto; padding: 0 .55rem; margin: 0; font-size: .78rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--hms-muted); }
    .hms-autosize { min-height: 110px; resize: none; overflow: hidden; line-height: 1.55; }
    .form-control.is-invalid, .form-select.is-invalid { border-color: #e11d48; }
    .form-control.is-invalid:focus, .form-select.is-invalid:focus { border-color: #e11d48; box-shadow: 0 0 0 .25rem rgba(225, 29, 72, .18); }
    .form-control:focus-visible, .form-select:focus-visible, .btn:focus-visible, a:focus-visible { outline: 2px solid var(--hms-primary); outline-offset: 2px; }

    /* Oversized patient calls-to-action */
    .hms-cta-lg { padding: .9rem 1.4rem; font-size: 1.02rem; font-weight: 800; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; gap: .55rem; min-height: 56px; }
    .hms-cta-lg i { font-size: 1.25rem; }

    /* Skeleton loaders keep users grounded during async work */
    .hms-skeleton { position: relative; overflow: hidden; background: #eef2f7; border-radius: 10px; min-height: 1rem; }
    .hms-skeleton::after { content: ""; position: absolute; inset: 0; transform: translateX(-100%); background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .75), transparent); animation: hms-shimmer 1.4s infinite; }
    @keyframes hms-shimmer { 100% { transform: translateX(100%); } }
    @media (prefers-reduced-motion: reduce) { .hms-skeleton::after { animation: none; } }
    .hms-skeleton-card { border: 1px solid var(--hms-line); border-radius: var(--hms-radius); padding: 1.1rem 1.2rem; background: #fff; display: grid; gap: .6rem; }

    /* Responsive tabular data: collapse rows into stacked cards < 768px */
    @media (max-width: 767.98px) {
        .hms-table--responsive thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; }
        .hms-table--responsive, .hms-table--responsive tbody, .hms-table--responsive tr, .hms-table--responsive td { display: block; width: 100%; }
        .hms-table--responsive tr { border-bottom: 2px solid var(--hms-line); padding: .65rem .9rem; background: #fff; }
        .hms-table--responsive td { border: 0; padding: .3rem 0; text-align: left !important; }
        .hms-table--responsive td::before { content: attr(data-label); display: block; font-size: .68rem; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: var(--hms-muted); margin-bottom: .15rem; }
        .hms-table--responsive td[data-label=""]::before { content: none; }
        .hms-table--responsive td.text-end { text-align: left !important; }
        .hms-table--responsive .d-inline-flex { flex-wrap: wrap; }
    }

    @media print {
        .no-print { display: none !important; }
        body { background: #fff; }
    }
</style>
