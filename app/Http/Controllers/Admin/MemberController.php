<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Csv;
use App\Support\Msg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    /** Email internal untuk siswa yang tidak punya email (login tetap lewat NIS). */
    private function fallbackEmail(string $nis): string
    {
        return strtolower(preg_replace('/[^A-Za-z0-9]/', '', $nis)).'@siswa.elibra.test';
    }

    public function index(Request $request)
    {
        $members = User::where('role', 'siswa')
            ->when($request->q, function ($query, $s) {
                $query->where(fn ($w) => $w->where('name', 'like', "%{$s}%")
                    ->orWhere('nis', 'like', "%{$s}%")
                    ->orWhere('class', 'like', "%{$s}%"));
            })
            ->withCount('loans')
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.members.index', compact('members'));
    }

    public function create()
    {
        return view('admin.members.form', ['member' => new User()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:150',
            'nis'      => 'required|string|max:50|unique:users,nis',
            'class'    => 'required|string|max:50',
            'email'    => 'nullable|email|max:150|unique:users,email',
            'password' => 'nullable|string|min:6|max:100',
        ], Msg::id(), Msg::attributes());

        $email = strtolower($data['email'] ?? '') ?: $this->fallbackEmail($data['nis']);
        if (User::where('email', $email)->exists()) {
            return back()->withInput()->withErrors(['email' => 'Email sudah dipakai anggota lain.']);
        }

        User::create([
            'name'     => $data['name'],
            'nis'      => $data['nis'],
            'class'    => $data['class'],
            'email'    => $email,
            'password' => ($data['password'] ?? '') ?: $data['nis'],
            'role'     => 'siswa',
        ]);

        return redirect()->route('admin.members.index')
            ->with('success', 'Anggota ditambahkan. Kata sandi awal: '.((($data['password'] ?? '') ?: 'sama dengan NIS').'.'));
    }

    public function edit(User $user)
    {
        abort_unless($user->role === 'siswa', 404);

        return view('admin.members.form', ['member' => $user]);
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->role === 'siswa', 404);

        $data = $request->validate([
            'name'     => 'required|string|max:150',
            'nis'      => 'required|string|max:50|unique:users,nis,'.$user->id,
            'class'    => 'required|string|max:50',
            'email'    => 'nullable|email|max:150|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6|max:100',
        ], Msg::id(), Msg::attributes());

        $user->fill([
            'name'  => $data['name'],
            'nis'   => $data['nis'],
            'class' => $data['class'],
            'email' => strtolower($data['email'] ?? '') ?: $user->email,
        ]);
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return redirect()->route('admin.members.index')->with('success', 'Data anggota diperbarui.');
    }

    public function destroy(User $user)
    {
        abort_unless($user->role === 'siswa', 404);

        if ($user->loans()->whereIn('status', ['pending', 'approved', 'active'])->exists()) {
            return back()->with('error', 'Anggota ini masih punya peminjaman/pengajuan yang berjalan.');
        }

        $user->delete();

        return redirect()->route('admin.members.index')->with('success', 'Anggota dihapus.');
    }

    public function template()
    {
        return Csv::download('template-anggota.csv',
            ['nama', 'nis', 'kelas', 'email', 'password'],
            [['Budi Santoso', '12345', 'X RPL 1', '', '']]
        );
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|extensions:csv,txt|max:4096'], Msg::id(), Msg::attributes());
        set_time_limit(0);

        $added = 0;
        $skipped = [];

        foreach (Csv::read($request->file('file')->getRealPath()) as $i => $row) {
            $line  = $i + 2;
            $name  = Csv::pick($row, ['nama', 'name']);
            $nis   = Csv::pick($row, ['nis']);
            $class = Csv::pick($row, ['kelas', 'class']);
            $email = strtolower(Csv::pick($row, ['email']));

            if ($name === '' || $nis === '' || $class === '') {
                $skipped[] = "baris {$line} (nama/NIS/kelas kosong)";
                continue;
            }
            if (User::where('nis', $nis)->exists()) {
                $skipped[] = "baris {$line} (NIS {$nis} sudah terdaftar)";
                continue;
            }

            $email = $email ?: $this->fallbackEmail($nis);
            if (User::where('email', $email)->exists()) {
                $skipped[] = "baris {$line} (email {$email} sudah dipakai)";
                continue;
            }

            User::create([
                'name'     => mb_substr($name, 0, 150),
                'nis'      => $nis,
                'class'    => mb_substr($class, 0, 50),
                'email'    => $email,
                // rounds 10 supaya impor ratusan siswa tidak lambat
                'password' => Hash::make(Csv::pick($row, ['password', 'kata sandi']) ?: $nis, ['rounds' => min(10, (int) config('hashing.bcrypt.rounds', 12))]),
                'role'     => 'siswa',
            ]);
            $added++;
        }

        $redirect = redirect()->route('admin.members.index')
            ->with('success', "{$added} anggota berhasil diimpor. Kata sandi awal = NIS (kecuali kolom password diisi).");

        if ($skipped) {
            $redirect->with('error', count($skipped).' baris dilewati: '.implode('; ', array_slice($skipped, 0, 6)).(count($skipped) > 6 ? '; ...' : ''));
        }

        return $redirect;
    }
}
