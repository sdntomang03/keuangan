<?php

namespace App\Http\Controllers;

use App\Imports\BarangImport;
use App\Models\Barang;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BarangController extends Controller
{
    /**
     * 1. Menampilkan halaman View (Form Import & Fitur Pencarian)
     */
    public function index()
    {
        // Mengambil daftar unik untuk filter
        $kategoriList = Barang::select('kategori')->distinct()->pluck('kategori');
        $kodeBelanjaList = Barang::select('kode_belanja')->distinct()->pluck('kode_belanja');

        // Mengambil daftar unik kode rekening beserta namanya untuk dropdown searchable
        $rekeningList = Barang::select('kode_rekening', 'nama_rekening')->distinct()->get();

        return view('barang.index', compact('kategoriList', 'kodeBelanjaList', 'rekeningList'));
    }

    /**
     * 2. Memproses Upload File JSON dari View
     */
    public function import(Request $request)
    {
        $request->validate([
            'file_import' => 'required|file', // Hapus batasan max size jika ingin menerima file sangat besar (atau set max:51200 untuk 50MB)
        ]);

        // Tingkatkan batas waktu dan memori eksekusi khusus untuk proses ini
        ini_set('max_execution_time', 600); // 10 Menit
        ini_set('memory_limit', '512M');    // 512 MB

        try {
            $file = $request->file('file_import');
            $extension = strtolower($file->getClientOriginalExtension());

            if ($extension === 'json') {
                $jsonContent = file_get_contents($file->getRealPath());
                $data = json_decode($jsonContent, true);
                $items = isset($data['data']) ? $data['data'] : $data;

                if (! is_array($items) || empty($items)) {
                    return back()->with('error', 'Format JSON tidak valid atau data kosong.');
                }

                $formattedItems = [];
                foreach ($items as $item) {
                    if (empty($item['id_barang']) && empty($item['ID Barang'])) {
                        continue;
                    }

                    $formattedItems[] = [
                        'id_barang' => (string) ($item['id_barang'] ?? $item['ID Barang'] ?? null),
                        'kode_rekening' => (string) ($item['kode_rekening'] ?? $item['Kode Rekening'] ?? null),
                        'nama_rekening' => (string) ($item['nama_rekening'] ?? $item['Nama Rekening'] ?? null),
                        'nama_barang' => (string) ($item['nama_barang'] ?? $item['Nama Barang'] ?? null),
                        'satuan' => (string) ($item['satuan'] ?? $item['Satuan'] ?? null),
                        'harga_barang' => (int) ($item['harga_barang'] ?? $item['Harga Barang'] ?? 0),
                        'harga_minimal' => (int) ($item['harga_minimal'] ?? $item['Harga Minimal'] ?? 0),
                        'harga_maksimal' => (int) ($item['harga_maksimal'] ?? $item['Harga Maksimal'] ?? 0),
                        'kode_belanja' => (string) ($item['kode_belanja'] ?? $item['Kode Belanja'] ?? null),
                        'kategori' => (string) ($item['kategori'] ?? $item['Kategori'] ?? '-'),
                        'digunakan_rkas' => filter_var($item['digunakan_rkas'] ?? $item['Digunakan RKAS'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    ];
                }

                if (empty($formattedItems)) {
                    return back()->with('error', 'Tidak ada data valid yang bisa diproses.');
                }

                // CHUNKING UNTUK JSON:
                // Pecah array raksasa menjadi potongan-potongan kecil berisi 1.000 data
                $chunks = array_chunk($formattedItems, 1000);

                foreach ($chunks as $chunk) {
                    Barang::upsert($chunk, ['id_barang'], [
                        'kode_rekening', 'nama_rekening', 'nama_barang', 'satuan',
                        'harga_barang', 'harga_minimal', 'harga_maksimal',
                        'kode_belanja', 'kategori', 'digunakan_rkas',
                    ]);
                }

            } elseif (in_array($extension, ['xlsx', 'xls', 'csv'])) {
                // Laravel Excel secara otomatis menggunakan konfigurasi Chunking dari class BarangImport
                Excel::import(new BarangImport, $file);
            } else {
                return back()->with('error', 'Format file tidak didukung! Gunakan JSON, XLSX, atau CSV.');
            }

            return back()->with('success', 'Data komponen berskala besar berhasil diimpor!');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error Import Barang: '.$e->getMessage());

            return back()->with('error', 'Terjadi kesalahan sistem saat memproses file: '.substr($e->getMessage(), 0, 150));
        }
    }

    /**
     * 3. API Untuk Pencarian Real-Time (Digunakan oleh Alpine.js)
     */
    public function search(Request $request)
    {
        $nama_barang = $request->query('nama_barang');
        $kode_rekening = $request->query('kode_rekening');
        $kode_belanja = $request->query('kode_belanja'); // Berupa string yang dipisahkan koma
        $kategori = $request->query('kategori');

        $query = Barang::query();

        if (! empty($nama_barang)) {
            $query->where('nama_barang', 'like', "%{$nama_barang}%");
        }

        if (! empty($kode_rekening)) {
            $query->where('kode_rekening', $kode_rekening);
        }

        // Filter Checklist Kode Belanja (whereIn)
        if (! empty($kode_belanja)) {
            $kodeArray = explode(',', $kode_belanja);
            $query->whereIn('kode_belanja', $kodeArray);
        }

        if (! empty($kategori)) {
            $query->where('kategori', $kategori);
        }

        $query->orderBy('harga_barang', 'asc');
        $barangs = $query->limit(50)->get();

        return response()->json($barangs);
    }

    /**
     * Mengosongkan seluruh data di tabel barangs
     */
    public function truncate()
    {
        try {
            // Perintah truncate() akan menghapus semua data dan mereset ID (Auto Increment) kembali ke 1
            Barang::truncate();

            return back()->with('success', 'Semua data komponen barang berhasil dikosongkan!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error Kosongkan Barang: '.$e->getMessage());

            return back()->with('error', 'Terjadi kesalahan sistem saat mencoba mengosongkan data.');
        }
    }
}
