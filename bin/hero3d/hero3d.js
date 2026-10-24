/**
 * VTurnU homepage hero: real-time WebGL scene.
 *
 * Source file. The site loads the bundled, minified build at
 * assets/js/hero3d.min.js; rebuild it with bin/build-hero3d.sh after editing.
 *
 * One scene, four states, driven by scroll progress inside the pinned hero:
 *   0  The VU logo as iridescent glass tubes, traced by a particle halo.
 *   1  The particles pour into a spinning lead funnel (attention in, pipeline out).
 *   2  They become a dotted globe with real continents, market pins and arcs from
 *      Chennai. Markets are interactive HTML hotspots anchored to the globe.
 *   3  They form a portal ring with the logo back at its centre.
 *
 * home.js owns scrolling and the HTML scenes. This module only renders, and
 * reads state through the getState() callback it is given.
 */
import {
    WebGLRenderer, Scene, PerspectiveCamera, Color, Group, Vector2, Vector3, Matrix4,
    BufferGeometry, BufferAttribute, Points, ShaderMaterial, Mesh, SphereGeometry, TubeGeometry,
    CylinderGeometry, PlaneGeometry, MeshBasicMaterial, MeshPhysicalMaterial, LineCurve3,
    QuadraticBezierCurve3, CurvePath, CatmullRomCurve3, PMREMGenerator, BackSide, DoubleSide,
    AdditiveBlending, ACESFilmicToneMapping, SRGBColorSpace, Raycaster, Plane, MathUtils, DirectionalLight
} from 'three';
import { LAND_W, LAND_H, LAND_B64 } from './landmask.js';

const HEX = ['#00B8D9', '#4338CA', '#FF3D8A', '#FF9100'];   // logo: V stroke, then U stroke
const TAU = Math.PI * 2;
const HQ = [13.08, 80.27];                                   // Chennai

/* --------------------------------------------------------------- helpers */

function rng(seed) {
    let s = seed >>> 0;
    return function () {
        s = (s + 0x6D2B79F5) >>> 0;
        let t = s;
        t = Math.imul(t ^ (t >>> 15), t | 1);
        t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
}

function latLon(lat, lon, r) {
    const p = MathUtils.degToRad(lat), l = MathUtils.degToRad(lon);
    return new Vector3(Math.cos(p) * Math.sin(l) * r, Math.sin(p) * r, Math.cos(p) * Math.cos(l) * r);
}

function landLookup() {
    const bin = atob(LAND_B64);
    const bytes = new Uint8Array(bin.length);
    for (let i = 0; i < bin.length; i++) { bytes[i] = bin.charCodeAt(i); }
    return function (lat, lon) {
        const x = Math.min(LAND_W - 1, Math.max(0, Math.floor(lon + 180)));
        const y = Math.min(LAND_H - 1, Math.max(0, Math.floor(90 - lat)));
        const i = y * LAND_W + x;
        return (bytes[i >> 3] >> (i & 7)) & 1;
    };
}

/* ------------------------------------------------------------ logo build */

/* The logo SVG is drawn in a 100-unit box: V = M16 18 L38 80 L60 18,
   U = M54 24 V56 Q54 80 71 80 Q88 80 88 56 V24, stroke width 14, round caps. */
const S = 1 / 24;
const lp = (x, y, z) => new Vector3((x - 52) * S, -(y - 49) * S, z || 0);
const TUBE_R = 7 * S;

function logoParts() {
    const zV = 0.16, zU = -0.16;
    const v = [lp(16, 18, zV), lp(38, 80, zV), lp(60, 18, zV)];
    const u = new CurvePath();
    u.add(new LineCurve3(lp(54, 24, zU), lp(54, 56, zU)));
    u.add(new QuadraticBezierCurve3(lp(54, 56, zU), lp(54, 80, zU), lp(71, 80, zU)));
    u.add(new QuadraticBezierCurve3(lp(71, 80, zU), lp(88, 80, zU), lp(88, 56, zU)));
    u.add(new LineCurve3(lp(88, 56, zU), lp(88, 24, zU)));
    return { v, u, vTop: lp(0, 18).y, vBot: lp(0, 80).y, uTop: lp(0, 24).y, uBot: lp(0, 80).y };
}

/* Vertical gradient per stroke, exactly as the SVG gradients run. */
function paint(geo, top, bot, cTop, cBot) {
    const pos = geo.attributes.position, col = new Float32Array(pos.count * 3), c = new Color();
    for (let i = 0; i < pos.count; i++) {
        const t = MathUtils.clamp((top - pos.getY(i)) / (top - bot), 0, 1);
        c.copy(cTop).lerp(cBot, t);
        col[i * 3] = c.r; col[i * 3 + 1] = c.g; col[i * 3 + 2] = c.b;
    }
    geo.setAttribute('color', new BufferAttribute(col, 3));
    return geo;
}

function buildLogo(material) {
    const L = logoParts(), g = new Group();
    const C = HEX.map((h) => new Color(h));
    const add = (geo, top, bot, a, b) => g.add(new Mesh(paint(geo, top, bot, a, b), material));
    add(new TubeGeometry(new LineCurve3(L.v[0], L.v[1]), 24, TUBE_R, 40, false), L.vTop, L.vBot, C[0], C[1]);
    add(new TubeGeometry(new LineCurve3(L.v[1], L.v[2]), 24, TUBE_R, 40, false), L.vTop, L.vBot, C[0], C[1]);
    add(new TubeGeometry(L.u, 160, TUBE_R, 40, false), L.uTop, L.uBot, C[2], C[3]);
    const cap = new SphereGeometry(TUBE_R, 40, 24);
    [L.v[0], L.v[1], L.v[2]].forEach((p) => { const m = add(cap.clone().translate(p.x, p.y, p.z), L.vTop, L.vBot, C[0], C[1]); return m; });
    [L.u.getPoint(0), L.u.getPoint(1)].forEach((p) => add(cap.clone().translate(p.x, p.y, p.z), L.uTop, L.uBot, C[2], C[3]));
    return { group: g, parts: L };
}

/* -------------------------------------------------------- environment map */

/* A small studio: dark dome plus brand-coloured soft boxes, baked to a PMREM
   so the glass picks up cyan, pink and orange reflections. */
function studio(renderer) {
    const env = new Scene();
    const dome = new Mesh(new SphereGeometry(20, 32, 16), new ShaderMaterial({
        side: BackSide,
        uniforms: {},
        vertexShader: 'varying vec3 vP; void main(){ vP = position; gl_Position = projectionMatrix * modelViewMatrix * vec4(position,1.); }',
        fragmentShader: 'varying vec3 vP; void main(){ float h = normalize(vP).y * .5 + .5; gl_FragColor = vec4(mix(vec3(.004,.003,.02), vec3(.05,.04,.12), h), 1.); }'
    }));
    env.add(dome);
    const box = (w, h, color, k, pos, look) => {
        const m = new Mesh(new PlaneGeometry(w, h), new MeshBasicMaterial({ color: new Color(color).multiplyScalar(k), side: DoubleSide }));
        m.position.copy(pos); m.lookAt(look || new Vector3()); env.add(m);
    };
    box(10, 4, '#ffffff', 2.6, new Vector3(0, 9, 3));
    box(5, 9, HEX[0], 2.2, new Vector3(-9, 1, 3));
    box(5, 9, HEX[2], 2.0, new Vector3(9, 0, 2));
    box(8, 3, HEX[3], 1.8, new Vector3(3, -8, 4));
    box(8, 6, HEX[1], 1.6, new Vector3(0, 2, -10));
    const pm = new PMREMGenerator(renderer);
    const tex = pm.fromScene(env, 0.035).texture;
    pm.dispose();
    return tex;
}

/* ------------------------------------------------------------- particles */

const PARTICLE_VS = `
attribute vec3 aLogo;
attribute vec3 aFunnel;
attribute vec3 aGlobe;
attribute vec3 aRing;
attribute vec4 aRand;
attribute vec4 aTone;
uniform float uTime;
uniform float uMorph;
uniform float uPixel;
uniform float uSize;
uniform float uGlobeRot;
uniform float uGlobeTilt;
uniform vec3 uPointer;
uniform float uPointerOn;
varying vec3 vColor;
varying float vAlpha;

vec3 brand(float t) {
    vec3 c0 = vec3(0.0, 0.722, 0.851);
    vec3 c1 = vec3(0.263, 0.220, 0.792);
    vec3 c2 = vec3(1.0, 0.239, 0.541);
    vec3 c3 = vec3(1.0, 0.569, 0.0);
    t = clamp(t, 0.0, 1.0);
    if (t < 0.3333) return mix(c0, c1, t * 3.0);
    if (t < 0.6666) return mix(c1, c2, (t - 0.3333) * 3.0);
    return mix(c2, c3, (t - 0.6666) * 3.0);
}
vec3 rotY(vec3 p, float a) { float c = cos(a), s = sin(a); return vec3(c * p.x + s * p.z, p.y, -s * p.x + c * p.z); }
vec3 rotX(vec3 p, float a) { float c = cos(a), s = sin(a); return vec3(p.x, c * p.y - s * p.z, s * p.y + c * p.z); }

void main() {
    /* Funnel: particles stream down a spinning vortex, faster near the tip. */
    float h = fract(aFunnel.y - uTime * 0.045 * (0.6 + aRand.w));
    float fr = mix(0.12, 2.05, pow(h, 1.55)) + aFunnel.z * 0.1;
    float fa = aFunnel.x + uTime * (0.25 + (1.0 - h) * 1.1);
    vec3 pF = vec3(cos(fa) * fr, (h - 0.5) * 4.3, sin(fa) * fr);
    float tF = mix(1.0, 0.0, h);

    vec3 pG = rotX(rotY(aGlobe, uGlobeRot), uGlobeTilt);

    float ra = aRing.x + uTime * 0.22;
    vec3 pR = vec3(cos(ra) * (2.05 + cos(aRing.y) * 0.3), sin(ra) * (2.05 + cos(aRing.y) * 0.3), sin(aRing.y) * 0.3 + aRing.z * 0.05);
    pR = rotX(pR, 0.18);

    vec3 pL = aLogo + vec3(0.0, sin(uTime * 1.3 + aRand.w * 6.2831) * 0.015, 0.0);

    float seg = floor(uMorph);
    float t = uMorph - seg;
    float k = clamp((t - aRand.x * 0.42) / 0.58, 0.0, 1.0);
    k = k * k * (3.0 - 2.0 * k);

    vec3 A; vec3 B; float tA; float tB; float aA; float aB;
    float aL = 0.55; float aF = 0.95; float aG = aTone.w; float aR = 0.9;
    if (seg < 0.5)      { A = pL; B = pF; tA = aTone.x; tB = tF;     aA = aL; aB = aF; }
    else if (seg < 1.5) { A = pF; B = pG; tA = tF;      tB = aTone.z; aA = aF; aB = aG; }
    else if (seg < 2.5) { A = pG; B = pR; tA = aTone.z; tB = aTone.y; aA = aG; aB = aR; }
    else                { A = pR; B = pR; tA = aTone.y; tB = aTone.y; aA = aR; aB = aR; }

    vec3 p = mix(A, B, k);
    float tr = sin(k * 3.14159);
    p += vec3(sin(p.y * 2.3 + uTime * 1.1 + aRand.w * 6.0), cos(p.x * 1.9 + uTime * 0.9), sin(p.z * 2.1 + uTime)) * 0.42 * tr;

    vec2 d = p.xy - uPointer.xy;
    float dl = length(d);
    p.xy += (d / max(dl, 0.001)) * 0.32 * smoothstep(0.9, 0.0, dl) * uPointerOn;

    vec4 mv = modelViewMatrix * vec4(p, 1.0);
    gl_Position = projectionMatrix * mv;
    gl_PointSize = mix(0.55, 1.5, aRand.y) * uSize * uPixel / -mv.z;

    vColor = brand(mix(tA, tB, k) + (aRand.z - 0.5) * 0.06);
    vAlpha = mix(aA, aB, k) * (0.65 + 0.35 * aRand.y);
}`;

const PARTICLE_FS = `
varying vec3 vColor;
varying float vAlpha;
void main() {
    float d = length(gl_PointCoord - 0.5);
    float a = smoothstep(0.5, 0.0, d);
    a *= a;
    gl_FragColor = vec4(vColor * (0.85 + 0.6 * a), a * vAlpha);
}`;

function buildParticles(count, parts, land, R) {
    const rand = rng(20200827);
    const aLogo = new Float32Array(count * 3), aFunnel = new Float32Array(count * 3);
    const aGlobe = new Float32Array(count * 3), aRing = new Float32Array(count * 3);
    const aRand = new Float32Array(count * 4), aTone = new Float32Array(count * 4);

    /* Logo: points on the tube surfaces, plus a loose halo. */
    const vLen = parts.v[0].distanceTo(parts.v[1]);
    const uLen = parts.u.getLength();
    const frames = parts.u.computeFrenetFrames(400, false);
    const tmp = new Vector3(), n = new Vector3(), b = new Vector3();
    for (let i = 0; i < count; i++) {
        const r = TUBE_R * (1.02 + rand() * 0.12);
        const ang = rand() * TAU;
        let p, tone;
        const pick = rand() * (vLen * 2 + uLen);
        if (rand() < 0.14) {
            const a = rand() * TAU, rr = 1.7 + rand() * 0.9;
            p = new Vector3(Math.cos(a) * rr, (rand() - 0.5) * 2.6, Math.sin(a) * rr * 0.6);
            tone = rand();
        } else if (pick < vLen * 2) {
            const seg = pick < vLen ? 0 : 1;
            const t = (pick % vLen) / vLen;
            const a = parts.v[seg], c = parts.v[seg + 1];
            p = a.clone().lerp(c, t);
            const dir = c.clone().sub(a).normalize();
            n.set(0, 0, 1).cross(dir).normalize(); b.copy(dir).cross(n).normalize();
            p.addScaledVector(n, Math.cos(ang) * r).addScaledVector(b, Math.sin(ang) * r);
            tone = 0.3333 * MathUtils.clamp((parts.vTop - p.y) / (parts.vTop - parts.vBot), 0, 1);
        } else {
            const t = (pick - vLen * 2) / uLen;
            const fi = Math.min(399, Math.floor(t * 400));
            p = parts.u.getPointAt(t, tmp).clone();
            p.addScaledVector(frames.normals[fi], Math.cos(ang) * r).addScaledVector(frames.binormals[fi], Math.sin(ang) * r);
            tone = 0.6666 + 0.3334 * MathUtils.clamp((parts.uTop - p.y) / (parts.uTop - parts.uBot), 0, 1);
        }
        aLogo.set([p.x, p.y, p.z], i * 3);
        aTone[i * 4] = tone;

        aFunnel.set([rand() * TAU, rand(), rand() * 2 - 1], i * 3);
        aRing.set([rand() * TAU, rand() * TAU, rand() * 2 - 1], i * 3);
        aTone[i * 4 + 1] = rand();
        aRand.set([rand(), rand(), rand(), rand()], i * 4);
    }

    /* Globe: Fibonacci points kept where the land mask says land, plus a faint ocean shell. */
    const landPts = [], seaPts = [];
    const M = 90000, golden = Math.PI * (3 - Math.sqrt(5));
    for (let i = 0; i < M; i++) {
        const y = 1 - (i / (M - 1)) * 2, rr = Math.sqrt(1 - y * y), th = golden * i;
        const x = Math.cos(th) * rr, z = Math.sin(th) * rr;
        const lat = Math.asin(y) * 180 / Math.PI, lon = Math.atan2(x, z) * 180 / Math.PI;
        (land(lat, lon) ? landPts : seaPts).push([x * R, y * R, z * R]);
    }
    const shuffle = (a) => { for (let i = a.length - 1; i > 0; i--) { const j = Math.floor(rand() * (i + 1)); [a[i], a[j]] = [a[j], a[i]]; } return a; };
    shuffle(landPts); shuffle(seaPts);
    const nLand = Math.floor(count * 0.8);
    for (let i = 0; i < count; i++) {
        const isLand = i < nLand;
        const src = isLand ? landPts[i % landPts.length] : seaPts[i % seaPts.length];
        aGlobe.set(src, i * 3);
        aTone[i * 4 + 2] = isLand ? 0.05 + rand() * 0.25 : 0.33;
        aTone[i * 4 + 3] = isLand ? 1.0 : 0.16;
    }

    const geo = new BufferGeometry();
    geo.setAttribute('position', new BufferAttribute(new Float32Array(count * 3), 3));
    geo.setAttribute('aLogo', new BufferAttribute(aLogo, 3));
    geo.setAttribute('aFunnel', new BufferAttribute(aFunnel, 3));
    geo.setAttribute('aGlobe', new BufferAttribute(aGlobe, 3));
    geo.setAttribute('aRing', new BufferAttribute(aRing, 3));
    geo.setAttribute('aRand', new BufferAttribute(aRand, 4));
    geo.setAttribute('aTone', new BufferAttribute(aTone, 4));
    geo.boundingSphere = null;

    const mat = new ShaderMaterial({
        vertexShader: PARTICLE_VS,
        fragmentShader: PARTICLE_FS,
        transparent: true,
        depthWrite: false,
        blending: AdditiveBlending,
        toneMapped: false,
        uniforms: {
            uTime: { value: 0 }, uMorph: { value: 0 }, uPixel: { value: 1 }, uSize: { value: 30 },
            uGlobeRot: { value: 0 }, uGlobeTilt: { value: 0.32 }, uPointer: { value: new Vector3(99, 99, 0) }, uPointerOn: { value: 0 }
        }
    });
    const pts = new Points(geo, mat);
    pts.frustumCulled = false;
    return pts;
}

/* ------------------------------------------------------------ background */

function buildBackground() {
    const geo = new BufferGeometry();
    geo.setAttribute('position', new BufferAttribute(new Float32Array([-1, -1, 0, 3, -1, 0, -1, 3, 0]), 3));
    const mat = new ShaderMaterial({
        depthWrite: false, depthTest: false, toneMapped: false,
        uniforms: { uTime: { value: 0 }, uAspect: { value: 1 }, uMorph: { value: 0 } },
        vertexShader: 'varying vec2 vUv; void main(){ vUv = position.xy * .5 + .5; gl_Position = vec4(position.xy, .9999, 1.); }',
        fragmentShader: `
            varying vec2 vUv; uniform float uTime; uniform float uAspect; uniform float uMorph;
            float orb(vec2 uv, vec2 c, float r) { vec2 d = (uv - c) * vec2(uAspect, 1.0); return smoothstep(r, 0.0, length(d)); }
            void main() {
                vec3 col = vec3(0.043, 0.031, 0.125);
                float t = uTime * 0.05;
                col += vec3(0.0, 0.722, 0.851) * 0.20 * orb(vUv, vec2(0.08 + 0.04 * sin(t), 0.92), 0.85);
                col += vec3(0.263, 0.220, 0.792) * 0.38 * orb(vUv, vec2(0.25 + 0.05 * cos(t * 1.3), 0.7), 0.9);
                col += vec3(1.0, 0.239, 0.541) * 0.20 * orb(vUv, vec2(0.92 + 0.03 * cos(t), 0.12 + 0.05 * sin(t * 0.7)), 0.8);
                col += vec3(1.0, 0.569, 0.0) * 0.10 * orb(vUv, vec2(0.75, -0.05), 0.6);
                col += vec3(0.263, 0.220, 0.792) * 0.10 * orb(vUv, vec2(0.72, 0.5), 0.55) * smoothstep(1.4, 2.0, uMorph) * smoothstep(2.6, 2.0, uMorph);
                float g = fract(sin(dot(vUv * 913.0 + uTime, vec2(12.9898, 78.233))) * 43758.5453);
                col += (g - 0.5) * 0.018;
                vec2 q = vUv - 0.5; col *= 1.0 - dot(q, q) * 0.55;
                gl_FragColor = vec4(col, 1.0);
            }`
    });
    const mesh = new Mesh(geo, mat);
    mesh.frustumCulled = false;
    mesh.renderOrder = -10;
    return mesh;
}

/* ---------------------------------------------------- globe pins and arcs */

const ARC_VS = 'attribute float aT; varying float vT; void main(){ vT = aT; gl_Position = projectionMatrix * modelViewMatrix * vec4(position,1.); }';
const ARC_FS = `
uniform float uTime; uniform float uAlpha; uniform float uOff; varying float vT;
void main(){
    float head = fract(uTime * 0.35 + uOff);
    float pulse = smoothstep(0.12, 0.0, abs(vT - head));
    vec3 col = mix(vec3(1.0, 0.239, 0.541), vec3(1.0, 0.569, 0.0), vT);
    gl_FragColor = vec4(col * (1.0 + pulse * 1.5), (0.28 + pulse * 0.9) * uAlpha);
}`;

function buildGlobeExtras(markets, R) {
    const g = new Group();
    const hq = latLon(HQ[0], HQ[1], R);
    const arcMats = [];
    const pinMat = new MeshBasicMaterial({ color: new Color(HEX[2]), transparent: true, blending: AdditiveBlending, depthWrite: false, toneMapped: false });
    const hqMat = new MeshBasicMaterial({ color: new Color(HEX[0]), transparent: true, blending: AdditiveBlending, depthWrite: false, toneMapped: false });
    const beamGeo = new CylinderGeometry(0.006, 0.018, 0.34, 8, 1, true).translate(0, 0.17, 0);
    const dotGeo = new SphereGeometry(0.035, 12, 8);
    const up = new Vector3(0, 1, 0);

    markets.forEach((m, i) => {
        const p = latLon(m.lat, m.lon, R);
        const isHQ = Math.abs(m.lat - HQ[0]) < 0.01 && Math.abs(m.lon - HQ[1]) < 0.01;
        const dot = new Mesh(dotGeo, isHQ ? hqMat : pinMat);
        dot.position.copy(p);
        g.add(dot);
        const beam = new Mesh(beamGeo, isHQ ? hqMat : pinMat);
        beam.position.copy(p);
        beam.quaternion.setFromUnitVectors(up, p.clone().normalize());
        g.add(beam);
        if (isHQ) { return; }
        const ang = hq.angleTo(p);
        const pts = [];
        for (let s = 0; s <= 48; s++) {
            const t = s / 48;
            const v = hq.clone().normalize().lerp(p.clone().normalize(), t).normalize();
            pts.push(v.multiplyScalar(R * (1 + Math.sin(Math.PI * t) * Math.min(0.16, 0.04 + ang * 0.08))));
        }
        const tube = new TubeGeometry(new CatmullRomCurve3(pts), 64, 0.007, 6, false);
        const aT = new Float32Array(tube.attributes.position.count);
        const ring = 7;
        for (let j = 0; j < aT.length; j++) { aT[j] = Math.floor(j / ring) / 64; }
        tube.setAttribute('aT', new BufferAttribute(aT, 1));
        const mat = new ShaderMaterial({
            vertexShader: ARC_VS, fragmentShader: ARC_FS, transparent: true, depthWrite: false,
            blending: AdditiveBlending, toneMapped: false,
            uniforms: { uTime: { value: 0 }, uAlpha: { value: 0 }, uOff: { value: i * 0.137 } }
        });
        arcMats.push(mat);
        g.add(new Mesh(tube, mat));
    });
    return { group: g, arcMats, pinMat, hqMat };
}

/* ================================================================ start */

export function start(opts) {
    const mount = opts.mount;
    const stage = opts.stage;
    const getState = opts.getState;
    const marketEls = Array.prototype.slice.call(opts.markets || []);
    const funnelEls = Array.prototype.slice.call(opts.funnel || []);
    const panel = opts.panel || null;
    const mobile = () => stage.clientWidth < 900;

    const renderer = new WebGLRenderer({ antialias: true, alpha: false, powerPreference: 'high-performance' });
    renderer.setClearColor(0x0b0820, 1);
    renderer.toneMapping = ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;
    renderer.outputColorSpace = SRGBColorSpace;
    mount.appendChild(renderer.domElement);

    const scene = new Scene();
    scene.environment = studio(renderer);
    const camera = new PerspectiveCamera(36, 1, 0.1, 100);
    camera.position.set(0, 0, 10);

    const key = new DirectionalLight(0xffffff, 1.4);
    key.position.set(-3, 5, 6);
    scene.add(key);

    scene.add(buildBackground());
    const bg = scene.children[scene.children.length - 1];

    /* The whole visual sits in one rig that moves to the right half on wide
       screens and below the copy on phones. */
    const rig = new Group();
    scene.add(rig);

    const lite = mobile() || (navigator.hardwareConcurrency || 8) <= 4;
    const glass = new MeshPhysicalMaterial({
        vertexColors: true, roughness: 0.16, metalness: 0.0, clearcoat: 1, clearcoatRoughness: 0.05,
        iridescence: 0.7, iridescenceIOR: 1.32, iridescenceThicknessRange: [160, 560],
        transmission: lite ? 0 : 0.22, thickness: 0.9, ior: 1.42, envMapIntensity: 1.15, specularIntensity: 1
    });
    const logo = buildLogo(glass);
    const logoRig = new Group();
    logoRig.add(logo.group);
    rig.add(logoRig);

    const R = 2.05;
    const land = landLookup();
    const count = lite ? 7000 : 15000;
    const particles = buildParticles(count, logo.parts, land, R);
    rig.add(particles);
    const U = particles.material.uniforms;

    const markets = marketEls.map((el) => ({
        el, lat: parseFloat(el.getAttribute('data-lat')), lon: parseFloat(el.getAttribute('data-lon'))
    }));
    const globeRig = new Group();
    const extras = buildGlobeExtras(markets, R);
    globeRig.add(extras.group);
    rig.add(globeRig);

    /* ---- sizing ---- */
    let W = 1, H = 1;
    function resize() {
        W = mount.clientWidth || stage.clientWidth;
        H = mount.clientHeight || stage.clientHeight;
        const dpr = Math.min(window.devicePixelRatio || 1, lite ? 1.4 : 1.75);
        renderer.setPixelRatio(dpr);
        renderer.setSize(W, H, false);
        renderer.domElement.style.width = '100%';
        renderer.domElement.style.height = '100%';
        camera.aspect = W / H;
        camera.updateProjectionMatrix();
        U.uPixel.value = dpr * (H / 900);
        bg.material.uniforms.uAspect.value = W / H;
    }
    resize();

    /* ---- pointer ---- */
    const pointer = new Vector2(0, 0), pointerS = new Vector2(0, 0);
    let pointerIn = false;
    const ray = new Raycaster(), plane = new Plane(new Vector3(0, 0, 1), 0), hit = new Vector3();
    stage.addEventListener('pointermove', (e) => {
        const r = stage.getBoundingClientRect();
        pointer.set(((e.clientX - r.left) / r.width) * 2 - 1, -((e.clientY - r.top) / r.height) * 2 + 1);
        pointerIn = e.pointerType === 'mouse';
    }, { passive: true });
    stage.addEventListener('pointerleave', () => { pointerIn = false; });

    /* ---- globe interaction: drag to spin, pins to focus ---- */
    let globeRot = 0, globeTarget = null, dragging = false, dragX = 0, lastInteract = 0, active = -1;
    stage.addEventListener('pointerdown', (e) => {
        if (getState().scene !== 2 || e.target.closest('a, button')) { return; }
        dragging = true; dragX = e.clientX; lastInteract = performance.now(); globeTarget = null;
    });
    window.addEventListener('pointerup', () => { dragging = false; });
    window.addEventListener('pointermove', (e) => {
        if (!dragging) { return; }
        globeRot += (e.clientX - dragX) * 0.006; dragX = e.clientX; lastInteract = performance.now();
    }, { passive: true });

    function focusMarket(i, byUser) {
        if (i < 0 || i >= markets.length) { return; }
        active = i;
        const want = -MathUtils.degToRad(markets[i].lon);
        let d = (want - globeRot) % TAU;
        if (d > Math.PI) { d -= TAU; } else if (d < -Math.PI) { d += TAU; }
        globeTarget = globeRot + d;
        markets.forEach((m, k) => m.el.classList.toggle('is-active', k === i));
        /* Phones show the insight in a fixed panel under the globe. */
        if (panel) {
            const card = markets[i].el.querySelector('.hp3-mcard');
            panel.innerHTML = card ? card.innerHTML : '';
            panel.classList.add('is-on');
        }
        if (byUser) { lastInteract = performance.now(); }
    }
    markets.forEach((m, i) => {
        const btn = m.el.querySelector('button');
        if (!btn) { return; }
        btn.addEventListener('click', () => focusMarket(i, true));
        btn.addEventListener('mouseenter', () => focusMarket(i, true));
        btn.addEventListener('focus', () => focusMarket(i, true));
    });

    /* ---- per-frame state ---- */
    let morph = 0, logoVis = 1, last = performance.now(), running = true, raf = 0, tourAt = 0, tourIdx = 0, sceneWas = -1;
    const proj = new Vector3(), world = new Vector3(), camDir = new Vector3(), m4 = new Matrix4();

    function layout(state) {
        const mob = mobile();
        const aspect = W / H;
        if (mob) {
            /* Phones stack copy over the visual, so the rig moves per chapter:
               logo low, funnel mid, globe high (above the insight panel), ring mid. */
            const ys = [-2.15, -1.6, -0.75, -1.65];
            const k = Math.min(2, Math.floor(morph)), f = Math.min(1, morph - k);
            rig.position.set(0, ys[k] + (ys[k + 1] - ys[k]) * f, 0);
            rig.scale.setScalar(Math.min(0.8, aspect * 1.25));
        } else {
            const x = Math.min(2.8, 1.05 * aspect + 0.3);
            rig.position.set(x, -0.05, 0);
            rig.scale.setScalar(1);
        }
        U.uSize.value = mob ? 26 : 30;
    }

    function place(el, v, visible, align) {
        if (!visible) { el.classList.add('is-hidden'); return; }
        proj.copy(v).project(camera);
        const x = (proj.x * 0.5 + 0.5) * W, y = (-proj.y * 0.5 + 0.5) * H;
        el.classList.remove('is-hidden');
        const shift = align === 'left' ? ' translateX(-100%)' : (align === 'center' ? ' translateX(-50%)' : '');
        el.style.transform = 'translate3d(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px,0)' + shift;
    }

    function frame(now) {
        if (!running) { return; }
        raf = requestAnimationFrame(frame);
        const dt = Math.min(0.05, (now - last) / 1000);
        last = now;
        const state = getState();
        if (!state.visible) { return; }
        const time = now / 1000;
        layout(state);

        /* Scroll position to morph target: each change finishes just before
           its scene's copy takes over. */
        const x = state.p * 4;
        let target = 0;
        for (let k = 1; k <= 3; k++) { target += MathUtils.smoothstep(x, k - 0.5, k + 0.02); }
        morph += (target - morph) * Math.min(1, dt * 3.2);

        U.uTime.value = time;
        U.uMorph.value = morph;
        bg.material.uniforms.uTime.value = time;
        bg.material.uniforms.uMorph.value = morph;

        pointerS.lerp(pointer, Math.min(1, dt * 4));

        /* Logo: hero at scene 0, shrinks away, returns inside the ring. */
        const show0 = 1 - MathUtils.smoothstep(morph, 0.05, 0.75);
        const show3 = MathUtils.smoothstep(morph, 2.35, 2.95);
        logoVis = Math.max(show0, show3);
        logoRig.visible = logoVis > 0.01;
        const back = show3 > show0;
        const logoK = mobile() ? 0.78 : 1;
        logoRig.scale.setScalar(Math.max(0.001, (back ? 0.25 + 0.4 * show3 : 0.35 + 0.65 * show0) * logoK));
        logoRig.rotation.y = back
            ? time * 0.45
            : pointerS.x * 0.45 + Math.sin(time * 0.6) * 0.12 + (1 - show0) * 2.2;
        logoRig.rotation.x = -pointerS.y * 0.25 + Math.sin(time * 0.8) * 0.05;
        logoRig.position.y = Math.sin(time * 0.9) * 0.08;

        /* Globe spin: auto, drag, or easing to a focused market. */
        const chapter = state.scene;
        if (chapter === 2 && sceneWas !== 2) { tourAt = now + 1600; lastInteract = 0; }
        sceneWas = chapter;
        if (globeTarget !== null) {
            globeRot += (globeTarget - globeRot) * Math.min(1, dt * 3);
            if (Math.abs(globeTarget - globeRot) < 0.002) { globeTarget = null; }
        } else if (!dragging) {
            globeRot += dt * 0.12;
        }
        if (chapter === 2 && markets.length && !dragging && now - lastInteract > 6000 && now > tourAt) {
            focusMarket(tourIdx % markets.length, false);
            tourIdx += 1;
            tourAt = now + 3400;
        }
        if (chapter !== 2 && active !== -1) {
            active = -1;
            markets.forEach((m) => m.el.classList.remove('is-active'));
            if (panel) { panel.classList.remove('is-on'); }
        }
        U.uGlobeRot.value = globeRot;
        /* Same order as the particle shader: spin about Y, then tilt about X. */
        globeRig.rotation.set(U.uGlobeTilt.value, globeRot, 0, 'XYZ');

        const globeVis = Math.max(0, 1 - Math.abs(morph - 2) * 1.6);
        extras.arcMats.forEach((m) => { m.uniforms.uTime.value = time; m.uniforms.uAlpha.value = globeVis; });
        extras.pinMat.opacity = globeVis; extras.hqMat.opacity = globeVis;
        globeRig.visible = globeVis > 0.01;

        /* Pointer repel in the particles' local space. */
        ray.setFromCamera(pointerS, camera);
        if (ray.ray.intersectPlane(plane, hit)) {
            m4.copy(rig.matrixWorld).invert();
            U.uPointer.value.copy(hit).applyMatrix4(m4);
        }
        U.uPointerOn.value += ((pointerIn && chapter !== 2 ? 1 : 0) - U.uPointerOn.value) * Math.min(1, dt * 4);

        rig.rotation.y = pointerS.x * 0.12 * (1 - globeVis);
        rig.rotation.x = -pointerS.y * 0.06;
        rig.updateMatrixWorld(true);

        renderer.render(scene, camera);

        /* HTML anchored to 3D: funnel stage tags and market hotspots. */
        const funnelVis = Math.max(0, 1 - Math.abs(morph - 1) * 1.8);
        funnelEls.forEach((el, i) => {
            const hh = parseFloat(el.getAttribute('data-h'));
            const r = 0.12 + (2.05 - 0.12) * Math.pow(hh, 1.55);
            /* Phones: labels ride the funnel's axis; wider screens: the rim. */
            const narrow = mobile();
            const side = el.getAttribute('data-side') === 'l' ? -1 : 1;
            world.set(narrow ? 0 : side * (r + 0.15), (hh - 0.5) * 4.3, 0).applyMatrix4(rig.matrixWorld);
            el.style.opacity = funnelVis.toFixed(3);
            el.classList.toggle('is-left', !narrow && side < 0);
            el.classList.toggle('is-center', narrow);
            place(el, world, funnelVis > 0.05, narrow ? 'center' : (side < 0 ? 'left' : ''));
        });
        camera.getWorldDirection(camDir);
        markets.forEach((m) => {
            world.copy(latLon(m.lat, m.lon, R * 1.02)).applyMatrix4(globeRig.matrixWorld);
            const normal = world.clone().sub(new Vector3().setFromMatrixPosition(globeRig.matrixWorld)).normalize();
            const facing = normal.dot(camDir) < -0.12;
            m.el.style.opacity = globeVis.toFixed(3);
            place(m.el, world, facing && globeVis > 0.25);
            /* Open the card towards free space so it never runs off screen. */
            m.el.classList.toggle('is-flip', proj.x * 0.5 + 0.5 > 1 - 480 / W);
            m.el.classList.toggle('is-up', -proj.y * 0.5 + 0.5 > 1 - 300 / H);
        });
    }

    const onResize = () => resize();
    window.addEventListener('resize', onResize);
    raf = requestAnimationFrame(frame);
    mount.classList.add('is-ready');

    return {
        stop() { running = false; cancelAnimationFrame(raf); },
        resume() { if (!running) { running = true; last = performance.now(); raf = requestAnimationFrame(frame); } },
        focusMarket
    };
}
