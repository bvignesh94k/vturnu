/* "Together we turn heads", as a room you can actually turn.
 *
 * A crowd of plaster busts looks away from the visitor. Heads the pointer reaches
 * turn toward it and fill with the logo gradient. Hovering the main button turns
 * the whole room to look at it. Plain WebGL2 with instanced drawing: the head is
 * generated here from a profile curve, so there is no model file and no library.
 *
 * The page shows a prerendered poster of this scene first, so this file is
 * decoration on top of finished content. It loads after the first interaction.
 */
(function () {
    'use strict';
    var canvas = document.getElementById('tt-crowd');
    var stage = document.getElementById('tt-hero');
    if (!canvas || !stage) { return; }
    var POSTER = /[?&]heroposter=1/.test(location.search);
    var gl = canvas.getContext('webgl2', { antialias: true, alpha: false, powerPreference: 'low-power', preserveDrawingBuffer: POSTER });
    if (!gl) { stage.classList.add('no-crowd'); return; }

    var small = window.innerWidth < 760;
    var BG = [0.953, 0.937, 0.906];

    /* ------------------------------------------------------- the head ------ */

    /* Side profile: [height, half width, half depth], crown to shoulders. */
    var PROFILE = [
        [1.00, 0.00, 0.00], [0.98, 0.19, 0.23], [0.91, 0.36, 0.45], [0.77, 0.485, 0.60], [0.57, 0.545, 0.68],
        [0.33, 0.555, 0.69], [0.10, 0.54, 0.65], [-0.11, 0.515, 0.58], [-0.28, 0.47, 0.50], [-0.41, 0.40, 0.43],
        [-0.51, 0.365, 0.37], [-0.63, 0.355, 0.35], [-0.80, 0.37, 0.36], [-0.96, 0.45, 0.38], [-1.10, 0.72, 0.43],
        [-1.26, 1.02, 0.49], [-1.52, 1.18, 0.55], [-1.95, 1.27, 0.60], [-2.60, 1.31, 0.62]
    ];
    function catmull(p0, p1, p2, p3, t) {
        var t2 = t * t, t3 = t2 * t;
        return 0.5 * ((2 * p1) + (-p0 + p2) * t + (2 * p0 - 5 * p1 + 4 * p2 - p3) * t2 + (-p0 + 3 * p1 - 3 * p2 + p3) * t3);
    }
    function profileAt(s) {
        var n = PROFILE.length - 1, f = Math.min(s * n, n - 1e-4), i = Math.floor(f), t = f - i;
        var a = PROFILE[Math.max(0, i - 1)], b = PROFILE[i], c = PROFILE[i + 1], d = PROFILE[Math.min(n, i + 2)];
        return [catmull(a[0], b[0], c[0], d[0], t), Math.max(0, catmull(a[1], b[1], c[1], d[1], t)), Math.max(0, catmull(a[2], b[2], c[2], d[2], t))];
    }
    function gauss(x, c, s) { var d = (x - c) / s; return Math.exp(-d * d); }

    /* One point on the bust, with the face sculpted onto the front. */
    function surface(s, ang) {
        var p = profileAt(s), y = p[0], cx = Math.cos(ang), sz = Math.sin(ang);
        var x = p[1] * cx, z = p[2] * sz;
        if (z < 0 && y > -0.5) { z *= 1 + 0.12 * gauss(y, 0.5, 0.55); }    // back of the skull
        z += 0.15 * gauss(y, -0.33, 0.27) - 0.13 * gauss(y, -0.74, 0.2);   // jaw forward, neck behind it
        var head = y > -0.62 ? 1 : 0, front = Math.max(0, sz);
        if (head) {
            /* A classical face, kept simple: brow, nose, closed eyes, mouth, chin.
               Stylised on purpose, because a near-real face in a crowd looks unsettling. */
            var f = Math.pow(front, 1.1), ax = Math.abs(x), run = Math.max(0, Math.min(1, (0.34 - y) / 0.47));
            var bridge = gauss(x, 0, 0.095 + 0.05 * run) * Math.min(1, Math.max(0, (y + 0.15) / 0.045));
            z += f * bridge * (0.03 + 0.19 * run);                                         // nose
            z += f * 0.04 * gauss(y, 0.37, 0.1) * gauss(x, 0, 0.44);                       // brow
            z -= f * 0.055 * gauss(y, 0.21, 0.07) * gauss(ax, 0.25, 0.13);                 // eye hollows
            z += f * 0.024 * gauss(y, 0.2, 0.03) * gauss(ax, 0.25, 0.08);                  // closed lids
            z -= f * 0.022 * gauss(y, -0.2, 0.03) * gauss(x, 0, 0.13);                     // under the nose
            z += f * 0.02 * gauss(y, -0.27, 0.026) * gauss(x, 0, 0.12);                    // upper lip
            z -= f * 0.006 * gauss(y, -0.305, 0.016) * gauss(x, 0, 0.1);                   // the mouth line
            z += f * 0.018 * gauss(y, -0.335, 0.024) * gauss(x, 0, 0.1);                   // lower lip
            z += f * 0.04 * gauss(y, -0.44, 0.05) * gauss(x, 0, 0.16);                     // chin
            x += (cx > 0 ? 1 : -1) * 0.05 * gauss(y, 0.12, 0.12) * gauss(sz, -0.1, 0.16);  // ears
        }
        return [x, y, z];
    }
    function buildHead(rows, cols) {
        var pos = [], nor = [], ao = [], idx = [], e = 0.0012;
        for (var r = 0; r <= rows; r++) {
            /* Rows bunch up on the head, where the detail is. */
            var s = Math.pow(r / rows, 1.32);
            for (var c = 0; c <= cols; c++) {
                var a = (c / cols) * Math.PI * 2 + Math.PI / 2;
                var s0 = Math.min(s, 1 - e), P = surface(s, a), A = surface(s0 + e, a), B = surface(s, a + e * 2), O = surface(s0, a);
                var ux = A[0] - O[0], uy = A[1] - O[1], uz = A[2] - O[2], vx = B[0] - P[0], vy = B[1] - P[1], vz = B[2] - P[2];
                var nx = vy * uz - vz * uy, ny = vz * ux - vx * uz, nz = vx * uy - vy * ux;
                var l = Math.sqrt(nx * nx + ny * ny + nz * nz) || 1;
                nx /= l; ny /= l; nz /= l;
                if (r === 0) { nx = 0; ny = 1; nz = 0; }
                pos.push(P[0], P[1], P[2]);
                nor.push(nx, ny, nz);
                /* Painted-in occlusion: under the chin, the neck, the eye sockets. */
                var y = P[1], o = 1;
                o -= 0.4 * gauss(y, -0.56, 0.1);
                o -= 0.1 * gauss(y, -0.86, 0.24);
                o -= 0.2 * gauss(y, 0.21, 0.07) * (gauss(P[0], 0.25, 0.13) + gauss(P[0], -0.25, 0.13)) * (P[2] > 0 ? 1 : 0);
                ao.push(o);
            }
        }
        var w = cols + 1;
        for (var i = 0; i < rows; i++) {
            for (var j = 0; j < cols; j++) {
                var q = i * w + j;
                idx.push(q, q + 1, q + w, q + 1, q + w + 1, q + w);
            }
        }
        return { pos: new Float32Array(pos), nor: new Float32Array(nor), ao: new Float32Array(ao), idx: new Uint32Array(idx) };
    }

    /* ------------------------------------------------------------ shaders --- */

    var VS = '#version 300 es\n' +
    'layout(location=0) in vec3 aPos; layout(location=1) in vec3 aNor; layout(location=2) in float aAo;\n' +
    'layout(location=3) in vec4 iOff; layout(location=4) in vec4 iRot;\n' +
    'uniform mat4 uVP; uniform vec3 uEye;\n' +
    'out vec3 vN; out vec3 vW; out float vAo; out float vY; flat out vec2 vI; out float vZ;\n' +
    'vec3 turn(vec3 p, float yaw, float pitch){\n' +
    '  float c=cos(pitch), s=sin(pitch); p=vec3(p.x, p.y*c-p.z*s, p.y*s+p.z*c);\n' +
    '  c=cos(yaw); s=sin(yaw); return vec3(p.x*c+p.z*s, p.y, -p.x*s+p.z*c);\n' +
    '}\n' +
    'void main(){\n' +
    '  float w=smoothstep(-0.98,-0.46,aPos.y), yaw=iRot.x*mix(0.45,1.0,w);\n' +     // the bust follows the head a little
    '  vec3 p=turn(aPos, yaw, iRot.y*w), n=turn(aNor, yaw, iRot.y*w);\n' +
    '  vec3 world=p*iOff.w+iOff.xyz;\n' +
    '  vN=n; vW=world; vAo=aAo; vY=aPos.y; vI=iRot.zw; vZ=iOff.z;\n' +
    '  gl_Position=uVP*vec4(world,1.0);\n' +
    '}\n';

    var FS = '#version 300 es\nprecision highp float;\n' +
    'in vec3 vN; in vec3 vW; in float vAo; in float vY; flat in vec2 vI; in float vZ;\n' +
    'uniform vec3 uEye; uniform vec3 uBg; out vec4 o;\n' +
    'void main(){\n' +
    '  vec3 N=normalize(vN), V=normalize(uEye-vW), L=normalize(vec3(-0.55,0.72,0.62));\n' +
    '  float nl=dot(N,L), wrap=clamp((nl+0.42)/1.42,0.0,1.0), diff=wrap*wrap*(3.0-2.0*wrap);\n' +
    '  float sky=0.5+0.5*N.y, fres=pow(1.0-max(dot(N,V),0.0),3.0);\n' +
    '  vec3 H=normalize(L+V); float spec=pow(max(dot(N,H),0.0),30.0);\n' +
    /* Plaster: warm matte, cool in the shadow, the way real casts look. */
    '  vec3 plaster=mix(vec3(0.60,0.585,0.63),vec3(0.985,0.965,0.925),diff);\n' +
    '  plaster=mix(plaster,plaster*vec3(1.02,1.0,0.965),sky)*mix(0.74,1.0,vAo);\n' +
    '  plaster+=fres*0.055;\n' +
    /* The logo gradients, in satin. */
    '  float t=clamp((1.05-vY)/2.0,0.0,1.0);\n' +
    '  vec3 v=mix(vec3(0.0,0.72,0.85),vec3(0.263,0.22,0.792),t), u=mix(vec3(1.0,0.239,0.541),vec3(1.0,0.569,0.0),t);\n' +
    '  vec3 g=vI.y<0.5?v:u;\n' +
    '  vec3 satin=g*(0.5+0.66*diff)*mix(0.5,1.0,vAo)+vec3(1.0)*spec*0.2+g*fres*0.34;\n' +
    /* Colour rises through the bust as the head turns. */
    '  float edge=mix(-2.3,1.5,vI.x), fill=smoothstep(edge+0.16,edge-0.16,vY);\n' +
    '  vec3 c=mix(plaster,satin,fill);\n' +
    /* Depth: lower bodies dissolve into the room, rows fade with distance. */
    '  float mist=smoothstep(-1.15,-2.15,vY), far=smoothstep(4.0,-20.0,vZ);\n' +
    '  c=mix(c,uBg,clamp(mist*0.96,0.0,1.0));\n' +
    '  c=mix(c,uBg,far*far*0.9);\n' +
    '  o=vec4(c,1.0);\n' +
    '}\n';

    function compile(type, src) {
        var s = gl.createShader(type);
        gl.shaderSource(s, src);
        gl.compileShader(s);
        if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) { throw new Error(gl.getShaderInfoLog(s)); }
        return s;
    }
    var prog = gl.createProgram();
    gl.attachShader(prog, compile(gl.VERTEX_SHADER, VS));
    gl.attachShader(prog, compile(gl.FRAGMENT_SHADER, FS));
    gl.linkProgram(prog);
    if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) { throw new Error(gl.getProgramInfoLog(prog)); }
    gl.useProgram(prog);
    var uVP = gl.getUniformLocation(prog, 'uVP'), uEye = gl.getUniformLocation(prog, 'uEye'), uBg = gl.getUniformLocation(prog, 'uBg');
    gl.uniform3fv(uBg, BG);

    var mesh = small || !window.matchMedia('(pointer: fine)').matches ? buildHead(84, 56) : buildHead(132, 88);
    function attr(loc, data, size) {
        gl.bindBuffer(gl.ARRAY_BUFFER, gl.createBuffer());
        gl.bufferData(gl.ARRAY_BUFFER, data, gl.STATIC_DRAW);
        gl.enableVertexAttribArray(loc);
        gl.vertexAttribPointer(loc, size, gl.FLOAT, false, 0, 0);
    }
    gl.bindVertexArray(gl.createVertexArray());
    attr(0, mesh.pos, 3);
    attr(1, mesh.nor, 3);
    attr(2, mesh.ao, 1);
    gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, gl.createBuffer());
    gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, mesh.idx, gl.STATIC_DRAW);
    var offBuf = gl.createBuffer(), rotBuf = gl.createBuffer();
    gl.enable(gl.DEPTH_TEST);
    gl.enable(gl.CULL_FACE);
    gl.clearColor(BG[0], BG[1], BG[2], 1);

    /* -------------------------------------------------------------- camera -- */

    var eye = [0, 2.4, 19], look = [0, 0.3, 0], W = 1, H = 1, aspect = 1, VP = new Float32Array(16);
    function cross(a, b) { return [a[1] * b[2] - a[2] * b[1], a[2] * b[0] - a[0] * b[2], a[0] * b[1] - a[1] * b[0]]; }
    function norm(a) { var l = Math.hypot(a[0], a[1], a[2]) || 1; return [a[0] / l, a[1] / l, a[2] / l]; }
    function sub3(a, b) { return [a[0] - b[0], a[1] - b[1], a[2] - b[2]]; }
    function dot3(a, b) { return a[0] * b[0] + a[1] * b[1] + a[2] * b[2]; }
    function camera() {
        var f = norm(sub3(look, eye)), r = norm(cross(f, [0, 1, 0])), u = cross(r, f);
        var fov = (aspect < 1 ? 30 : 22) * Math.PI / 180, t = 1 / Math.tan(fov / 2), n = 0.5, fr = 80;
        var P = [t / aspect, 0, 0, 0, 0, t, 0, 0, 0, 0, (fr + n) / (n - fr), -1, 0, 0, 2 * fr * n / (n - fr), 0];
        var V = [r[0], u[0], -f[0], 0, r[1], u[1], -f[1], 0, r[2], u[2], -f[2], 0, -dot3(r, eye), -dot3(u, eye), dot3(f, eye), 1];
        for (var c = 0; c < 4; c++) {
            for (var i = 0; i < 4; i++) {
                VP[c * 4 + i] = 0;
                for (var k = 0; k < 4; k++) { VP[c * 4 + i] += P[k * 4 + i] * V[c * 4 + k]; }
            }
        }
        gl.uniformMatrix4fv(uVP, false, VP);
        gl.uniform3fv(uEye, eye);
        cam = { r: r, u: u, f: f, t: t };
    }
    var cam = null;
    function project(x, y, z) {
        var cx = VP[0] * x + VP[4] * y + VP[8] * z + VP[12], cy = VP[1] * x + VP[5] * y + VP[9] * z + VP[13], cw = VP[3] * x + VP[7] * y + VP[11] * z + VP[15];
        return [cx / cw, cy / cw];
    }
    /* Where a screen point lands on the plane just in front of the crowd. */
    function unproject(nx, ny, planeZ) {
        var d = norm([
            cam.f[0] + (cam.r[0] * nx * aspect + cam.u[0] * ny) / cam.t,
            cam.f[1] + (cam.r[1] * nx * aspect + cam.u[1] * ny) / cam.t,
            cam.f[2] + (cam.r[2] * nx * aspect + cam.u[2] * ny) / cam.t
        ]);
        var s = (planeZ - eye[2]) / d[2];
        return [eye[0] + d[0] * s, eye[1] + d[1] * s, planeZ];
    }

    /* --------------------------------------------------------------- crowd -- */

    var heads = [], rotData = null, offData = null;
    function seed(i) { var s = Math.sin(i * 127.1 + 3.7) * 43758.5453; return s - Math.floor(s); }

    /* Everything a head must stay clear of, in canvas pixels. Measuring the real
       text means the room rearranges itself around the copy on any screen. */
    function keepOut() {
        var c = canvas.getBoundingClientRect(), out = [], PAD = 14;
        Array.prototype.forEach.call(stage.querySelectorAll('.tt-h1, .tt-lead, .tt-actions > *'), function (el) {
            var range = document.createRange();
            range.selectNodeContents(el);
            var list = el.matches('.tt-h1, .tt-lead') ? range.getClientRects() : [el.getBoundingClientRect()];
            Array.prototype.forEach.call(list, function (b) {
                out.push([-1e4, el.matches('.tt-h1') ? -1e4 : b.top - c.top - PAD, b.right - c.left + PAD, b.bottom - c.top + PAD]);
            });
        });
        return out;
    }

    function layout() {
        heads = [];
        var portrait = aspect < 1, id = 0, boxes = keepOut();
        var cssW = canvas.getBoundingClientRect().width, cssH = canvas.getBoundingClientRect().height;
        /* Raked seating: each row sits just above the crowns of the row in front. */
        var ny = -0.94;
        for (var r = 0; r < (portrait ? 8 : 12) && ny < 1.1; r++) {
            var z = (portrait ? -3 : 6) - r * 2.6, dist = eye[2] - z, gap = 2.62, rowY = unproject(0, ny, z)[1];
            ny += (1.5 * cam.t / dist) * (portrait ? 0.62 : 0.56);
            for (var c = -14; c <= 14; c++) {
                id++;
                var x = (c + (r % 2 ? 0.5 : 0)) * gap + (seed(id) - 0.5) * 0.46;
                var yy = rowY + (seed(id + 9) - 0.5) * 0.24, zz = z + (seed(id + 17) - 0.5) * 0.6;
                var p = project(x, yy, zz);
                if (Math.abs(p[0]) > 1.14) { continue; }
                /* Skip any seat that would sit behind the words. */
                var scale = cam.t / dist, px = (p[0] + 1) * cssW / 2, py = (1 - p[1]) * cssH / 2;
                var hw = 0.72 * scale / aspect * cssW / 2, top = 1.05 * scale * cssH / 2, bot = 0.5 * scale * cssH / 2, hit = false;
                for (var k = 0; k < boxes.length && !hit; k++) {
                    var q = boxes[k];
                    hit = px + hw > q[0] && px - hw < q[2] && py + bot > q[1] && py - top < q[3];
                }
                if (hit) { continue; }
                var turned = seed(id + 31) > 0.84;
                heads.push({
                    x: x, y: yy, z: zz, s: 1 + (seed(id + 5) - 0.5) * 0.09, sx: p[0], sy: p[1],
                    away: (seed(id + 67) > 0.5 ? 1 : -1) * (1.32 + (seed(id + 41) - 0.5) * 0.42), tilt: (seed(id + 47) - 0.5) * 0.14,
                    yaw: 0, pitch: 0, vy: 0, vp: 0, a: turned ? 1 : 0, lit: turned ? 1e9 : 0,
                    pal: seed(id + 53) > 0.5 ? 1 : 0, phase: seed(id + 61) * 6.283
                });
            }
        }
        heads.sort(function (a, b) { return a.z - b.z; });
        offData = new Float32Array(heads.length * 4);
        rotData = new Float32Array(heads.length * 4);
        heads.forEach(function (h, i) {
            offData.set([h.x, h.y, h.z, h.s], i * 4);
            h.yaw = h.a > 0.5 ? -0.5 : h.away;
            h.pitch = h.tilt;
        });
        gl.bindBuffer(gl.ARRAY_BUFFER, offBuf);
        gl.bufferData(gl.ARRAY_BUFFER, offData, gl.STATIC_DRAW);
        gl.enableVertexAttribArray(3);
        gl.vertexAttribPointer(3, 4, gl.FLOAT, false, 0, 0);
        gl.vertexAttribDivisor(3, 1);
        gl.bindBuffer(gl.ARRAY_BUFFER, rotBuf);
        gl.bufferData(gl.ARRAY_BUFFER, rotData, gl.DYNAMIC_DRAW);
        gl.enableVertexAttribArray(4);
        gl.vertexAttribPointer(4, 4, gl.FLOAT, false, 0, 0);
        gl.vertexAttribDivisor(4, 1);
    }

    function resize() {
        var r = canvas.getBoundingClientRect();
        var dpr = POSTER ? window.devicePixelRatio : Math.min(window.devicePixelRatio || 1, small ? 1.5 : 1.75);
        var nw = Math.max(1, Math.round(r.width * dpr)), nh = Math.max(1, Math.round(r.height * dpr));
        if (nw === W && nh === H) { return; }
        W = nw;
        H = nh;
        canvas.width = W;
        canvas.height = H;
        gl.viewport(0, 0, W, H);
        aspect = r.width / r.height;
        camera();
        layout();
    }

    /* ----------------------------------------------------------- behaviour -- */

    var pointer = null, lookAll = null, lastMove = 0, nextWave = 4, time = 0, scrollFill = 0, HOLD = 7;
    var headline = [-5, 5, 8];

    function setHeadline() {
        var h = document.querySelector('.tt-h1');
        if (!h) { return; }
        var s = stage.getBoundingClientRect(), b = h.getBoundingClientRect();
        var nx = ((b.left + b.width * 0.5) - s.left) / s.width * 2 - 1, ny = 1 - ((b.top + b.height * 0.5) - s.top) / s.height * 2;
        headline = unproject(nx, ny, 9);
    }
    function toNdc(e) {
        var s = stage.getBoundingClientRect();
        return [(e.clientX - s.left) / s.width * 2 - 1, 1 - (e.clientY - s.top) / s.height * 2];
    }
    function reach(n, radius) {
        heads.forEach(function (h) {
            var dx = (h.sx - n[0]) * Math.max(aspect, 1), dy = h.sy - n[1];
            if (dx * dx + dy * dy < radius * radius) { h.lit = time + HOLD; }
        });
    }
    function allLook(n) {
        var w = unproject(n[0], n[1], 9);
        lookAll = w;
        heads.forEach(function (h) {
            var dx = (h.sx - n[0]) * Math.max(aspect, 1), dy = h.sy - n[1];
            h.delay = time + Math.sqrt(dx * dx + dy * dy) * 0.32;
            h.lit = time + HOLD;
        });
    }

    var running = false, last = 0, awake = 0, dead = false;
    function wake() { awake = 3; if (!running && !dead && !document.hidden && onScreen) { running = true; last = performance.now(); requestAnimationFrame(frame); } }

    function step(dt) {
        time += dt;
        if (!POSTER && time > nextWave && time - lastMove > 3) {
            /* Left alone, the room keeps stirring: a band of heads turns on its own. */
            nextWave = time + 6.5;
            var cx = (Math.random() * 1.5) - 0.3, cy = -0.55 - Math.random() * 0.3;
            reach([cx, cy], 0.34);
        }
        var target = lookAll || pointer || headline, moving = false;
        for (var i = 0; i < heads.length; i++) {
            var h = heads[i];
            var fired = scrollFill > 0 && (i + 1) / heads.length < scrollFill;
            var on = (h.lit > time || fired) && !(h.delay && time < h.delay);
            var goal = on ? 1 : 0;
            h.a += (goal - h.a) * Math.min(1, dt * (on ? 4.2 : 1.5));
            var wantYaw = h.away, wantPitch = h.tilt;
            if (on) {
                var dx = target[0] - h.x, dy = target[1] - (h.y + 0.1), dz = target[2] - h.z;
                wantYaw = Math.atan2(dx, dz);
                wantPitch = -Math.atan2(dy, Math.hypot(dx, dz));
                wantYaw = Math.max(-1.1, Math.min(1.1, wantYaw));
                wantPitch = Math.max(-0.5, Math.min(0.4, wantPitch));
            }
            /* A soft spring: quick to start, a little overshoot, then settles. */
            var k = on ? 34 : 13, d = on ? 8.5 : 6.2;
            h.vy += ((wantYaw - h.yaw) * k - h.vy * d) * dt;
            h.vp += ((wantPitch - h.pitch) * k - h.vp * d) * dt;
            h.yaw += h.vy * dt;
            h.pitch += h.vp * dt;
            if (Math.abs(h.vy) > 0.004 || Math.abs(goal - h.a) > 0.004) { moving = true; }
            rotData[i * 4] = h.yaw;
            rotData[i * 4 + 1] = h.pitch;
            rotData[i * 4 + 2] = h.a;
            rotData[i * 4 + 3] = h.pal;
        }
        return moving;
    }
    function draw() {
        gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
        gl.bindBuffer(gl.ARRAY_BUFFER, rotBuf);
        gl.bufferSubData(gl.ARRAY_BUFFER, 0, rotData);
        gl.drawElementsInstanced(gl.TRIANGLES, mesh.idx.length, gl.UNSIGNED_INT, 0, heads.length);
    }
    /* A device that cannot keep up keeps the poster: a still picture beats a stutter. */
    var probe = 0, probeStart = 0, SKIP_PROBE = /[?&]herolive=1/.test(location.search);
    function tooSlow(now) {
        if (SKIP_PROBE || probe > 16) { return false; }
        if (probe++ === 0) { probeStart = now; return false; }
        return probe > 16 && (now - probeStart) / 16 > 85;
    }
    function frame(now) {
        if (!running) { return; }
        if (tooSlow(now)) {
            running = false;
            dead = true;
            canvas.classList.remove('is-on');
            stage.classList.remove('is-live');
            stage.classList.add('no-crowd');
            return;
        }
        var dt = Math.max(0, Math.min((now - last) / 1000, 0.05));
        last = now;
        var moving = step(dt);
        draw();
        if (!moving) { awake -= dt; } else { awake = 2; }
        /* Nothing moving: stop drawing until the next wave, to save the battery. */
        if (awake <= 0 && !pointerOver) {
            running = false;
            setTimeout(function () { wake(); }, Math.max(400, (nextWave - time) * 1000));
            return;
        }
        requestAnimationFrame(frame);
    }

    /* ------------------------------------------------------------- wiring --- */

    var pointerOver = false, onScreen = true;
    resize();
    setHeadline();
    step(0.016);

    if (POSTER) {
        for (var i = 0; i < 200; i++) { step(0.016); }
        draw();
        canvas.classList.add('is-on');
        stage.classList.add('is-live');
        window.__crowdReady = true;
        return;
    }
    draw();
    requestAnimationFrame(function () { canvas.classList.add('is-on'); stage.classList.add('is-live'); });
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }

    stage.addEventListener('pointermove', function (e) {
        var n = toNdc(e);
        pointer = unproject(n[0], n[1], 9);
        lastMove = time;
        pointerOver = true;
        reach(n, small ? 0.46 : 0.26);
        wake();
    }, { passive: true });
    stage.addEventListener('pointerleave', function () { pointerOver = false; pointer = null; });
    stage.addEventListener('pointerdown', function (e) {
        var n = toNdc(e);
        pointer = unproject(n[0], n[1], 9);
        reach(n, small ? 0.62 : 0.4);
        wake();
    }, { passive: true });

    /* The button is the point of the page, so it gets the whole room. */
    Array.prototype.forEach.call(document.querySelectorAll('[data-crowd-look]'), function (el) {
        function on() {
            var s = stage.getBoundingClientRect(), b = el.getBoundingClientRect();
            allLook([((b.left + b.width / 2) - s.left) / s.width * 2 - 1, 1 - ((b.top + b.height / 2) - s.top) / s.height * 2]);
            stage.classList.add('is-all');
            wake();
        }
        function off() { lookAll = null; stage.classList.remove('is-all'); wake(); }
        el.addEventListener('pointerenter', on);
        el.addEventListener('pointerleave', off);
        el.addEventListener('focus', on);
        el.addEventListener('blur', off);
    });

    /* Scrolling past the room turns it too, which is how a phone sees the idea. */
    window.addEventListener('scroll', function () {
        var r = stage.getBoundingClientRect();
        var p = Math.max(0, Math.min(1, -r.top / (r.height * 0.55)));
        if (p !== scrollFill) { scrollFill = p; wake(); }
    }, { passive: true });

    if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (en) {
            onScreen = en[0].isIntersecting;
            if (onScreen) { wake(); } else { running = false; }
        }).observe(stage);
    }
    document.addEventListener('visibilitychange', function () { if (document.hidden) { running = false; } else { wake(); } });
    var resizeTimer = 0;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () { small = window.innerWidth < 760; resize(); setHeadline(); step(0.016); draw(); }, 200);
    });
    wake();
})();
