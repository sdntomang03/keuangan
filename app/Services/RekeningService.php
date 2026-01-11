<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RekeningService
{
    protected $map = [];

    public function __construct()
    {
        // Ambil data dari tabel koreks: kode sebagai key, id sebagai value
        $this->map = DB::table('koreks')->pluck('id', 'kode')->toArray();
    }

    public function getIdByKode($kode)
    {
        return $this->map[$kode] ?? null;
    }
}
