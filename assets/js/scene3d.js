/* VTurnU inner-page hero scenes.
 *
 * A small hand-written 3D renderer on a 2D canvas: no library, so every page
 * gets real depth for a few kilobytes instead of the 590 KB WebGL bundle the
 * homepage uses. main.js loads this after the page is idle. Scenes follow the
 * pointer, pause when off screen, and draw one still frame for visitors who
 * prefer reduced motion. All colours come from the logo gradients.
 */
(function () {
    'use strict';
    var host = document.querySelector('.page-hero');
    var kind = document.body.getAttribute('data-scene');
    if (!host || !kind) { return; }

    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var small = window.innerWidth < 760;
    var canvas = document.createElement('canvas');
    canvas.className = 'hero3d';
    canvas.setAttribute('aria-hidden', 'true');
    host.insertBefore(canvas, host.firstChild);
    var ctx = canvas.getContext('2d');
    if (!ctx) { canvas.remove(); return; }

    var W = 0, H = 0, S = 1, CX = 0, CY = 0;
    var DPR = Math.min(window.devicePixelRatio || 1, 1.5);
    function size() {
        var r = canvas.getBoundingClientRect();
        W = r.width; H = r.height;
        canvas.width = Math.round(W * DPR);
        canvas.height = Math.round(H * DPR);
        ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
        S = Math.min(W, H) * 0.42;
        CX = W / 2; CY = H / 2;
    }

    /* Logo stops: V cyan to indigo, U pink to orange. */
    var V0 = [0, 184, 217], V1 = [67, 56, 202], U0 = [255, 61, 138], U1 = [255, 145, 0];
    function mix(a, b, t) {
        return [a[0] + (b[0] - a[0]) * t, a[1] + (b[1] - a[1]) * t, a[2] + (b[2] - a[2]) * t];
    }
    function rgba(c, a) {
        return 'rgba(' + (c[0] | 0) + ',' + (c[1] | 0) + ',' + (c[2] | 0) + ',' + a.toFixed(3) + ')';
    }
    /* t in 0..1 runs the whole brand ramp: cyan, indigo, pink, orange. */
    function brand(t) {
        t = Math.max(0, Math.min(1, t));
        if (t < 0.45) { return mix(V0, V1, t / 0.45); }
        if (t < 0.55) { return mix(V1, U0, (t - 0.45) / 0.1); }
        return mix(U0, U1, (t - 0.55) / 0.45);
    }

    var rotY = 0.5, rotX = -0.32, targetY = 0, targetX = 0, spin = 0, time = 0;
    function project(x, y, z) {
        var cy = Math.cos(rotY), sy = Math.sin(rotY), cx = Math.cos(rotX), sx = Math.sin(rotX);
        var x1 = x * cy + z * sy, z1 = -x * sy + z * cy;
        var y1 = y * cx - z1 * sx, z2 = y * sx + z1 * cx;
        var p = 3.4 / (3.4 - z2);
        return { x: CX + x1 * S * p, y: CY - y1 * S * p, z: z2, p: p };
    }
    function dot(p, r, c, a) {
        var size = Math.max(0.4, r * p.p);
        ctx.fillStyle = rgba(c, a);
        // Tiny points read the same as squares and draw far faster than arcs.
        if (size < 1.7) {
            ctx.fillRect(p.x - size, p.y - size, size * 2, size * 2);
            return;
        }
        ctx.beginPath();
        ctx.arc(p.x, p.y, size, 0, 6.283);
        ctx.fill();
    }
    function line(a, b, c, alpha, w) {
        ctx.strokeStyle = rgba(c, alpha);
        ctx.lineWidth = w || 1;
        ctx.beginPath();
        ctx.moveTo(a.x, a.y);
        ctx.lineTo(b.x, b.y);
        ctx.stroke();
    }
    function poly(pts, fill) {
        ctx.fillStyle = fill;
        ctx.beginPath();
        ctx.moveTo(pts[0].x, pts[0].y);
        for (var i = 1; i < pts.length; i++) { ctx.lineTo(pts[i].x, pts[i].y); }
        ctx.closePath();
        ctx.fill();
    }
    function sphere(n) {
        var out = [], g = Math.PI * (3 - Math.sqrt(5));
        for (var i = 0; i < n; i++) {
            var y = 1 - (i / (n - 1)) * 2, r = Math.sqrt(1 - y * y), a = i * g;
            out.push([Math.cos(a) * r, y, Math.sin(a) * r]);
        }
        return out;
    }

    /* Globe: where AI answers and buyers meet. */
    function sceneGlobe() {
        var pts = sphere(small ? 240 : 520), pins = [40, 150, 230, 330, 420].map(function (i) { return pts[i % pts.length]; });
        return function () {
            for (var i = 0; i < pts.length; i++) {
                var q = pts[i], p = project(q[0], q[1], q[2]);
                var f = (p.z + 1) / 2;
                dot(p, 1 + f * 1.6, brand((q[1] + 1) / 2), 0.1 + f * 0.75);
            }
            pins.forEach(function (q, i) {
                var p = project(q[0], q[1], q[2]);
                if (p.z < 0) { return; }
                var pulse = (time * 0.6 + i * 0.37) % 1;
                ctx.strokeStyle = rgba(brand(i / 4), (1 - pulse) * 0.8);
                ctx.lineWidth = 1.6;
                ctx.beginPath();
                ctx.arc(p.x, p.y, 3 + pulse * 18, 0, 6.283);
                ctx.stroke();
                dot(p, 3.4, brand(i / 4), 1);
            });
        };
    }

    /* Funnel: attention narrowing into pipeline. */
    function sceneFunnel() {
        var rings = 13, per = small ? 26 : 42, drops = [];
        for (var d = 0; d < (small ? 16 : 34); d++) { drops.push({ a: Math.random() * 6.28, t: Math.random() }); }
        return function () {
            for (var r = 0; r < rings; r++) {
                var u = r / (rings - 1), y = 1 - u * 2, rad = 0.2 + Math.pow(1 - u, 1.5) * 1.02;
                var prev = null, first = null;
                for (var i = 0; i <= per; i++) {
                    var a = (i / per) * 6.283 + spin * (0.6 + u);
                    var p = project(Math.cos(a) * rad, y, Math.sin(a) * rad);
                    if (prev) { line(prev, p, brand(u), 0.14 + ((p.z + 1.2) / 2.4) * 0.5, 1.2); } else { first = p; }
                    prev = p;
                }
                if (first) { line(prev, first, brand(u), 0.2, 1); }
            }
            drops.forEach(function (d) {
                var t = (d.t + time * 0.12) % 1, y = 1.1 - t * 2.3, rad = (0.15 + Math.pow(1 - t, 1.5) * 0.9) * 0.9;
                var a = d.a + t * 7 + spin;
                dot(project(Math.cos(a) * rad, y, Math.sin(a) * rad), 2.6, brand(t), 0.95);
            });
        };
    }

    /* Bars: a growth chart with real volume. */
    function sceneBars() {
        var n = 5, g = 0.42, w = 0.3;
        function box(x, z, h, t) {
            var b = -0.85, xa = x - w / 2, xb = x + w / 2, za = z - w / 2, zb = z + w / 2, top = b + h;
            var A = project(xa, top, za), B = project(xb, top, za), C = project(xb, top, zb), D = project(xa, top, zb);
            var E = project(xa, b, za), F = project(xb, b, za), G = project(xb, b, zb), H2 = project(xa, b, zb);
            var c = brand(t), dk = mix(c, [29, 25, 54], 0.32), dk2 = mix(c, [29, 25, 54], 0.14);
            var faces = [[A, B, F, E, dk], [B, C, G, F, dk2], [C, D, H2, G, dk], [D, A, E, H2, dk2]];
            faces.sort(function (p, q) {
                return (p[0].z + p[1].z + p[2].z) - (q[0].z + q[1].z + q[2].z);
            });
            faces.forEach(function (f) { poly(f.slice(0, 4), rgba(f[4], 0.93)); });
            poly([A, B, C, D], rgba(mix(c, [255, 255, 255], 0.28), 0.98));
        }
        return function () {
            var list = [];
            for (var i = 0; i < n; i++) {
                for (var j = 0; j < n; j++) {
                    var x = (i - 2) * g, z = (j - 2) * g, grow = (i + (4 - j)) / 8;
                    var h = 0.18 + grow * 1.45 + Math.sin(time * 1.5 + i * 0.7 + j * 0.5) * 0.09;
                    list.push({ x: x, z: z, h: h, t: grow, d: project(x, 0, z).z });
                }
            }
            list.sort(function (a, b) { return a.d - b.d; });
            list.forEach(function (b) { box(b.x, b.z, b.h, b.t); });
        };
    }

    /* Cube: a website as a built object, panels inside a frame. */
    function sceneCube() {
        var c = [[-1, -1, -1], [1, -1, -1], [1, 1, -1], [-1, 1, -1], [-1, -1, 1], [1, -1, 1], [1, 1, 1], [-1, 1, 1]];
        var e = [[0, 1], [1, 2], [2, 3], [3, 0], [4, 5], [5, 6], [6, 7], [7, 4], [0, 4], [1, 5], [2, 6], [3, 7]];
        return function () {
            [1.0, 0.68, 0.36].forEach(function (s, k) {
                var turn = k % 2 ? -spin * 1.3 : spin * 0.7, co = Math.cos(turn), si = Math.sin(turn);
                var pr = c.map(function (q) {
                    var x = q[0] * co - q[2] * si, z = q[0] * si + q[2] * co;
                    return project(x * s, q[1] * s, z * s);
                });
                e.forEach(function (ed) { line(pr[ed[0]], pr[ed[1]], brand(k / 2 * 0.9 + 0.05), 0.72, 2.2 - k * 0.5); });
                pr.forEach(function (p) { dot(p, 3.2 - k * 0.7, brand(k / 2), 0.95); });
                if (k === 1) {
                    poly([pr[4], pr[5], pr[6], pr[7]], 'rgba(255,61,138,.09)');
                    poly([pr[0], pr[1], pr[2], pr[3]], 'rgba(0,184,217,.1)');
                }
            });
        };
    }

    /* Orbit: enterprise systems moving around one core. */
    function sceneOrbit() {
        var rings = [{ r: 1.02, tx: 0.5, tz: 0.2 }, { r: 0.8, tx: -0.8, tz: 0.6 }, { r: 1.2, tx: 1.15, tz: -0.4 }];
        var core = sphere(small ? 90 : 170);
        function orient(x, y, z, ring) {
            var cx = Math.cos(ring.tx), sx = Math.sin(ring.tx), cz = Math.cos(ring.tz), sz = Math.sin(ring.tz);
            var y1 = y * cx - z * sx, z1 = y * sx + z * cx;
            return [x * cz - y1 * sz, x * sz + y1 * cz, z1];
        }
        return function () {
            core.forEach(function (q) {
                var p = project(q[0] * 0.42, q[1] * 0.42, q[2] * 0.42), f = (p.z + 0.5);
                dot(p, 1.5 + f * 1.2, brand((q[1] + 1) / 2), 0.2 + f * 0.6);
            });
            rings.forEach(function (ring, k) {
                var prev = null, steps = small ? 48 : 80;
                for (var i = 0; i <= steps; i++) {
                    var a = (i / steps) * 6.283;
                    var o = orient(Math.cos(a) * ring.r, 0, Math.sin(a) * ring.r, ring);
                    var p = project(o[0], o[1], o[2]);
                    if (prev) { line(prev, p, brand(0.15 + k * 0.33), 0.2 + (p.z + 1.2) * 0.22, 1.4); }
                    prev = p;
                }
                for (var m = 0; m < 3; m++) {
                    var b = time * (0.5 - k * 0.1) + (m / 3) * 6.283 + k;
                    var o2 = orient(Math.cos(b) * ring.r, 0, Math.sin(b) * ring.r, ring);
                    dot(project(o2[0], o2[1], o2[2]), 5.2 - k * 0.5, brand(0.15 + k * 0.33 + m * 0.08), 1);
                }
            });
        };
    }

    /* Nodes: audiences and ideas connecting. */
    function sceneNodes() {
        var pts = sphere(small ? 34 : 64).map(function (q, i) {
            var r = 0.72 + (i % 5) * 0.09;
            return [q[0] * r, q[1] * r, q[2] * r];
        });
        var links = [];
        pts.forEach(function (a, i) {
            pts.forEach(function (b, j) {
                if (j <= i) { return; }
                var d = Math.pow(a[0] - b[0], 2) + Math.pow(a[1] - b[1], 2) + Math.pow(a[2] - b[2], 2);
                if (d < 0.2) { links.push([i, j]); }
            });
        });
        return function () {
            var pr = pts.map(function (q) { return project(q[0], q[1], q[2]); });
            links.forEach(function (l) {
                var a = pr[l[0]], b = pr[l[1]], f = (a.z + b.z + 2) / 4;
                line(a, b, brand(l[0] / pts.length), 0.08 + f * 0.42, 1.1);
            });
            pr.forEach(function (p, i) {
                var f = (p.z + 1) / 2, beat = 1 + Math.sin(time * 2 + i) * 0.25;
                dot(p, (2 + (i % 3) * 1.6) * beat, brand(i / pts.length), 0.3 + f * 0.7);
            });
        };
    }

    /* Book: open pages turning. */
    function sceneBook() {
        var sheets = 9;
        function page(angle, t, a) {
            var w = 1.05, h = 0.74, ca = Math.cos(angle), sa = Math.sin(angle);
            var A = project(0, -h, 0), B = project(0, h, 0), C = project(ca * w, h, sa * w), D = project(ca * w, -h, sa * w);
            poly([A, B, C, D], rgba(mix(brand(t), [255, 255, 255], 0.28), a));
            line(B, C, brand(t), 1, 1.6);
            line(C, D, brand(t), 1, 1.6);
            line(D, A, brand(t), 1, 1.6);
            for (var i = 1; i < 5; i++) {
                var v = -h + (i / 5) * 2 * h;
                line(project(ca * 0.15, v, sa * 0.15), project(ca * (w - 0.15), v, sa * (w - 0.15)), [255, 255, 255], 0.55, 1.4);
            }
        }
        return function () {
            var list = [];
            for (var i = 0; i < sheets; i++) {
                var phase = ((time * 0.16 + i / sheets) % 1);
                list.push({ a: 0.14 + phase * (Math.PI - 0.28), t: i / (sheets - 1) });
            }
            list.sort(function (x, y) { return Math.abs(y.a - Math.PI / 2) - Math.abs(x.a - Math.PI / 2); });
            list.forEach(function (l) { page(l.a, l.t, 0.86); });
        };
    }

    /* Rings: a torus of points, one system with many parts. */
    function sceneRings() {
        var pts = [], R = 0.86, r = 0.36, a = small ? 46 : 80, b = small ? 12 : 18;
        for (var i = 0; i < a; i++) {
            for (var j = 0; j < b; j++) {
                var u = (i / a) * 6.283, v = (j / b) * 6.283;
                pts.push([(R + r * Math.cos(v)) * Math.cos(u), r * Math.sin(v), (R + r * Math.cos(v)) * Math.sin(u), i / a]);
            }
        }
        return function () {
            pts.forEach(function (q, k) {
                var w = 1 + Math.sin(time * 1.4 + q[3] * 12.56) * 0.055;
                var p = project(q[0] * w, q[1] * w, q[2] * w), f = (p.z + 1.2) / 2.4;
                dot(p, 1 + f * 1.8, brand(q[3]), 0.12 + f * 0.8);
            });
        };
    }

    var build = { globe: sceneGlobe, funnel: sceneFunnel, bars: sceneBars, cube: sceneCube, orbit: sceneOrbit, nodes: sceneNodes, book: sceneBook, rings: sceneRings }[kind] || sceneNodes;
    var draw = build();

    var running = false, last = 0;
    function frame(now) {
        if (!running) { return; }
        requestAnimationFrame(frame);
        if (now - last < 32) { return; }   // About 30 fps: smooth, and gentle on phones.
        var dt = Math.min((now - last) / 1000, 0.1);
        last = now;
        time += dt;
        spin += dt * 0.25;
        rotY += (0.5 + targetY - rotY) * 0.06 + (kind === 'bars' || kind === 'book' ? 0 : dt * 0);
        rotX += (-0.32 + targetX - rotX) * 0.06;
        if (kind === 'globe' || kind === 'nodes' || kind === 'rings' || kind === 'orbit') { targetY += dt * 0.16; }
        ctx.clearRect(0, 0, W, H);
        draw();
    }
    function start() { if (!running) { running = true; last = performance.now(); requestAnimationFrame(frame); } }
    function stop() { running = false; }

    size();
    window.addEventListener('resize', function () { size(); if (reduce) { ctx.clearRect(0, 0, W, H); draw(); } }, { passive: true });

    if (reduce) {
        time = 1;
        draw();
        canvas.classList.add('is-on');
        return;
    }

    host.addEventListener('pointermove', function (e) {
        if (e.pointerType === 'touch') { return; }
        var r = host.getBoundingClientRect();
        targetX = ((e.clientY - r.top) / r.height - 0.5) * -0.5;
        var nudge = ((e.clientX - r.left) / r.width - 0.5) * 0.9;
        if (kind === 'bars' || kind === 'book' || kind === 'funnel' || kind === 'cube') { targetY = nudge; }
    }, { passive: true });

    /* A finished still frame paints straight away. Motion waits for the first
       sign of a real person, or nine seconds, so the animation never competes
       with loading on a slow phone. */
    time = 1;
    ctx.clearRect(0, 0, W, H);
    draw();
    requestAnimationFrame(function () { canvas.classList.add('is-on'); });

    var armed = false, onScreen = true;
    function arm() {
        if (armed) { return; }
        armed = true;
        ['pointermove', 'pointerdown', 'scroll', 'keydown', 'touchstart'].forEach(function (e) { window.removeEventListener(e, arm); });
        if (onScreen && !document.hidden) { start(); }
    }
    ['pointermove', 'pointerdown', 'scroll', 'keydown', 'touchstart'].forEach(function (e) { window.addEventListener(e, arm, { passive: true }); });
    setTimeout(arm, 9000);

    if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (en) {
            onScreen = en[0].isIntersecting;
            if (!armed) { return; }
            if (onScreen) { start(); } else { stop(); }
        }).observe(host);
    }
    document.addEventListener('visibilitychange', function () {
        if (!armed) { return; }
        if (document.hidden) { stop(); } else if (onScreen) { start(); }
    });
})();
