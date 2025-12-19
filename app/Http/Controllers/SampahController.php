<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SampahController extends Controller
{
    public function index()
    {
        return view('sampah');
    }

    public function cekSampah(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5048',
        ]);

        // 2. Ambil File
        $image = $request->file('image');
        
        // 3. Baca file untuk dikirim ke API Flask
        $imageContents = file_get_contents($image);
        $imageName = $image->getClientOriginalName();

        try {
            // 4. Kirim ke API Flask (Pastikan Port 5001 sesuai app.py kamu)
            $response = Http::attach('image', $imageContents, $imageName)
                ->post('http://127.0.0.1:5001/predict');
            
            $result = $response->json();

            // 5. Ubah gambar jadi Base64 biar bisa ditampilkan di View tanpa save ke storage
            $base64 = base64_encode($imageContents);
            $imgSrc = 'data:image/' . $image->extension() . ';base64,' . $base64;

            return back()->with([
                'success' => true,
                'data' => $result,
                'image_preview' => $imgSrc
            ]);

        } catch (\Exception $e) {
           return back()->with('error', 'Error Detail: ' . $e->getMessage());
        }
    }
}