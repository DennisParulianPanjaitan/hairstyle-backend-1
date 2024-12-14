<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class HaircutController extends Controller
{
  public function setHaircut(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string',
      'description' => 'required|string',
    ]);

    try {
      DB::table('haircut')->insert($validated);
    } catch (\Exception $e) {

    }

    return response()->json([
      'success' => true,
      'message' => 'Gaya rambut berhasil ditambahkan'
    ], 201);
  }

  public function getHaircut(Request $request)
  {
    try {
      // Mengambil data dari tabel `haircut` dan `haircut_images`
      $haircutlist = DB::table('haircut')
        ->join('haircut_images', 'haircut.id', '=', 'haircut_images.hair_style_id')
        ->select('haircut.id', 'haircut.name', 'haircut.description', 'haircut_images.image_url')
        ->get();
    } catch (\Throwable $th) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal mendapatkan daftar gaya rambut',
        'error' => $th->getMessage()
      ], 500);
    }

    // Mengelompokkan gambar berdasarkan `hair_style_id`
    $groupedHaircuts = $haircutlist->groupBy('id');

    // Memformat data menjadi array yang sesuai dengan struktur JSON yang diinginkan
    $result = $groupedHaircuts->map(function ($group) {
      $haircut = $group->first(); // Ambil data pertama untuk nama, deskripsi, dll
      return [
        'id' => $haircut->id,
        'name' => $haircut->name,
        'description' => $haircut->description,
        'images' => $group->map(function ($image) {
          return [
            'id' => $image->id,
            'image_url' => $image->image_url,
          ];
        })->toArray()
      ];
    });

    return response()->json([
      'success' => true,
      'message' => 'Data semua gaya rambut berhasil didapatkan',
      'data' => $result
    ], 200);
  }



  public function updateHaircut(Request $request)
  {
    $validated = $request->validate([
      'id' => 'int',
      'name' => 'required|string',
      'description' => 'required|string',
    ]);

    // Simpan data ke database
    try {
      DB::table('haircut')
        ->where('id', $validated['id'])
        ->update([
          'name' => $validated['name'],
          'description' => $validated['description'],
        ]);
    } catch (\Exception $e) {
      // Jika terjadi error saat insert
      return response()->json([
        'success' => false,
        'message' => 'Gagal memperbarui data gaya rambut',
        'error' => $e->getMessage()
      ], 500);
    }

    return response()->json([
      'success' => true,
      'message' => 'Data gaya rambut berhasil diperbarui',
    ], 200);
  }
  public function destroyHaircut(Request $request)
  {

  }
}
?>