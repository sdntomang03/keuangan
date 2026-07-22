<?php

namespace App\Http\Controllers;

use App\Imports\BarangImport;
use App\Models\Barang;
use App\Models\Komponen;
use App\Models\Korek;
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
        $barangs = $query->paginate(50);

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

    // Halaman Pencarian Komponen (Mendukung Server-Side DataTables)
    public function CariKomponen(Request $request)
    {
        // 1. Jika Request adalah AJAX (Dari DataTables)
        if ($request->ajax()) {
            $query = Komponen::query(); // Hapus with('korek') jika tidak ditampilkan untuk menghemat query

            // Filter Berdasarkan Dropdown Kode Rekening
            if ($request->filled('kode_rekening')) {
                $query->where('kode_rekening', $request->kode_rekening);
            }

            // Hitung Total Data sebelum pencarian (Wajib untuk DataTables)
            $recordsTotal = $query->count();

            // Filter Pencarian dari Search Box DataTables
            $searchValue = $request->input('search.value');
            if ($searchValue) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('namakomponen', 'like', '%'.$searchValue.'%')
                        ->orWhere('spek', 'like', '%'.$searchValue.'%')
                        ->orWhere('kode_rekening', 'like', '%'.$searchValue.'%');
                });
            }

            // Hitung Total Data setelah filter pencarian
            $recordsFiltered = $query->count();

            // Fitur Pengurutan (Sorting) dari klik Header Tabel
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir', 'asc');
            // Pastikan urutan array ini sama persis dengan urutan <th> di HTML
            $columns = ['kode_rekening', 'namakomponen', 'spek', 'satuan', 'harga', 'tahun'];

            if (! is_null($orderColumnIndex) && isset($columns[$orderColumnIndex])) {
                $query->orderBy($columns[$orderColumnIndex], $orderDirection);
            } else {
                $query->orderBy('id', 'desc'); // Default urutan jika tidak diklik
            }

            // Fitur Pagination (Paging limit & offset)
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            if ($length > 0) {
                $query->offset($start)->limit($length);
            }

            // Ambil Data
            $data = $query->get();

            // Format Data Array untuk dikembalikan sebagai JSON
            $formattedData = [];
            foreach ($data as $item) {
                $formattedData[] = [
                    'kode_rekening' => $item->kode_rekening,
                    'namakomponen' => $item->namakomponen,
                    'spek' => $item->spek,
                    // Kita bisa menyisipkan tag HTML langsung dari Controller
                    'satuan' => '<span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-medium">'.$item->satuan.'</span>',
                    'harga' => 'Rp '.number_format($item->harga, 0, ',', '.'),
                    'tahun' => $item->tahun,
                ];
            }

            // Return JSON sesuai standar format DataTables
            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $formattedData,
            ]);
        }

        // 2. Jika Request Biasa (Load Halaman Pertama Kali)
        $koreks = Korek::orderBy('kode', 'asc')->get();

        // Perhatikan kita tidak lagi me-load $komponens di sini karena akan di-load via AJAX
        return view('komponen.index', compact('koreks'));
    }

    // Halaman Form Import
    public function createImport()
    {
        $koreks = Korek::orderBy('kode', 'asc')->get();

        return view('komponen.import', compact('koreks'));
    }

    // Proses Import JSON
    public function storeImport(Request $request)
    {
        $request->validate([
            'json_files' => 'required',
            'json_files.*' => 'mimes:json,txt',
            'tahun' => 'required|digits:4',
        ]);

        $count = 0;

        foreach ($request->file('json_files') as $file) {
            // Ambil data JSON dari file
            $jsonContent = file_get_contents($file->getRealPath());
            $data = json_decode($jsonContent, true);

            // Coba ambil kode rekening dari nama file (Format: 5.1.02...._Nama.json)
            $filename = $file->getClientOriginalName();
            preg_match('/^[0-9\.]+/', $filename, $matches);
            $kodeRekeningFile = $matches[0] ?? null;

            // Gunakan kode dari nama file, ATAU dari input form jika tidak terdeteksi
            $kodeRekening = $kodeRekeningFile ?: $request->kode_rekening;

            // Pastikan format JSON sesuai (memiliki array 'data')
            if (isset($data['data']) && is_array($data['data'])) { // [cite: 2]
                foreach ($data['data'] as $item) { // [cite: 2]
                    // Gunakan updateOrCreate untuk menghindari data ganda
                    Komponen::updateOrCreate(
                        [
                            'idkomponen' => $item['idkomponen'], // [cite: 2]
                            'tahun' => $request->tahun,
                        ],
                        [
                            'kode_rekening' => $kodeRekening,
                            'namakomponen' => $item['namakomponen'], // [cite: 2]
                            'spek' => $item['spek'], // [cite: 2]
                            'satuan' => $item['satuan'], // [cite: 2]
                            'harga' => $item['harga'], // [cite: 2]
                        ]
                    );
                    $count++;
                }
            }
        }

        return redirect()->route('komponenrkas.index')->with('success', "$count komponen berhasil di-import!");
    }
}
