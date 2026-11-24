/* Vee: the AI live chat widget. Reads its settings from window.VEE_CONFIG
   (printed by includes/chat.php's chat_widget_config(), via footer.php).
   Talks to /chat/api/ (includes/chat.php chat_route()). Replaces the tawk.to
   widget: the .js-live-chat buttons in the header/footer open this panel. */
(function () {
    'use strict';
    var cfg = window.VEE_CONFIG;
    if (!cfg || !document.body) { return; }

    var SID_KEY = 'vee_sid';
    var HIST_KEY = 'vee_history';
    var SEEN_KEY = 'vee_teaser_seen';
    var sid = '';
    try { sid = localStorage.getItem(SID_KEY) || ''; } catch (e) {}
    var history = [];
    try { history = JSON.parse(localStorage.getItem(HIST_KEY) || '[]'); } catch (e) { history = []; }
    var sending = false;
    var opened = false;
    var greeted = false;

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    /* Plain text only (the server already strips markdown), but a link to
       vturnu.com, wa.me or a mailto: still needs to render as a clickable one. */
    function linkify(text) {
        var html = escapeHtml(text);
        html = html.replace(/(https?:\/\/[^\s<>"]+)/gi, function (m) {
            return '<a href="' + m + '" target="_blank" rel="noopener">' + m + '</a>';
        });
        html = html.replace(/\b((?:www\.)?vturnu\.com(?:\/[^\s<>"]*)?|wa\.me\/\d+(?:\?[^\s<>"]*)?)\b(?![^<]*>)/gi, function (m) {
            var href = /^https?:\/\//i.test(m) ? m : 'https://' + m;
            return '<a href="' + href + '" target="_blank" rel="noopener">' + m + '</a>';
        });
        html = html.replace(/\b([\w.+-]+@vturnu\.com)\b/gi, function (m) {
            return '<a href="mailto:' + m + '">' + m + '</a>';
        });
        return html.replace(/\n/g, '<br>');
    }

    /* -------------------- build the widget -------------------- */

    var root = document.createElement('div');
    root.className = 'vee-root';
    root.innerHTML =
        '<button class="vee-launcher" type="button" aria-expanded="false" aria-label="Chat with ' + escapeHtml(cfg.name) + ', ' + escapeHtml(cfg.label) + '">' +
            '<span class="vee-dot" aria-hidden="true"></span>' +
            '<svg class="vee-chat-ic" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3C6.5 3 2 6.9 2 11.7c0 2.7 1.4 5.1 3.7 6.7L5 22l4-1.8c1 .2 2 .3 3 .3 5.5 0 10-3.9 10-8.8S17.5 3 12 3z"/></svg>' +
            '<svg class="vee-close-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>' +
        '</button>' +
        '<div class="vee-teaser" role="button" tabindex="0"><button class="vee-teaser-x" type="button" aria-label="Dismiss">&times;</button><span class="vee-teaser-text"></span></div>' +
        '<div class="vee-panel" role="dialog" aria-label="' + escapeHtml(cfg.name) + ', ' + escapeHtml(cfg.label) + '">' +
            '<div class="vee-head">' +
                '<span class="vee-avatar"><img src="' + escapeHtml(cfg.avatar) + '" alt=""></span>' +
                '<span class="vee-head-text">' +
                    '<span class="vee-head-name">' + escapeHtml(cfg.name) + ' <span class="vee-head-tag">' + escapeHtml(cfg.label) + '</span></span>' +
                    '<span class="vee-head-sub"><span class="vee-online-dot" aria-hidden="true"></span>Replies instantly</span>' +
                '</span>' +
                '<button class="vee-head-close" type="button" aria-label="Close chat">&times;</button>' +
            '</div>' +
            '<div class="vee-body" aria-live="polite"></div>' +
            '<div class="vee-foot">' +
                '<div class="vee-quick"></div>' +
                '<form class="vee-form">' +
                    '<label class="vee-honey">Leave this field empty<input type="text" name="hp" tabindex="-1" autocomplete="off"></label>' +
                    '<div class="vee-input-row">' +
                        '<textarea class="vee-input" rows="1" maxlength="1000" placeholder="Type a message..." aria-label="Message"></textarea>' +
                        '<button class="vee-send" type="submit" aria-label="Send" disabled>' +
                            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 11l18-8-8 18-2-8-8-2z"/></svg>' +
                        '</button>' +
                    '</div>' +
                '</form>' +
                '<p class="vee-foot-note">' + escapeHtml(cfg.label) + '. Chats are saved and shared with the VTurnU team so we can reply. <a href="' + escapeHtml(cfg.privacy) + '" target="_blank" rel="noopener">Privacy policy</a></p>' +
            '</div>' +
        '</div>';
    document.body.appendChild(root);

    var launcher = root.querySelector('.vee-launcher');
    var panel = root.querySelector('.vee-panel');
    var body = root.querySelector('.vee-body');
    var quick = root.querySelector('.vee-quick');
    var form = root.querySelector('.vee-form');
    var input = root.querySelector('.vee-input');
    var sendBtn = root.querySelector('.vee-send');
    var hp = root.querySelector('.vee-honey input');
    var teaser = root.querySelector('.vee-teaser');
    var teaserText = root.querySelector('.vee-teaser-text');

    /* -------------------- rendering ---------------------------- */

    function scrollDown() { body.scrollTop = body.scrollHeight; }

    function addRow(role, text, save) {
        var row = document.createElement('div');
        row.className = 'vee-row vee-' + role;
        var avatar = role === 'vee' ? '<span class="vee-bubble-avatar"><img src="' + escapeHtml(cfg.avatar) + '" alt=""></span>' : '';
        row.innerHTML = avatar + '<span class="vee-bubble">' + (role === 'system' ? escapeHtml(text) : linkify(text)) + '</span>';
        body.appendChild(row);
        scrollDown();
        if (save !== false && role !== 'system') {
            history.push({ role: role, text: text });
            if (history.length > 60) { history = history.slice(-60); }
            try { localStorage.setItem(HIST_KEY, JSON.stringify(history)); } catch (e) {}
        }
    }

    function showTyping() {
        var row = document.createElement('div');
        row.className = 'vee-typing';
        row.innerHTML = '<span class="vee-bubble-avatar"><img src="' + escapeHtml(cfg.avatar) + '" alt=""></span><span class="vee-bubble"><span></span><span></span><span></span></span>';
        body.appendChild(row);
        scrollDown();
        return row;
    }

    function restoreHistory() {
        if (!history.length) { return; }
        for (var i = 0; i < history.length; i++) {
            addRow(history[i].role, history[i].text, false);
        }
        greeted = true;
    }

    function greet() {
        if (greeted) { return; }
        greeted = true;
        addRow('vee', cfg.greeting);
    }

    /* -------------------- sending ------------------------------ */

    function attribution() {
        try { return localStorage.getItem('vt_src') || ''; } catch (e) { return ''; }
    }

    function timeZone() {
        try { return Intl.DateTimeFormat().resolvedOptions().timeZone || ''; } catch (e) { return ''; }
    }

    /* How long Vee "thinks" before a reply shows, so it reads like someone
       typing rather than a form submitting: roughly half a second plus a
       little per character, capped, minus whatever the request itself took. */
    function typingDelay(reply, elapsedMs) {
        var want = 500 + Math.min(reply.length, 160) * 15;
        return Math.max(250, Math.min(2800, want) - elapsedMs);
    }

    function sendBeaconEnd() {
        if (!sid || !navigator.sendBeacon) { return; }
        try {
            navigator.sendBeacon(cfg.api, new Blob([JSON.stringify({ action: 'end', sid: sid })], { type: 'application/json' }));
        } catch (e) {}
    }

    function notifyTeam(urgent) {
        if (!sid) { return; }
        var body = JSON.stringify({ action: 'notify', sid: sid, urgent: !!urgent });
        try {
            if (window.fetch) {
                fetch(cfg.api, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: body, keepalive: true, credentials: 'omit' });
            } else if (navigator.sendBeacon) {
                navigator.sendBeacon(cfg.api, new Blob([body], { type: 'application/json' }));
            }
        } catch (e) {}
    }

    function send(text) {
        if (!text || sending) { return; }
        sending = true;
        sendBtn.disabled = true;
        addRow('visitor', text);
        input.value = '';
        input.style.height = 'auto';
        var started = Date.now();
        var typingRow = showTyping();

        var payload = {
            sid: sid, text: text, page: location.pathname, title: document.title || '',
            attr: attribution(), tz: timeZone(), hp: hp.value,
        };

        fetch(cfg.api, {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload), credentials: 'omit',
        }).then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
          .then(function (res) {
            var elapsed = Date.now() - started;
            if (res && res.sid && res.sid !== sid) {
                sid = res.sid;
                try { localStorage.setItem(SID_KEY, sid); } catch (e) {}
            }
            var replies = (res && res.ok && res.replies && res.replies.length) ? res.replies
                : ['Sorry, something went wrong on our side. Please try again, or write to ' + cfg.email + '.'];
            var delay = res && res.ok ? typingDelay(replies[0], elapsed) : 300;
            setTimeout(function () {
                typingRow.remove();
                showNext(replies, 0);
            }, delay);
            if (res && res.notify) { notifyTeam(res.urgent === true); }
        }).catch(function () {
            typingRow.remove();
            addRow('vee', 'Sorry, I could not reach our server. Please try again, or write to ' + cfg.email + '.');
            sending = false;
            sendBtn.disabled = input.value.trim() === '';
        });
    }

    function showNext(replies, i) {
        addRow('vee', replies[i]);
        if (i + 1 < replies.length) {
            var row = showTyping();
            setTimeout(function () { row.remove(); showNext(replies, i + 1); }, 420 + Math.min(replies[i + 1].length, 140) * 8);
        } else {
            sending = false;
            sendBtn.disabled = input.value.trim() === '';
        }
    }

    /* -------------------- open / close -------------------------- */

    function openPanel() {
        opened = true;
        panel.classList.add('open');
        launcher.setAttribute('aria-expanded', 'true');
        launcher.removeAttribute('data-unread');
        hideTeaser(true);
        if (!history.length) { restoreHistory(); }
        if (!greeted) { greet(); }
        setTimeout(function () { input.focus(); }, 260);
    }
    function closePanel() {
        panel.classList.remove('open');
        launcher.setAttribute('aria-expanded', 'false');
    }
    function togglePanel() { panel.classList.contains('open') ? closePanel() : openPanel(); }

    launcher.addEventListener('click', togglePanel);
    root.querySelector('.vee-head-close').addEventListener('click', closePanel);

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        send(input.value.trim());
    });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit ? form.requestSubmit() : send(input.value.trim()); }
    });
    input.addEventListener('input', function () {
        sendBtn.disabled = sending || input.value.trim() === '';
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 92) + 'px';
    });

    /* Every Live Chat trigger already on the page (desktop quick actions,
       the mobile bar) opens this panel instead of tawk.to. */
    var triggers = document.querySelectorAll('.js-live-chat');
    for (var t = 0; t < triggers.length; t++) {
        triggers[t].addEventListener('click', function (e) { e.preventDefault(); openPanel(); });
    }

    /* -------------------- quick replies -------------------------- */

    var QUICK = (cfg.quickReplies && cfg.quickReplies.length) ? cfg.quickReplies
        : ['What does VTurnU do?', 'How much does it cost?', 'I want to talk to someone'];
    if (!history.length) {
        QUICK.forEach(function (q) {
            var b = document.createElement('button');
            b.type = 'button';
            b.textContent = q;
            b.addEventListener('click', function () { quick.hidden = true; send(q); });
            quick.appendChild(b);
        });
    } else {
        quick.hidden = true;
    }

    /* -------------------- the teaser bubble ----------------------- */

    function hideTeaser(remember) {
        teaser.classList.remove('on');
        if (remember) { try { sessionStorage.setItem(SEEN_KEY, '1'); } catch (e) {} }
    }
    teaser.addEventListener('click', function () { hideTeaser(true); openPanel(); });
    root.querySelector('.vee-teaser-x').addEventListener('click', function (e) { e.stopPropagation(); hideTeaser(true); });

    var seenTeaser = false;
    try { seenTeaser = sessionStorage.getItem(SEEN_KEY) === '1'; } catch (e) {}
    if (cfg.teaser && !seenTeaser && !history.length) {
        setTimeout(function () {
            if (opened) { return; }
            teaserText.textContent = cfg.teaser;
            teaser.classList.add('on');
        }, Math.max(2, cfg.teaserDelay || 25) * 1000);
    }

    window.addEventListener('pagehide', sendBeaconEnd);
})();
