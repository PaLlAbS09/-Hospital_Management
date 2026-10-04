{{--
    Boots every `.swiper` element that declares its options through
    `data-hms-swiper` (a JSON object). Keeps Swiper usage declarative and means
    the CDN bundle only needs loading once on public pages.
--}}
<script>
    (function () {
        if (typeof Swiper === 'undefined') return;

        document.querySelectorAll('.swiper[data-hms-swiper]').forEach(function (el) {
            if (el.dataset.hmsSwiperReady === '1') return;

            let options = {};
            try {
                options = JSON.parse(el.dataset.hmsSwiper || '{}');
            } catch (error) {
                console.error('Invalid Swiper options for', el.id, error);
                return;
            }

            const resolve = (selector) => (selector ? el.querySelector(selector) : null);

            new Swiper(el, Object.assign({
                slidesPerView: 1,
                spaceBetween: 20,
                loop: el.querySelectorAll('.swiper-slide').length > 1,
                pagination: { el: resolve('.swiper-pagination'), clickable: true },
                navigation: {
                    nextEl: resolve('.swiper-button-next'),
                    prevEl: resolve('.swiper-button-prev'),
                },
            }, options));

            el.dataset.hmsSwiperReady = '1';
        });
    })();
</script>