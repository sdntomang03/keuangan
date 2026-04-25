<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KegiatanManualController extends Controller
{
    public function createImport()
    {
        // Mengambil daftar sumber dana milik sekolah user atau yang global
        $sumberDanas = \App\Models\SumberDanaManual::where('school_id', auth()->user()->sekolah_id)
            ->orWhereNull('school_id')
            ->get();

        return view('kegiatan.import', compact('sumberDanas'));
    }

    // Tambahkan di KegiatanManualController.php

    public function indexSumberDana()
    {
        $sumberDanas = \App\Models\SumberDanaManual::where('school_id', auth()->user()->sekolah_id)
            ->orWhereNull('school_id')
            ->get();

        return view('kegiatan.sumber-dana', compact('sumberDanas'));
    }

    public function storeSumberDana(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'tahun' => 'required|integer',
        ]);

        \App\Models\SumberDanaManual::create([
            'school_id' => auth()->user()->sekolah_id,
            'kode' => strtoupper($request->kode),
            'nama' => $request->nama,
            'tahun' => $request->tahun,

        ]);

        return back()->with('success', 'Sumber dana berhasil ditambahkan!');
    }

    public function storeImport(Request $request)
    {
        // 1. Validasi File dan Pilihan Sumber Dana
        $request->validate([
            'file_json' => 'required|file|max:5120',
            'sumber_dana_id' => 'required', // Tambahkan validasi pilihan sumber dana
        ], [
            'file_json.required' => 'Anda belum memilih file JSON.',
            'sumber_dana_id.required' => 'Pilih sumber dana terlebih dahulu.',
        ]);

        $schoolId = auth()->user()->sekolah_id;

        if (! $schoolId) {
            return back()->withErrors(['error' => 'Gagal mengidentifikasi sekolah.']);
        }

        // Ambil NAMA sumber dana berdasarkan ID yang dipilih user
        $sumberDana = \App\Models\SumberDanaManual::find($request->sumber_dana_id);
        $namaSumberDana = $sumberDana ? $sumberDana->nama : 'Lainnya';

        $file = $request->file('file_json');
        $jsonContent = file_get_contents($file->getPathname());
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
            return back()->withErrors(['error' => 'Format file JSON tidak valid.']);
        }

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($data as $item) {
                if (empty($item['id_kegiatan'])) {
                    continue;
                }

                \App\Models\KegiatanManual::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'id_kegiatan' => trim($item['id_kegiatan']),
                    ],
                    [
                        'standar_pendidikan' => trim($item['standar_pendidikan'] ?? '-'),
                        'sumber_dana' => $namaSumberDana, // Menggunakan pilihan dari Form, bukan dari JSON
                        'nama_kegiatan' => trim($item['nama_kegiatan'] ?? '-'),
                    ]
                );
                $count++;
            }

            DB::commit();

            return back()->with('success', "Berhasil mengimpor $count data kegiatan dengan Sumber Dana: $namaSumberDana");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal mengimpor data: '.$e->getMessage()]);
        }
    }

    public function daftarKegiatan()
    {
        $schoolId = auth()->user()->sekolah_id ?? auth()->user()->school_id;

        // 1. Ambil daftar kegiatan dengan Eager Loading Relasi
        // Kita tambahkan with([...]) agar data Program, Sub Program, dan Uraian terbaca
        $kegiatan = \App\Models\KegiatanManual::with([
            'program',
            'subProgram',
            'sumberDana',
        ])
            ->where('school_id', $schoolId)
            ->withSum('rkasManuals as total_anggaran', 'total_akhir') // Beri alias agar mudah dipanggil
            ->orderBy('id_kegiatan', 'asc')
            ->get();

        // 2. Buat Rekapitulasi Anggaran per Tahun dan Sumber Dana
        $rekapAnggaran = \Illuminate\Support\Facades\DB::table('rkas_manuals')
            ->join('sumber_dana_manuals', 'rkas_manuals.sumber_dana_id', '=', 'sumber_dana_manuals.id')
            ->where('rkas_manuals.school_id', $schoolId)
            ->select(
                'rkas_manuals.tahun_anggaran',
                'sumber_dana_manuals.nama as sumber_dana',
                \Illuminate\Support\Facades\DB::raw('SUM(rkas_manuals.total_akhir) as total_anggaran')
            )
            ->groupBy('rkas_manuals.tahun_anggaran', 'sumber_dana_manuals.nama')
            ->orderBy('rkas_manuals.tahun_anggaran', 'desc')
            ->get();

        return view('kegiatan.daftar_kegiatan', compact('kegiatan', 'rekapAnggaran'));
    }

    public function tambahKomponen($id)
    {
        // 1. Ambil data kegiatan dengan Eager Loading agar relasi Program, Sub, dan Uraian langsung terbaca
        $kegiatan = \App\Models\KegiatanManual::with(['program', 'subProgram', 'sumberDana'])
            ->findOrFail($id);

        $schoolId = auth()->user()->sekolah_id;

        // 2. Keamanan: Pastikan user tidak mengintip data sekolah lain
        if ($kegiatan->school_id != $schoolId) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        // 3. Ambil Kode Rekening (Korek) yang hanya memiliki komponen di sekolah tersebut
        // Menggunakan whereHas agar dropdown Korek hanya menampilkan yang ada isinya saja
        $koreks = \App\Models\Korek::whereHas('komponenManuals', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })->get();

        // 4. Ambil Daftar RKAS yang sudah diinput untuk kegiatan ini
        // Pastikan kolom menggunakan 'kegiatan_manual_id' sesuai migrasi terbaru
        $rincianRkas = \App\Models\RkasManual::with(['sumberDana', 'komponenManual'])
            ->where('kegiatan_manual_id', $id)
            ->where('school_id', $schoolId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('kegiatan.tambah-komponen', compact('kegiatan', 'koreks', 'rincianRkas'));
    }

    public function destroyKomponen($kegiatan_id, $rkas_id)
    {
        $schoolId = auth()->user()->sekolah_id ?? auth()->user()->school_id;
        $rkas = \App\Models\RkasManual::where('id', $rkas_id)->where('school_id', $schoolId)->firstOrFail();

        $rkas->delete();

        return back()->with('success', 'Rincian komponen berhasil dihapus dari RKAS.');
    }

    // Method baru untuk mengambil komponen via AJAX
    public function getKomponenByKorek(Request $request)
    {
        $schoolId = auth()->user()->sekolah_id;

        $komponen = \App\Models\KomponenManual::where('school_id', $schoolId)
            ->where('korek_id', $request->korek_id)
            ->get();

        return response()->json($komponen);
    }

    public function storeKomponen(\Illuminate\Http\Request $request, $id)
    {
        $request->validate([
            'uraian_id' => 'required|exists:uraian_kegiatans,id',
            'nama_rincian' => 'required|string|max:255',
            'rincian' => 'required|array|min:1',
            'rincian.*.komponen_manual_id' => 'required',
            'rincian.*.nama_komponen' => 'required',
            'rincian.*.harga_satuan' => 'required|numeric|min:0',
            'rincian.*.volume' => 'required|integer|min:1',
            'rincian.*.keterangan' => 'required|string',
        ]);

        $schoolId = auth()->user()->sekolah_id ?? auth()->user()->school_id;
        $kegiatan = \App\Models\KegiatanManual::findOrFail($id);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // 1. CARI ATAU BUAT MASTER RINCINAN BARU
            // Ini memastikan Bapak hanya menyimpan ID di tabel RKAS
            $masterRincian = \App\Models\RincianKegiatan::firstOrCreate([
                'kegiatan_manual_id' => $kegiatan->id,
                'nama_rincian' => trim($request->nama_rincian),
            ]);

            foreach ($request->rincian as $item) {
                $komponen = \App\Models\KomponenManual::findOrFail($item['komponen_manual_id']);
                $subTotal = $item['harga_satuan'] * $item['volume'];
                $persenPpn = $request->has('pakai_ppn') ? 0.12 : 0;
                $nilaiPpn = $subTotal * $persenPpn;

                \App\Models\RkasManual::create([
                    'school_id' => $schoolId,
                    'tahun_anggaran' => $kegiatan->sumberDana->tahun,
                    'sumber_dana_id' => $kegiatan->sumber_dana_id,
                    'kegiatan_manual_id' => $kegiatan->id,

                    // SIMPAN DALAM BENTUK ID (Bukan teks)
                    'uraian_id' => $request->uraian_id,
                    'rincian_kegiatan_id' => $masterRincian->id,

                    'korek_id' => $komponen->korek_id,
                    'komponen_manual_id' => $komponen->id,
                    'nama_komponen' => $item['nama_komponen'],
                    'spesifikasi' => $komponen->spesifikasi,
                    'satuan' => $komponen->satuan,
                    'harga_satuan' => $item['harga_satuan'],
                    'volume' => $item['volume'],
                    'jumlah_harga' => $subTotal,
                    'ppn' => $nilaiPpn,
                    'total_akhir' => $subTotal + $nilaiPpn,
                    'keterangan' => $item['keterangan'],
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();

            return back()->with('success', 'Rincian berhasil tertaut ke master dan tersimpan di RKAS.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();

            return back()->withErrors(['error' => 'Gagal menyimpan: '.$e->getMessage()]);
        }
    }

    public function checkKomponenDuplicate(Request $request, $id)
    {
        // Gunakan fallback agar lebih aman (sekolah_id atau school_id)
        $schoolId = auth()->user()->sekolah_id ?? auth()->user()->school_id;
        $komponenIds = $request->komponen_ids; // Menerima array ID Komponen

        // Cari rincian RKAS yang sudah ada untuk kegiatan ini dan komponen yang dipilih
        $existingRkas = \App\Models\RkasManual::where('kegiatan_manual_id', $id) // <-- PERBAIKAN DI SINI
            ->where('school_id', $schoolId)
            ->whereIn('komponen_manual_id', $komponenIds)
            ->pluck('nama_komponen')
            ->unique();

        if ($existingRkas->isNotEmpty()) {
            return response()->json([
                'is_duplicate' => true,
                'names' => $existingRkas->implode(', '),
            ]);
        }

        return response()->json(['is_duplicate' => false]);
    }

    public function createImportKomponen()
    {
        return view('kegiatan.import_komponen');
    }

    // Memproses file JSON
    public function storeImportKomponen(Request $request)
    {
        // Validasi file sebagai array
        $request->validate([
            'file_json' => 'required|array',
            'file_json.*' => 'required|file|mimetypes:application/json,text/plain|max:10240', // Maks 10MB per file
        ], [
            'file_json.required' => 'Anda belum memilih file JSON.',
            'file_json.*.mimetypes' => 'Pastikan semua file yang diunggah berformat JSON.',
        ]);

        $schoolId = auth()->user()->sekolah_id ?? auth()->user()->school_id;

        if (! $schoolId) {
            return back()->withErrors(['error' => 'Gagal mengidentifikasi sekolah.']);
        }

        $files = $request->file('file_json');
        $countTotal = 0;
        $korekNotFound = [];

        DB::beginTransaction();
        try {
            // Looping untuk setiap file yang diunggah
            foreach ($files as $file) {
                $jsonContent = file_get_contents($file->getPathname());
                $data = json_decode($jsonContent, true);

                // Validasi format JSON per file
                if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
                    throw new \Exception('Format file JSON tidak valid pada file: '.$file->getClientOriginalName());
                }

                // Jika JSON berupa 1 objek tunggal, bungkus menjadi array
                if (isset($data['kode_rekening'])) {
                    $data = [$data];
                }

                // Looping data rekening di dalam file tersebut
                foreach ($data as $grupRekening) {
                    $uraianRekening = $grupRekening['uraian_rekening'] ?? '';
                    $kodeRekening = $grupRekening['kode_rekening'] ?? '';

                    $korek = \App\Models\Korek::where('uraian_singkat', $uraianRekening)->first();

                    if (! $korek) {
                        $korekNotFound[] = $kodeRekening.' - '.$uraianRekening;

                        continue; // Lanjut ke rekening berikutnya
                    }

                    if (isset($grupRekening['daftar_komponen']) && is_array($grupRekening['daftar_komponen'])) {
                        foreach ($grupRekening['daftar_komponen'] as $komp) {
                            \App\Models\KomponenManual::updateOrCreate(
                                [
                                    'school_id' => $schoolId,
                                    'id_komponen' => trim($komp['id_komponen']),
                                ],
                                [
                                    'korek_id' => $korek->id,
                                    'nama' => trim($komp['nama'] ?? '-'),
                                    'spesifikasi' => trim($komp['spesifikasi'] ?? null),
                                    'satuan' => trim($komp['satuan'] ?? null),
                                    'harga' => str_replace(['.', ','], '', $komp['harga'] ?? 0),
                                ]
                            );
                            $countTotal++;
                        }
                    }
                }
            }

            DB::commit();

            // Membuat pesan notifikasi akhir
            $pesanSukses = "Berhasil mengimpor $countTotal data komponen dari ".count($files).' file.';
            if (count($korekNotFound) > 0) {
                $pesanSukses .= ' Namun, beberapa komponen dilewati karena Rekening berikut tidak ditemukan di Master Korek: '.implode(', ', array_unique($korekNotFound));
            }

            return back()->with('success', $pesanSukses);

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal mengimpor data: '.$e->getMessage()]);
        }
    }

    // Method untuk Update Multi Komponen
    public function updateMultiKomponen(Request $request, $id)
    {
        // Validasi
        $request->validate([
            'edit_rincian' => 'required|array|min:1',
            'edit_rincian.*.id' => 'required|exists:rkas_manuals,id',
            'edit_rincian.*.volume' => 'required|integer|min:1',
            'edit_rincian.*.keterangan' => 'required|string',
        ]);

        $schoolId = auth()->user()->sekolah_id ?? auth()->user()->school_id;
        $kegiatan = \App\Models\KegiatanManual::findOrFail($id);

        if ($kegiatan->school_id != $schoolId) {
            abort(403, 'Akses ditolak.');
        }

        // Cek PPN Global untuk Edit
        $pakaiPpn = $request->has('edit_pakai_ppn');
        $persenPpn = $pakaiPpn ? 0.12 : 0;

        DB::beginTransaction();
        try {
            foreach ($request->edit_rincian as $item) {
                // Ambil data RKAS yang akan diupdate
                $rkas = \App\Models\RkasManual::where('id', $item['id'])
                    ->where('school_id', $schoolId)
                    ->firstOrFail();

                // Hitung ulang berdasarkan volume baru
                $subTotal = $rkas->harga_satuan * $item['volume'];
                $nilaiPpn = $subTotal * $persenPpn;
                $totalAkhir = $subTotal + $nilaiPpn;

                // Update data
                $rkas->update([
                    'volume' => $item['volume'],
                    'jumlah_harga' => $subTotal,
                    'ppn' => $nilaiPpn,
                    'total_akhir' => $totalAkhir,
                    'keterangan' => $item['keterangan'],
                ]);
            }

            DB::commit();

            return back()->with('success', 'Berhasil memperbarui '.count($request->edit_rincian).' rincian komponen.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Gagal memperbarui rincian: '.$e->getMessage()]);
        }
    }

    public function destroyMultiKomponen(\Illuminate\Http\Request $request, $id)
    {
        // Mendekode JSON array ID yang dikirim oleh Javascript tadi
        $ids = json_decode($request->ids, true);

        if (is_array($ids) && count($ids) > 0) {
            // Hapus semua rincian yang ID-nya ada di dalam array tersebut
            \App\Models\RkasManual::whereIn('id', $ids)->delete();

            return back()->with('success', count($ids).' rincian berhasil dihapus dari RKAS.');
        }

        return back()->withErrors(['error' => 'Tidak ada data rincian yang dipilih untuk dihapus.']);
    }

    /**
     * Menampilkan Form Tambah Kegiatan
     */
    public function create()
    {
        $schoolId = auth()->user()->sekolah_id ?? auth()->user()->school_id;

        // Ambil data untuk dropdown
        $programs = \App\Models\Program::orderBy('nama_program', 'asc')->get();
        $sumberDanas = \App\Models\SumberDanaManual::where('school_id', $schoolId)
            ->orWhereNull('school_id')
            ->orderBy('nama', 'asc')
            ->get();

        return view('kegiatan.create', compact('programs', 'sumberDanas'));
    }

    /**
     * Endpoint untuk AJAX Dropdown Sub Program
     */
    public function getSubPrograms(\Illuminate\Http\Request $request)
    {
        $subPrograms = \App\Models\SubProgram::where('program_id', $request->program_id)
            ->orderBy('nama_sub_program', 'asc')
            ->get();

        return response()->json($subPrograms);
    }

    /**
     * Menyimpan Data Kegiatan Baru
     */
    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'sumber_dana_id' => 'required|exists:sumber_dana_manuals,id',
            'program_id' => 'required|exists:programs,id',
            'sub_program_id' => 'required|exists:sub_programs,id',
            'id_kegiatan' => 'nullable|string|max:50',
        ]);

        $schoolId = auth()->user()->sekolah_id ?? auth()->user()->school_id;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // 1. Logika Pembuatan ID Kegiatan Acak
            $idKegiatan = $request->id_kegiatan;

            if (empty($idKegiatan)) {
                do {
                    // Generate acak: Contoh KGT-A1B2C3
                    $randomCode = strtoupper(\Illuminate\Support\Str::random(6));
                    $idKegiatan = 'KGT-'.$randomCode;

                    // Cek di database apakah ID ini sudah ada untuk sekolah ini
                    $exists = \App\Models\KegiatanManual::where('school_id', $schoolId)
                        ->where('id_kegiatan', $idKegiatan)
                        ->exists();
                } while ($exists); // Ulangi jika ID tidak sengaja sama
            }

            // 3. Simpan ke tabel kegiatan_manuals
            \App\Models\KegiatanManual::create([
                'school_id' => $schoolId,
                'program_id' => $request->program_id,
                'sub_program_id' => $request->sub_program_id,
                'sumber_dana_id' => $request->sumber_dana_id,
                'id_kegiatan' => $idKegiatan,
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('kegiatan.index')
                ->with('success', "Kegiatan baru dengan ID $idKegiatan berhasil ditambahkan.");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();

            return back()->withErrors(['error' => 'Gagal menyimpan kegiatan: '.$e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        $schoolId = auth()->user()->sekolah_id ?? auth()->user()->school_id;
        $kegiatan = \App\Models\KegiatanManual::where('id', $id)->where('school_id', $schoolId)->firstOrFail();

        $kegiatan->delete();

        return back()->with('success', 'Kegiatan beserta seluruh rincian anggarannya berhasil dihapus.');
    }

    public function rekapAnggaran(\Illuminate\Http\Request $request)
    {
        $listSumberDana = \App\Models\SumberDanaManual::orderBy('tahun', 'desc')->orderBy('nama', 'asc')->get();
        $sumberDanaId = $request->query('sumber_dana_id');

        $rekap = [];
        $rekapRekening = []; // <-- WADAH BARU UNTUK REKAP KODE REKENING
        $grandTotal = 0;
        $sumberDana = null;

        if ($sumberDanaId) {
            $sumberDana = \App\Models\SumberDanaManual::findOrFail($sumberDanaId);

            $rkasData = \App\Models\RkasManual::with([
                'kegiatanManual.program',
                'korek',
            ])
                ->where('sumber_dana_id', $sumberDanaId)
                ->get();

            foreach ($rkasData as $rkas) {
                $namaProgram = $rkas->kegiatanManual->program->nama_program ?? 'Program Tidak Diketahui';
                $kodeRekening = $rkas->korek
                    ? $rkas->korek->ket
                    : 'Rekening Tidak Diketahui';

                // 1. REKAP BERDASARKAN PROGRAM
                if (! isset($rekap[$namaProgram])) {
                    $rekap[$namaProgram] = [
                        'total_program' => 0,
                        'rekening' => [],
                    ];
                }
                if (! isset($rekap[$namaProgram]['rekening'][$kodeRekening])) {
                    $rekap[$namaProgram]['rekening'][$kodeRekening] = 0;
                }

                $rekap[$namaProgram]['rekening'][$kodeRekening] += $rkas->total_akhir;
                $rekap[$namaProgram]['total_program'] += $rkas->total_akhir;

                // 2. REKAP BERDASARKAN KODE REKENING MURNI
                if (! isset($rekapRekening[$kodeRekening])) {
                    $rekapRekening[$kodeRekening] = 0;
                }
                $rekapRekening[$kodeRekening] += $rkas->total_akhir;

                $grandTotal += $rkas->total_akhir;
            }

            // Urutkan rekap rekening berdasarkan abjad/nomor kode
            ksort($rekapRekening);
        }

        // Jangan lupa tambahkan $rekapRekening ke dalam compact()
        return view('kegiatan.rekap_program_rekening', compact('rekap', 'rekapRekening', 'sumberDana', 'grandTotal', 'listSumberDana', 'sumberDanaId'));
    }
}
