/* Article pages: the contents rail follows the reader.
   Wide screens: a list beside the article, with the current heading marked
   and kept in view. Narrow screens: the same list folds into a bar under the
   site header that names the current heading and opens on tap.
   Without this script the list is a plain set of links. */
(() => {
    const shell = document.querySelector('[data-pv]');
    if (!shell) return;
    const rail = shell.querySelector('[data-pv-rail]');
    const main = shell.querySelector('.pv-main');
    const links = [...shell.querySelectorAll('[data-pv-link]')];
    const targets = links.map((a) => document.getElementById(decodeURIComponent(a.hash.slice(1))));
    const toggle = shell.querySelector('[data-pv-toggle]');
    const list = shell.querySelector('.pv-list');
    const now = shell.querySelector('[data-pv-now]');
    const count = shell.querySelector('[data-pv-count]');
    const prog = shell.querySelector('[data-pv-prog]');
    const left = shell.querySelector('[data-pv-left]');
    const header = document.querySelector('.site-header');
    const minutes = parseInt(shell.dataset.read, 10) || 0;
    const root = document.documentElement;
    let active = -1;
    let ticking = false;
    const wide = window.matchMedia('(min-width: 1400px)');

    shell.classList.add('pv--js');

    const measure = () => {
        const h = header ? Math.round(header.getBoundingClientRect().height) : 0;
        root.style.setProperty('--pv-top', h + 'px');
    };

    const close = () => {
        rail.classList.remove('is-open');
        if (!wide.matches) toggle.setAttribute('aria-expanded', 'false');
    };

    /* Beside the article the list is always open: the label is not a control there. */
    const mode = () => {
        close();
        if (wide.matches) {
            toggle.tabIndex = -1;
            toggle.removeAttribute('aria-expanded');
            toggle.removeAttribute('aria-controls');
        } else {
            toggle.removeAttribute('tabindex');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-controls', 'pv-list');
        }
    };

    const update = () => {
        ticking = false;
        const line = (header ? header.getBoundingClientRect().bottom : 0) + 110;
        let i = 0;
        targets.forEach((t, n) => {
            /* Beside the article the form is always in view, so it is not a place to arrive at. */
            if (!t || (t.classList.contains('pv-side') && getComputedStyle(t).position === 'sticky')) return;
            if (t.getBoundingClientRect().top <= line) i = n;
        });
        if (i !== active) {
            active = i;
            links.forEach((a, n) => {
                a.classList.toggle('is-active', n === i);
                a.classList.toggle('is-done', n < i);
                if (n === i) a.setAttribute('aria-current', 'true'); else a.removeAttribute('aria-current');
            });
            now.textContent = links[i].lastElementChild.textContent;
            count.textContent = (i + 1) + ' of ' + links.filter((l) => l.offsetParent !== null || getComputedStyle(l.parentNode).display !== 'none').length;
            /* Keep the current heading in view inside the list, without moving the page. */
            const a = links[i];
            const top = a.offsetTop - list.clientHeight / 2 + a.offsetHeight / 2;
            if (list.scrollHeight > list.clientHeight) list.scrollTo({ top, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
        }
        const box = main.getBoundingClientRect();
        const span = box.height - window.innerHeight * 0.6;
        const done = Math.max(0, Math.min(1, (line - box.top) / (span > 0 ? span : 1)));
        prog.style.setProperty('--p', (done * 100).toFixed(1) + '%');
        rail.classList.toggle('is-started', box.top < line - 110);
        if (left && minutes) {
            const m = Math.ceil(minutes * (1 - done));
            left.textContent = done >= 1 ? 'You reached the end' : m <= 1 ? 'About a minute left' : 'About ' + m + ' minutes left';
        }
    };
    const queue = () => { if (!ticking) { ticking = true; requestAnimationFrame(update); } };

    toggle.addEventListener('click', () => {
        if (wide.matches) return;
        const open = !rail.classList.contains('is-open');
        rail.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        /* Open on the current heading, not at the top of the list. */
        if (open && links[active]) list.scrollTop = links[active].offsetTop - list.clientHeight / 2 + links[active].offsetHeight / 2;
    });
    links.forEach((a) => a.addEventListener('click', close));
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape' || !rail.classList.contains('is-open')) return;
        const inside = rail.contains(document.activeElement);
        close();
        if (inside) toggle.focus();
    });
    rail.addEventListener('focusout', (e) => { if (e.relatedTarget && !rail.contains(e.relatedTarget)) close(); });
    /* Keep a focused link or field clear of the site header and the contents bar. */
    shell.addEventListener('focusin', (e) => {
        const el = e.target;
        if (rail.contains(el)) return;
        requestAnimationFrame(() => {
            let cover = header ? header.getBoundingClientRect().bottom : 0;
            if (!wide.matches) cover = Math.max(cover, toggle.getBoundingClientRect().bottom);
            const gap = cover + 12 - el.getBoundingClientRect().top;
            if (gap > 0) window.scrollBy({ top: -gap, behavior: 'instant' });
        });
    });
    /* Beside the article the form is already on screen: go to its first field. */
    /* Every "talk to us" or quote link on the page goes straight to the form's first field. */
    document.querySelectorAll('a[href="#post-enquiry"]').forEach((a) => a.addEventListener('click', (e) => {
        const side = document.getElementById('post-enquiry');
        const first = side && side.querySelector('input[name="name"]');
        if (!first) return;
        e.preventDefault();
        close();
        first.focus({ preventScroll: true });
        first.scrollIntoView({ block: 'center', behavior: 'instant' });
    }));
    document.addEventListener('click', (e) => { if (!rail.contains(e.target)) close(); });

    measure();
    mode();
    wide.addEventListener('change', mode);
    update();
    window.addEventListener('scroll', queue, { passive: true });
    window.addEventListener('resize', () => { measure(); queue(); });
    if (header && 'ResizeObserver' in window) new ResizeObserver(() => { measure(); queue(); }).observe(header);
})();
