/* E-LIBRA — pembuat QR Code (byte mode, koreksi galat M, versi 1-10). Tanpa dependensi, jalan offline.
   API: ElibraQR.matrix(teks) -> array 2D boolean | ElibraQR.svg(teks, {margin, dark, light}) -> string SVG */
(function (root) {
  'use strict';
  var ECC = [-1, 10, 16, 26, 18, 24, 16, 18, 22, 22, 26];   // kode koreksi per blok (level M)
  var BLK = [-1, 1, 1, 1, 2, 2, 4, 4, 4, 5, 5];              // jumlah blok (level M)

  function rawModules(v) {
    var r = (16 * v + 128) * v + 64;
    if (v >= 2) { var a = Math.floor(v / 7) + 2; r -= (25 * a - 10) * a - 55; if (v >= 7) r -= 36; }
    return r;
  }
  function dataCodewords(v) { return Math.floor(rawModules(v) / 8) - ECC[v] * BLK[v]; }

  var EXP = [], LOG = [];
  (function () { var x = 1; for (var i = 0; i < 255; i++) { EXP[i] = x; LOG[x] = i; x <<= 1; if (x & 256) x ^= 0x11D; } })();
  function gmul(a, b) { return (a === 0 || b === 0) ? 0 : EXP[(LOG[a] + LOG[b]) % 255]; }
  function rsGenerator(n) {
    var g = [1];
    for (var i = 0; i < n; i++) {
      var ng = new Array(g.length + 1).fill(0);
      for (var j = 0; j < g.length; j++) { ng[j] ^= gmul(g[j], 1); ng[j + 1] ^= gmul(g[j], EXP[i]); }
      g = ng;
    }
    return g.slice(1); // tanpa suku utama
  }
  function rsRemainder(data, gen) {
    var rem = new Array(gen.length).fill(0);
    data.forEach(function (b) {
      var f = b ^ rem.shift(); rem.push(0);
      for (var i = 0; i < gen.length; i++) rem[i] ^= gmul(gen[i], f);
    });
    return rem;
  }

  function utf8(s) { return Array.prototype.slice.call(new TextEncoder().encode(s)); }

  function encodeData(bytes) {
    var ver = 0;
    for (var v = 1; v <= 10; v++) {
      var cc = v < 10 ? 8 : 16;
      if (4 + cc + bytes.length * 8 <= dataCodewords(v) * 8) { ver = v; break; }
    }
    if (!ver) throw new Error('Teks terlalu panjang untuk QR (maks. 213 byte)');
    var bits = [];
    function put(val, len) { for (var i = len - 1; i >= 0; i--) bits.push((val >>> i) & 1); }
    put(4, 4); put(bytes.length, ver < 10 ? 8 : 16);
    bytes.forEach(function (b) { put(b, 8); });
    var cap = dataCodewords(ver) * 8;
    put(0, Math.min(4, cap - bits.length));
    while (bits.length % 8) bits.push(0);
    for (var pad = 0xEC; bits.length < cap; pad ^= 0xEC ^ 0x11) put(pad, 8);
    var data = [];
    for (var i = 0; i < bits.length; i += 8) { var b = 0; for (var k = 0; k < 8; k++) b = (b << 1) | bits[i + k]; data.push(b); }
    return { ver: ver, data: data };
  }

  function interleave(ver, data) {
    var nb = BLK[ver], el = ECC[ver], raw = Math.floor(rawModules(ver) / 8);
    var nShort = nb - raw % nb, shortLen = Math.floor(raw / nb), gen = rsGenerator(el), blocks = [], k = 0;
    for (var i = 0; i < nb; i++) {
      var dl = shortLen - el + (i < nShort ? 0 : 1), d = data.slice(k, k + dl); k += dl;
      var e = rsRemainder(d, gen);
      if (i < nShort) d.push(0); // penanda sementara agar panjang blok sama
      blocks.push({ d: d, e: e, real: dl });
    }
    var out = [];
    for (var c = 0; c < shortLen - el + 1; c++)
      blocks.forEach(function (bl, i) { if (c < bl.real) out.push(bl.d[c]); });
    for (var c2 = 0; c2 < el; c2++) blocks.forEach(function (bl) { out.push(bl.e[c2]); });
    return out;
  }

  function build(text) {
    var enc = encodeData(utf8(text)), ver = enc.ver, size = ver * 4 + 17;
    var codewords = interleave(ver, enc.data);
    var m = [], fn = [];
    for (var y = 0; y < size; y++) { m.push(new Array(size).fill(false)); fn.push(new Array(size).fill(false)); }
    function setF(x, y, dark) { m[y][x] = dark; fn[y][x] = true; }

    for (var i = 0; i < size; i++) { setF(6, i, i % 2 === 0); setF(i, 6, i % 2 === 0); }
    function finder(cx, cy) {
      for (var dy = -4; dy <= 4; dy++) for (var dx = -4; dx <= 4; dx++) {
        var x = cx + dx, y = cy + dy; if (x < 0 || y < 0 || x >= size || y >= size) continue;
        var d = Math.max(Math.abs(dx), Math.abs(dy)); setF(x, y, d !== 2 && d !== 4);
      }
    }
    finder(3, 3); finder(size - 4, 3); finder(3, size - 4);
    var pos = [];
    if (ver > 1) {
      var na = Math.floor(ver / 7) + 2, step = Math.ceil((ver * 4 + 4) / (na * 2 - 2)) * 2;
      pos = [6]; for (var p = size - 7; pos.length < na; p -= step) pos.splice(1, 0, p);
    }
    pos.forEach(function (ax, i) { pos.forEach(function (ay, j) {
      if ((i === 0 && j === 0) || (i === 0 && j === pos.length - 1) || (i === pos.length - 1 && j === 0)) return;
      for (var dy = -2; dy <= 2; dy++) for (var dx = -2; dx <= 2; dx++) setF(ax + dx, ay + dy, Math.max(Math.abs(dx), Math.abs(dy)) !== 1);
    }); });
    function drawFormat(mask) {
      var d = (0 << 3) | mask, r = d; // level M = 00
      for (var i = 0; i < 10; i++) r = (r << 1) ^ ((r >>> 9) * 0x537);
      var bits = ((d << 10) | r) ^ 0x5412, b = function (i) { return ((bits >>> i) & 1) !== 0; };
      for (var i2 = 0; i2 <= 5; i2++) setF(8, i2, b(i2));
      setF(8, 7, b(6)); setF(8, 8, b(7)); setF(7, 8, b(8));
      for (var i3 = 9; i3 < 15; i3++) setF(14 - i3, 8, b(i3));
      for (var i4 = 0; i4 < 8; i4++) setF(size - 1 - i4, 8, b(i4));
      for (var i5 = 8; i5 < 15; i5++) setF(8, size - 15 + i5, b(i5));
      setF(8, size - 8, true);
    }
    drawFormat(0);
    if (ver >= 7) {
      var r = ver; for (var i6 = 0; i6 < 12; i6++) r = (r << 1) ^ ((r >>> 11) * 0x1F25);
      var vb = (ver << 12) | r;
      for (var i7 = 0; i7 < 18; i7++) { var bit = ((vb >>> i7) & 1) !== 0, a = size - 11 + i7 % 3, bb = Math.floor(i7 / 3); setF(a, bb, bit); setF(bb, a, bit); }
    }
    var k = 0;
    for (var right = size - 1; right >= 1; right -= 2) {
      if (right === 6) right = 5;
      for (var vert = 0; vert < size; vert++) for (var j = 0; j < 2; j++) {
        var x = right - j, up = ((right + 1) & 2) === 0, y = up ? size - 1 - vert : vert;
        if (!fn[y][x] && k < codewords.length * 8) { m[y][x] = ((codewords[k >>> 3] >>> (7 - (k & 7))) & 1) !== 0; k++; }
      }
    }
    var masks = [
      function (x, y) { return (x + y) % 2 === 0; }, function (x, y) { return y % 2 === 0; },
      function (x, y) { return x % 3 === 0; }, function (x, y) { return (x + y) % 3 === 0; },
      function (x, y) { return (Math.floor(x / 3) + Math.floor(y / 2)) % 2 === 0; },
      function (x, y) { return x * y % 2 + x * y % 3 === 0; },
      function (x, y) { return (x * y % 2 + x * y % 3) % 2 === 0; },
      function (x, y) { return ((x + y) % 2 + x * y % 3) % 2 === 0; }
    ];
    function applyMask(mi) { for (var y = 0; y < size; y++) for (var x = 0; x < size; x++) if (!fn[y][x] && masks[mi](x, y)) m[y][x] = !m[y][x]; }
    function penalty() {
      var pen = 0, dark = 0, x, y;
      function line(get) {
        var run = 1, hist = '';
        for (var i = 0; i < size; i++) { var c = get(i); hist += c ? '1' : '0';
          if (i > 0 && c === get(i - 1)) { run++; if (run === 5) pen += 3; else if (run > 5) pen++; } else run = 1; }
        var re = /10111010000|00001011101/g, mt; while ((mt = re.exec(hist))) { pen += 40; re.lastIndex = mt.index + 1; }
      }
      for (y = 0; y < size; y++) line(function (i) { return m[y][i]; });
      for (x = 0; x < size; x++) line(function (i) { return m[i][x]; });
      for (y = 0; y < size - 1; y++) for (x = 0; x < size - 1; x++) { var c = m[y][x]; if (c === m[y][x + 1] && c === m[y + 1][x] && c === m[y + 1][x + 1]) pen += 3; }
      for (y = 0; y < size; y++) for (x = 0; x < size; x++) if (m[y][x]) dark++;
      pen += Math.floor(Math.abs(dark * 20 - size * size * 10) / (size * size)) * 10;
      return pen;
    }
    var best = 0, bestPen = Infinity;
    for (var mi = 0; mi < 8; mi++) { applyMask(mi); drawFormat(mi); var pn = penalty(); if (pn < bestPen) { bestPen = pn; best = mi; } applyMask(mi); }
    applyMask(best); drawFormat(best);
    return m;
  }

  function svg(text, o) {
    o = o || {}; var mg = o.margin == null ? 4 : o.margin, m = build(text), n = m.length, t = n + mg * 2, d = '';
    for (var y = 0; y < n; y++) for (var x = 0; x < n; x++) if (m[y][x]) d += 'M' + (x + mg) + ',' + (y + mg) + 'h1v1h-1z';
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + t + ' ' + t + '" shape-rendering="crispEdges">' +
      '<rect width="100%" height="100%" fill="' + (o.light || '#fff') + '"/><path d="' + d + '" fill="' + (o.dark || '#2B1A24') + '"/></svg>';
  }
  root.ElibraQR = { matrix: build, svg: svg };
  if (typeof module !== 'undefined') module.exports = root.ElibraQR;
})(typeof window !== 'undefined' ? window : globalThis);
