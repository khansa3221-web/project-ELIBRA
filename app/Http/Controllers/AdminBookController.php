<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBookController extends Controller
{
    // Nama kolom CSV yang dikenali (huruf kecil, tanpa spasi/simbol) => nama field
    private const COLUMN_ALIASES = [
        'judul' => 'title', 'judulbuku' => 'title', 'namabuku' => 'title', 'title' => 'title',
        'penulis' => 'author', 'pengarang' => 'author', 'author' => 'author',
        'kategori' => 'category', 'category' => 'category',
        'penerbit' => 'publisher', 'publisher' => 'publisher',
        'tahun' => 'year', 'tahunterbit' => 'year', 'year' => 'year',
        'isbn' => 'isbn',
        'rak' => 'shelf', 'lokasi' => 'shelf', 'shelf' => 'shelf',
        'bahasa' => 'language', 'language' => 'language',
        'stok' => 'stock', 'stock' => 'stock', 'jumlah' => 'stock', 'eksemplar' => 'stock',
        'deskripsi' => 'description', 'sinopsis' => 'description', 'keterangan' => 'description',
    ];

    // ---------- Daftar buku (dengan pencarian) ----------
    public function index(Request $request)
    {
        $query = Book::with('category')->orderBy('title');

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                  ->orWhere('author', 'like', "%{$q}%")
                  ->orWhere('isbn', 'like', "%{$q}%");
            });
        }

        $books = $query->paginate(15)->withQueryString();

        return view('admin.books', compact('books'));
    }

    // ---------- Tambah ----------
    public function create()
    {
        return view('admin.book-form', [
            'book'       => new Book(['language' => 'Indonesia', 'total_stock' => 1]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data  = $this->validated($request);
        $total = (int) $data['total_stock'];
        $categoryId = $this->categoryId($data['category']);

        Book::create([
            'category_id'     => $categoryId,
            'title'           => $data['title'],
            'slug'            => $this->uniqueSlug($data['title']),
            'author'          => $data['author'],
            'publisher'       => $data['publisher'] ?? null,
            'year'            => $data['year'] ?? null,
            'cover_path'      => $request->hasFile('cover') ? $this->storeCover($request->file('cover')) : null,
            'isbn'            => $data['isbn'] ?? null,
            'shelf'           => $data['shelf'] ?? null,
            'language'        => $data['language'] ?? 'Indonesia',
            'description'     => $data['description'] ?? null,
            'total_stock'     => $total,
            'available_stock' => $total,
            'cover_gradient'  => 'mc'.(($categoryId % 6) + 1),
        ]);

        return redirect()->route('admin.books')
            ->with('success', 'Buku "'.$data['title'].'" berhasil ditambahkan.');
    }

    // ---------- Ubah ----------
    public function edit(Book $book)
    {
        return view('admin.book-form', [
            'book'       => $book->load('category'),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Book $book)
    {
        $data  = $this->validated($request);
        $total = (int) $data['total_stock'];

        // Stok tersedia ikut bergeser sesuai perubahan total stok
        $available = max(0, min($total, $book->available_stock + ($total - $book->total_stock)));

        $coverPath = $book->cover_path;
        if ($request->boolean('remove_cover')) {
            $this->deleteCover($coverPath);
            $coverPath = null;
        }
        if ($request->hasFile('cover')) {
            $this->deleteCover($coverPath);
            $coverPath = $this->storeCover($request->file('cover'));
        }

        $book->update([
            'category_id'     => $this->categoryId($data['category']),
            'title'           => $data['title'],
            'author'          => $data['author'],
            'publisher'       => $data['publisher'] ?? null,
            'year'            => $data['year'] ?? null,
            'cover_path'      => $coverPath,
            'isbn'            => $data['isbn'] ?? null,
            'shelf'           => $data['shelf'] ?? null,
            'language'        => $data['language'] ?? 'Indonesia',
            'description'     => $data['description'] ?? null,
            'total_stock'     => $total,
            'available_stock' => $available,
        ]);

        return redirect()->route('admin.books')
            ->with('success', 'Data buku "'.$book->title.'" berhasil diperbarui.');
    }

    // ---------- Hapus ----------
    public function destroy(Book $book)
    {
        $masihJalan = $book->loans()->whereIn('status', \App\Models\Loan::OPEN_STATUSES)->exists();

        if ($masihJalan) {
            return back()->with('error', 'Buku "'.$book->title.'" masih punya pengajuan atau peminjaman yang berjalan, jadi belum bisa dihapus.');
        }

        $title = $book->title;
        $this->deleteCover($book->cover_path);
        $book->loans()->delete();
        $book->delete();

        return redirect()->route('admin.books')->with('success', 'Buku "'.$title.'" dihapus.');
    }

    // ---------- Impor CSV ----------
    public function importForm()
    {
        return view('admin.book-import');
    }

    public function template()
    {
        $rows = [
            ['judul', 'penulis', 'kategori', 'isbn', 'rak', 'bahasa', 'stok', 'deskripsi'],
            ['Laskar Pelangi', 'Andrea Hirata', 'Fiksi', '9789793062792', 'Rak F1', 'Indonesia', 3, 'Novel tentang persahabatan anak-anak Belitung.'],
            ['Matematika Kelas XII', 'Kemendikbud', 'Pelajaran', '', 'Rak P2', 'Indonesia', 10, ''],
        ];

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // agar Excel membaca sebagai UTF-8
            foreach ($rows as $row) {
                fputcsv($out, $row, ';', '"', '\\');
            }
            fclose($out);
        }, 'template-data-buku.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|max:4096'], [
            'file.required' => 'Pilih file CSV terlebih dahulu.',
            'file.max'      => 'Ukuran file maksimal 4 MB.',
        ]);

        $file = $request->file('file');
        if (! in_array(strtolower($file->getClientOriginalExtension()), ['csv', 'txt'], true)) {
            return back()->withErrors(['file' => 'File harus berformat .csv (di Excel: File → Save As → CSV).']);
        }

        $handle = fopen($file->getRealPath(), 'r');
        $firstLine = (string) fgets($handle);
        rewind($handle);
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $header = fgetcsv($handle, 0, $delimiter, '"', '\\');
        if (! $header) {
            fclose($handle);
            return back()->withErrors(['file' => 'File kosong.']);
        }

        // Cocokkan nama kolom di baris pertama
        $columns = [];
        foreach ($header as $i => $name) {
            $name = preg_replace('/^\xEF\xBB\xBF/', '', (string) $name);
            $key  = preg_replace('/[^a-z]/', '', strtolower($this->utf8($name)));
            if (isset(self::COLUMN_ALIASES[$key])) {
                $columns[self::COLUMN_ALIASES[$key]] = $i;
            }
        }
        if (! isset($columns['title'], $columns['author'])) {
            fclose($handle);
            return back()->withErrors(['file' => 'Baris pertama harus berisi nama kolom, minimal "judul" dan "penulis". Unduh template agar formatnya sesuai.']);
        }

        // Opsi: hapus buku lama (mis. data contoh) sebelum impor.
        // Buku yang masih punya pengajuan/pinjaman berjalan tidak ikut dihapus.
        $removed = 0;
        if ($request->boolean('replace')) {
            Book::whereDoesntHave('loans', fn ($q) => $q->whereIn('status', ['pending', 'approved', 'active']))
                ->get()
                ->each(function ($old) use (&$removed) {
                    $old->loans()->delete();
                    $old->delete();
                    $removed++;
                });
            Category::doesntHave('books')->delete();
        }

        $added = 0;
        $skipped = 0;
        $errors = [];
        $categoryCache = [];
        $line = 1;

        while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            $line++;
            if ($line > 5001) {
                $errors[] = 'Impor berhenti di baris 5001 (maksimal 5.000 buku sekali impor). Impor sisanya dengan file terpisah.';
                break;
            }
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue; // baris kosong
            }

            $get = fn (string $key) => isset($columns[$key])
                ? trim($this->utf8((string) ($row[$columns[$key]] ?? '')))
                : '';

            $title  = mb_substr($get('title'), 0, 255);
            $author = mb_substr($get('author'), 0, 255);
            if ($title === '' || $author === '') {
                $skipped++;
                $errors[] = "Baris {$line}: judul atau penulis kosong, dilewati.";
                continue;
            }

            $stockRaw = $get('stock');
            if ($stockRaw === '') {
                $stock = 1;
            } elseif (ctype_digit($stockRaw)) {
                $stock = min(10000, (int) $stockRaw);
            } else {
                $skipped++;
                $errors[] = "Baris {$line} ({$title}): stok \"{$stockRaw}\" bukan angka bulat, dilewati.";
                continue;
            }

            $isbn = $get('isbn');
            if ($isbn !== '' && preg_match('/^[\d.,]+e\+?\d+$/i', $isbn)) {
                $errors[] = "Baris {$line} ({$title}): ISBN \"{$isbn}\" rusak oleh Excel, dikosongkan. Format kolom ISBN sebagai Teks sebelum mengisi.";
                $isbn = '';
            }
            $isbn = $isbn !== '' ? mb_substr($isbn, 0, 50) : null;

            // Lewati buku yang sudah ada (judul+penulis sama, atau ISBN sama)
            $exists = Book::whereRaw('LOWER(title) = ? AND LOWER(author) = ?', [mb_strtolower($title), mb_strtolower($author)])
                ->when($isbn !== null, fn ($q) => $q->orWhere('isbn', $isbn))
                ->exists();
            if ($exists) {
                $skipped++;
                $errors[] = "Baris {$line}: \"{$title}\" sudah ada, dilewati.";
                continue;
            }

            try {
                $categoryName = mb_substr($get('category') ?: 'Umum', 0, 100);
                $categoryKey  = mb_strtolower($categoryName);
                $categoryCache[$categoryKey] ??= $this->categoryId($categoryName);
                $categoryId = $categoryCache[$categoryKey];

                Book::create([
                    'category_id'     => $categoryId,
                    'title'           => $title,
                    'slug'            => $this->uniqueSlug($title),
                    'author'          => $author,
                    'publisher'       => mb_substr($get('publisher'), 0, 150) ?: null,
                    'year'            => preg_match('/^\\d{4}$/', $get('year')) ? (int) $get('year') : null,
                    'isbn'            => $isbn,
                    'shelf'           => mb_substr($get('shelf'), 0, 50) ?: null,
                    'language'        => mb_substr($get('language'), 0, 50) ?: 'Indonesia',
                    'description'     => mb_substr($get('description'), 0, 2000) ?: null,
                    'total_stock'     => $stock,
                    'available_stock' => $stock,
                    'cover_gradient'  => 'mc'.(($categoryId % 6) + 1),
                ]);
                $added++;
            } catch (\Throwable $e) {
                report($e);
                $skipped++;
                $errors[] = "Baris {$line} ({$title}): gagal disimpan.";
            }
        }
        fclose($handle);

        $summary = "Impor selesai: {$added} buku ditambahkan"
            .($removed ? ", {$removed} buku lama dihapus" : '')
            .($skipped ? ", {$skipped} baris dilewati" : '').'.';

        if (count($errors) > 30) {
            $extra = count($errors) - 30;
            $errors = array_slice($errors, 0, 30);
            $errors[] = "... dan {$extra} catatan lainnya.";
        }

        return redirect()->route('admin.books.import')
            ->with($added > 0 ? 'success' : 'error', $summary)
            ->with('import_errors', $errors);
    }

    // ---------- Pembantu ----------
    private function validated(Request $request): array
    {
        return $request->validate([
            'title'       => 'required|string|max:255',
            'author'      => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'isbn'        => 'nullable|string|max:50',
            'shelf'       => 'nullable|string|max:50',
            'language'    => 'nullable|string|max:50',
            'description' => 'nullable|string|max:2000',
            'total_stock' => 'required|integer|min:0|max:10000',
            'publisher'   => 'nullable|string|max:150',
            'year'        => 'nullable|integer|min:1000|max:'.((int) date('Y') + 1),
            'cover'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:min_width=150,min_height=200',
        ], [
            'cover.image'      => 'Cover harus berupa gambar.',
            'cover.mimes'      => 'Cover harus berformat JPG, PNG, atau WEBP.',
            'cover.max'        => 'Ukuran cover maksimal 2 MB.',
            'cover.dimensions' => 'Cover terlalu kecil (minimal 150 x 200 piksel).',
            'cover.uploaded'   => 'Cover gagal diunggah. Coba file lain (maksimal 2 MB).',
        ]);
    }

    // Simpan cover ke public/uploads/covers dengan nama acak (tidak memakai nama file asli)
    private function storeCover(\Illuminate\Http\UploadedFile $file): string
    {
        $dir  = public_path('uploads/covers');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = Str::random(32).'.'.($file->guessExtension() ?: 'jpg');
        $file->move($dir, $name);

        return 'uploads/covers/'.$name;
    }

    private function deleteCover(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/covers/') && is_file(public_path($path))) {
            @unlink(public_path($path));
        }
    }

    private function categoryId(string $name): int
    {
        $name = trim($name);
        $slug = Str::slug($name) ?: 'umum';

        return Category::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'buku';
        $slug = $base;
        $i = 2;

        while (Book::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function utf8(string $value): string
    {
        return mb_check_encoding($value, 'UTF-8')
            ? $value
            : mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
    }
}
