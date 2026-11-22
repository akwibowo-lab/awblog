<button
    id="filament-back-to-top"
    type="button"
    aria-label="Kembali ke atas"
    title="Kembali ke atas"
    style="display:none;position:fixed;right:1.25rem;bottom:1.25rem;z-index:40;width:2.75rem;height:2.75rem;align-items:center;justify-content:center;border-radius:9999px;border:1px solid rgba(245,158,11,0.45);background:rgba(17,24,39,0.92);color:#f59e0b;box-shadow:0 10px 25px rgba(0,0,0,0.35);cursor:pointer;"
>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M12 19V5M5 12l7-7 7 7" />
    </svg>
</button>
<script>
    (function () {
        var btn = document.getElementById('filament-back-to-top');
        if (!btn) return;
        var toggle = function () {
            btn.style.display = window.scrollY > 280 ? 'inline-flex' : 'none';
        };
        window.addEventListener('scroll', toggle, { passive: true });
        toggle();
        btn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    })();
</script>
