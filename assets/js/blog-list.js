/* Blog index: the topic and the search word filter the cards in place.
   Without this script the form submits and the server does the same job. */
(() => {
    const form = document.querySelector('[data-bf]');
    if (!form) return;
    const cat = form.querySelector('[data-bf-cat]');
    const q = form.querySelector('[data-bf-q]');
    const count = form.querySelector('[data-bf-count]');
    const none = document.querySelector('[data-bf-none]');
    const cards = [...document.querySelectorAll('[data-bf-card]')];
    const extras = [...document.querySelectorAll('[data-bf-extra]')];
    /* The server may already have narrowed the list (a topic address). Then
       changing the topic needs the full list, so load it. */
    const narrowed = new URLSearchParams(location.search).has('cat') || new URLSearchParams(location.search).has('q');
    form.classList.add('bf--js');

    const apply = () => {
        const c = cat.value;
        const words = q.value.toLowerCase().trim().split(/\s+/).filter(Boolean);
        let shown = 0;
        cards.forEach((card) => {
            const ok = (!c || card.dataset.cat === c) && words.every((w) => card.dataset.text.includes(w));
            card.hidden = !ok;
            if (ok) { shown++; card.classList.add('is-visible'); }
        });
        const filtered = Boolean(c || words.length);
        extras.forEach((e) => { e.hidden = filtered; });
        none.hidden = shown > 0;
        count.textContent = shown === 0 ? 'No articles match'
            : shown + (shown === 1 ? ' article' : ' articles') + (c ? ' on ' + c : '') + (words.length ? ' mentioning "' + q.value.trim() + '"' : '') + (filtered ? '' : ', newest first');
        const url = new URL(location.href);
        if (c) url.searchParams.set('cat', c); else url.searchParams.delete('cat');
        url.searchParams.delete('q');
        history.replaceState(null, '', url);
    };

    cat.addEventListener('change', () => { if (narrowed) form.submit(); else apply(); });
    q.addEventListener('input', apply);
    form.addEventListener('submit', (e) => { if (!narrowed) { e.preventDefault(); apply(); q.blur(); } });
    if (!narrowed) apply();
})();
