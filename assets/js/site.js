// Efek halaman Zine: parallax kolase, reveal "halaman berbalik", galeri horizontal, tumpukan catatan, kontak AJAX
(() => {
    const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const $ = (s, el = document) => el.querySelector(s);
    const $$ = (s, el = document) => [...el.querySelectorAll(s)];

    // Kolase sampul: tiap lapis bergeser sesuai data-depth mengikuti kursor + scroll
    const art = $('#coverArt'), layers = art ? $$('.layer', art) : [];
    let mx = 0, my = 0;
    const paint = () => {
        const sy = Math.min(scrollY, 900);
        layers.forEach(l => {
            const d = +l.dataset.depth || 0;
            l.style.translate = `${mx * d}px ${my * d - sy * d / 60}px`;
        });
        if (art) art.style.transform = `rotateY(${mx * 6}deg) rotateX(${-my * 6}deg)`;
    };
    if (art && !reduce) {
        art.style.transformStyle = 'preserve-3d';
        addEventListener('pointermove', e => { mx = e.clientX / innerWidth - 0.5; my = e.clientY / innerHeight - 0.5; requestAnimationFrame(paint); });
    }

    // Galeri karya horizontal: tinggi section = panjang lintasan, scroll vertikal menggeser track
    const works = $('#karya'), track = $('#track');
    const wide = () => innerWidth > 980 && !reduce;
    const sizeWorks = () => {
        if (!works || !track) return;
        if (!wide()) { works.style.height = ''; track.style.transform = ''; return; }
        const geser = Math.max(0, track.scrollWidth - innerWidth);
        works.style.height = (geser + innerHeight) + 'px';
        works.dataset.geser = geser;
    };
    const moveWorks = () => {
        if (!works || !wide()) return;
        const r = works.getBoundingClientRect();
        const total = works.offsetHeight - innerHeight + 90;
        const p = Math.min(1, Math.max(0, -r.top / Math.max(1, total)));
        track.style.transform = `translate3d(${-p * (+works.dataset.geser || 0)}px,0,0)`;
    };

    let ticking = false;
    addEventListener('scroll', () => {
        if (ticking) return; ticking = true;
        requestAnimationFrame(() => { moveWorks(); if (!reduce) paint(); ticking = false; });
    }, { passive: true });
    addEventListener('resize', () => { sizeWorks(); moveWorks(); });
    addEventListener('load', () => { sizeWorks(); moveWorks(); });
    sizeWorks();

    // Filter kategori karya
    $$('.filters button').forEach(b => b.addEventListener('click', () => {
        $$('.filters button').forEach(x => x.classList.toggle('on', x === b));
        $$('.poster[data-k]').forEach(c => c.classList.toggle('hide', b.dataset.f !== '*' && c.dataset.k !== b.dataset.f));
        sizeWorks(); moveWorks();
    }));
    $$('[data-go]').forEach(b => b.addEventListener('click', e => { e.preventDefault(); $(b.dataset.go)?.scrollIntoView({ behavior: 'smooth' }); }));

    // Reveal & hitung angka
    const count = el => {
        const n = +el.dataset.count; if (reduce) { el.textContent = n; return; }
        const t0 = performance.now();
        const step = t => { const p = Math.min(1, (t - t0) / 1100); el.textContent = Math.round(n * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(step); };
        requestAnimationFrame(step);
    };
    const io = new IntersectionObserver(es => es.forEach(en => {
        if (!en.isIntersecting) return;
        en.target.classList.add('in');
        $$('[data-count]', en.target).forEach(count);
        io.unobserve(en.target);
    }), { threshold: 0.12 });
    $$('.rv').forEach(el => io.observe(el));

    // Tumpukan catatan testimoni: kartu teratas "terbang", lalu pindah ke paling bawah
    const stack = $('#notes');
    if (stack) {
        const notes = $$('.note', stack);
        let order = notes.map((_, i) => i), busy = false;
        const layout = () => order.forEach((idx, k) => {
            const n = notes[idx];
            n.style.zIndex = notes.length - k;
            n.style.transform = n.classList.contains('gone') ? '' : `translate(${k * 10}px, ${k * 10}px) rotate(${k === 0 ? -1.5 : (k % 2 ? 3 : -3)}deg)`;
        });
        const next = () => {
            if (busy) return; busy = true;
            const top = notes[order[0]];
            top.classList.add('gone'); top.style.transform = '';
            setTimeout(() => { order.push(order.shift()); top.classList.remove('gone'); layout(); busy = false; }, reduce ? 0 : 520);
        };
        const prev = () => { order.unshift(order.pop()); layout(); };
        stack.addEventListener('click', next);
        $$('[data-note]').forEach(b => b.addEventListener('click', () => (b.dataset.note === 'next' ? next : prev)()));
        layout();
    }

    // Formulir kontak (fetch), tetap jalan tanpa JS
    const form = $('#cform'), msg = $('#formMsg');
    if (form) form.addEventListener('submit', async e => {
        e.preventDefault();
        if (!form.reportValidity()) return;
        const btn = $('button[type=submit]', form);
        btn.disabled = true; msg.className = 'form-msg'; msg.textContent = 'Menempel perangko...';
        try {
            const r = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'X-Requested-With': 'fetch' } });
            const j = await r.json();
            msg.className = 'form-msg ' + (j.ok ? 'ok' : 'err'); msg.textContent = j.msg;
            if (j.ok) form.reset();
        } catch { msg.className = 'form-msg err'; msg.textContent = 'Kotak posnya macet. Coba lagi sebentar lagi.'; }
        btn.disabled = false;
    });

    // Lightbox galeri
    const lb = $('#lb');
    if (lb) {
        $$('[data-lb]').forEach(a => a.addEventListener('click', e => { e.preventDefault(); $('img', lb).src = a.href; $('img', lb).alt = $('img', a).alt; lb.hidden = false; }));
        lb.addEventListener('click', () => { lb.hidden = true; });
        addEventListener('keydown', e => { if (e.key === 'Escape') lb.hidden = true; });
    }
    $$('#tabs a').forEach(a => a.addEventListener('click', () => document.body.classList.remove('menu-open')));
})();
