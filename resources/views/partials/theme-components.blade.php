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

    .hms-stat--primary { background: linear-gradient(135deg, #4f46e5, #6366f1); }
    .hms-stat--accent  { background: linear-gradient(135deg, #0e7490, #06b6d4); }
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
        border: 1px solid rgba(79, 70, 229, .18);
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
        color: #0e7490;
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

    .table > :not(caption) > * > * { padding: .7rem .85rem; }

    .pagination { margin: 0; }

    @media print {
        .no-print { display: none !important; }
        body { background: #fff; }
    }
</style>
