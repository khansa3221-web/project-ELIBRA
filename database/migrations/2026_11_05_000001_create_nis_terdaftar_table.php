<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Daftar NIS siswa yang BOLEH mendaftar. Pendaftaran ditolak bila NIS tidak ada di tabel ini.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('nis_terdaftar')) {
            Schema::create('nis_terdaftar', function (Blueprint $table) {
                $table->id();
                $table->string('nis', 30)->unique();
                $table->string('nama', 150);
                $table->timestamps();
            });
        }

        $now = now();
        foreach ([
            ["24088", "ABROR ABIYYI"],
            ["24089", "AL BANIE SETYAWAN"],
            ["24090", "ALDEJAN KOVIC PUTRA SULASH"],
            ["24091", "ALDO FELICIA PRATAMA"],
            ["24092", "ALFIKO DAFA ARFANSYAH"],
            ["24093", "ALIIMMA AFILLA PRIMA"],
            ["24094", "ANDREAS DWI PRAKASA"],
            ["24095", "ANSHAR DEAS ALIF DZULHIJJAH"],
            ["24096", "ARDELIA ANGGUN SAPUTRI"],
            ["24097", "ARFATH YOAN NDARU"],
            ["24099", "AUREL KHAIRA LUBNA SUTRISNO"],
            ["24100", "BAGOES VERNANDA"],
            ["24101", "BIMO NUGROHO"],
            ["24102", "DHANI ARRGIAWAN WIDIYATMOKO"],
            ["24103", "FAHRI FERDYANSYAH"],
            ["24105", "IBRA AL TABIAN"],
            ["24106", "KHANSA KHAIRUNNISA"],
            ["24107", "KHANSA QURRATU'AIN"],
            ["24108", "MARISA APRILYA HAPSARI"],
            ["24109", "MU'AMMAR AKYAS"],
            ["24110", "MUCHAMMAD NAUFAL FARIZ"],
            ["24111", "MUHAMMAD FINZA MUTA'ALI"],
            ["24112", "NAUFAAL ZAIDAN AUFAA CHANDRA"],
            ["24113", "NOUFAL ROZZAK ABIZARD"],
            ["24114", "RAFIF BARI MUZHAFIR"],
            ["24116", "RASHEESA SHAREEF AHMADI"],
            ["24117", "ROSYIDAH ALIF HAKIMAH"],
            ["24118", "SALMAN ALFARIZI BASHEN"],
            ["24119", "TSABITAH QURRATA A'YUN"],
            ["24120", "VENSKA FELLICIA PERTIWI"],
            ["24121", "YURI AULIA WIDYADANA"]
        ] as [$nis, $nama]) {
            DB::table('nis_terdaftar')->updateOrInsert(['nis' => $nis], ['nama' => $nama, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('nis_terdaftar');
    }
};
