<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Support\Csv;
use App\Support\Msg;
use Illuminate\Http\Request;

class BookController extends Controller
{
    private function rules(): array
    {
        return [
            'title'          => 'required|string|max:200',
            'author'         => 'required|string|max:150',
            'category_id'    => 'nullable|exists:categories,id',
            'new_category'   => 'nullable|string|max:80',
            'isbn'           => 'nullable|string|max:50',
            'shelf'          => 'nullable|string|max:80',
            'language'       => 'nullable|string|max:50',
            'description'    => 'nullable|string|max:3000',
            'total_stock'    => 'required|integer|min:0|max:100000',
            'cover_gradient' => 'nullable|in:mc1,mc2,mc3,mc4,mc5,mc6',
        ];
    }

    private function categoryId(array $data): int
    {
        if (! empty($data['new_category'])) {
            return Category::resolve($data['new_category'])->id;
        }
        if (! empty($data['category_id'])) {
            return (int) $data['category_id'];
        }

        return Category::resolve('Umum')->id;
    }

    public function index(Request $request)
    {
        $books = Book::with('category')
            ->when($request->q, function ($query, $s) {
                $query->where(fn ($w) => $w->where('title', 'like', "%{$s}%")
                    ->orWhere('author', 'like', "%{$s}%")
                    ->orWhere('isbn', 'like', "%{$s}%"));
            })
            ->orderBy('title')->paginate(15)->withQueryString();

        return view('admin.books.index', compact('books'));
    }

    public function create()
    {
        return view('admin.books.form', [
            'book'       => new Book(['total_stock' => 1, 'language' => 'Indonesia']),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data  = $request->validate($this->rules(), Msg::id(), Msg::attributes());
        $total = (int) $data['total_stock'];

        Book::create([
            'category_id'     => $this->categoryId($data),
            'title'           => $data['title'],
            'slug'            => Book::uniqueSlug($data['title']),
            'author'          => $data['author'],
            'isbn'            => $data['isbn'] ?? null,
            'shelf'           => $data['shelf'] ?? null,
            'language'        => $data['language'] ?: 'Indonesia',
            'description'     => $data['description'] ?? null,
            'total_stock'     => $total,
            'available_stock' => $total,
            'cover_gradient'  => $data['cover_gradient'] ?: 'mc'.((Book::count() % 6) + 1),
        ]);

        return redirect()->route('admin.books.index')->with('success', 'Buku berhasil ditambahkan.');
    }

    public function edit(Book $book)
    {
        return view('admin.books.form', [
            'book'       => $book,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Book $book)
    {
        $data     = $request->validate($this->rules(), Msg::id(), Msg::attributes());
        $total    = (int) $data['total_stock'];
        $borrowed = $book->borrowedCount();

        if ($total < $borrowed) {
            return back()->withInput()->withErrors([
                'total_stock' => "Total stok tidak boleh kurang dari jumlah yang sedang dipinjam ({$borrowed}).",
            ]);
        }

        $book->update([
            'category_id'     => $this->categoryId($data),
            'title'           => $data['title'],
            'slug'            => $data['title'] === $book->title ? $book->slug : Book::uniqueSlug($data['title'], $book->id),
            'author'          => $data['author'],
            'isbn'            => $data['isbn'] ?? null,
            'shelf'           => $data['shelf'] ?? null,
            'language'        => $data['language'] ?: 'Indonesia',
            'description'     => $data['description'] ?? null,
            'total_stock'     => $total,
            'available_stock' => $total - $borrowed,
            'cover_gradient'  => $data['cover_gradient'] ?: $book->cover_gradient,
        ]);

        return redirect()->route('admin.books.index')->with('success', 'Data buku diperbarui.');
    }

    public function destroy(Book $book)
    {
        if ($book->loans()->whereIn('status', ['pending', 'approved', 'active'])->exists()) {
            return back()->with('error', 'Buku ini masih dipinjam atau ada pengajuan yang menunggu, belum bisa dihapus.');
        }

        $book->delete();

        return redirect()->route('admin.books.index')->with('success', 'Buku dihapus.');
    }

    public function template()
    {
        return Csv::download('template-buku.csv',
            ['judul', 'penulis', 'kategori', 'isbn', 'rak', 'bahasa', 'deskripsi', 'stok'],
            [['Laskar Pelangi', 'Andrea Hirata', 'Fiksi', '9789793062792', 'Rak A1', 'Indonesia', 'Novel tentang anak-anak Belitung.', '3']]
        );
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|extensions:csv,txt|max:4096'], Msg::id(), Msg::attributes());
        set_time_limit(0);

        $added = 0;
        $skipped = [];

        foreach (Csv::read($request->file('file')->getRealPath()) as $i => $row) {
            $line   = $i + 2; // baris 1 = judul kolom
            $title  = Csv::pick($row, ['judul', 'title']);
            $author = Csv::pick($row, ['penulis', 'pengarang', 'author']);
            $isbn   = Csv::pick($row, ['isbn']);

            if ($title === '' || $author === '') {
                $skipped[] = "baris {$line} (judul/penulis kosong)";
                continue;
            }

            $duplicate = Book::where(function ($q) use ($isbn, $title, $author) {
                $q->where(fn ($w) => $w->where('title', $title)->where('author', $author));
                if ($isbn !== '') {
                    $q->orWhere('isbn', $isbn);
                }
            })->exists();

            if ($duplicate) {
                $skipped[] = "baris {$line} ({$title} sudah ada)";
                continue;
            }

            $total = max(0, (int) (Csv::pick($row, ['stok', 'stock', 'jumlah']) ?: 1));

            Book::create([
                'category_id'     => Category::resolve(Csv::pick($row, ['kategori', 'category']))->id,
                'title'           => mb_substr($title, 0, 200),
                'slug'            => Book::uniqueSlug($title),
                'author'          => mb_substr($author, 0, 150),
                'isbn'            => $isbn ?: null,
                'shelf'           => Csv::pick($row, ['rak', 'shelf']) ?: null,
                'language'        => Csv::pick($row, ['bahasa', 'language']) ?: 'Indonesia',
                'description'     => Csv::pick($row, ['deskripsi', 'description']) ?: null,
                'total_stock'     => $total,
                'available_stock' => $total,
                'cover_gradient'  => 'mc'.((Book::count() % 6) + 1),
            ]);
            $added++;
        }

        $redirect = redirect()->route('admin.books.index')->with('success', "{$added} buku berhasil diimpor.");

        if ($skipped) {
            $redirect->with('error', count($skipped).' baris dilewati: '.implode('; ', array_slice($skipped, 0, 6)).(count($skipped) > 6 ? '; ...' : ''));
        }

        return $redirect;
    }
}
