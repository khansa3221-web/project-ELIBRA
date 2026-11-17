// E-LIBRA — pembuat bundle aset (tanpa paket tambahan; cukup Node.js).
//   Jalankan:  node build-aset.mjs
// Menggabungkan & memadatkan CSS/JS menjadi public/build/elibra.min.css dan elibra.min.js.
// File sumber (public/css, public/js) TIDAK diubah. Hapus folder public/build bila ingin kembali ke file terpisah.
// Urutan file harus sama dengan App\Support\Assets::CSS / ::JS.
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';

const CSS = ['css/app.css', 'css/animations.css', 'css/extras.css', 'css/polish.css', 'css/dreamy.css', 'css/motion-boost.css', 'css/theme.css', 'css/perf.css'];
const JS  = ['js/animations.js', 'js/extras.js', 'js/polish.js', 'js/dreamy.js', 'js/motion-boost.js'];
const read = (f) => readFileSync(new URL('./public/' + f, import.meta.url), 'utf8');

// Lindungi string "..." / '...' dan url(...) agar spasi di dalamnya tidak ikut dipadatkan
function minCss(src) {
  const keep = [];
  const hold = (m) => { keep.push(m); return `\u0000${keep.length - 1}\u0000`; };
  let s = src.replace(/\/\*[\s\S]*?\*\//g, '');
  s = s.replace(/"(?:\\.|[^"\\])*"|'(?:\\.|[^'\\])*'|url\([^)]*\)/g, hold);
  s = s.replace(/\s+/g, ' ')
       .replace(/\s*([{};,])\s*/g, '$1')
       .replace(/;}/g, '}')
       .trim();
  return s.replace(/\u0000(\d+)\u0000/g, (_, i) => keep[+i]);
}

// JS: hanya hapus komentar satu baris penuh, komentar blok penuh, dan indentasi.
// (Tanpa mengubah nama variabel/isi baris, sehingga aman.)
function minJs(src) {
  let s = src.replace(/^\s*\/\*[\s\S]*?\*\/\s*$/gm, '');          // blok komentar yang berdiri sendiri
  s = s.split('\n').map((l) => l.trim()).filter((l) => l && !l.startsWith('//')).join('\n');
  return s;
}

mkdirSync(new URL('./public/build/', import.meta.url), { recursive: true });
const css = CSS.map((f) => minCss(read(f))).join('\n');
const js  = JS.map((f) => minJs(read(f))).join(';\n');
writeFileSync(new URL('./public/build/elibra.min.css', import.meta.url), css);
writeFileSync(new URL('./public/build/elibra.min.js', import.meta.url), js);
console.log(`CSS: ${css.length} byte, JS: ${js.length} byte -> public/build/`);
