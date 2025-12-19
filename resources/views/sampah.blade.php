<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klasifikasi Sampah Cerdas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center py-10">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">♻️ Cek Jenis Sampah</h1>
            <p class="text-gray-500 text-sm">Upload foto sampah untuk diidentifikasi</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('cek.sampah') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            
            <div class="flex items-center justify-center w-full">
                <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-blue-400 rounded-lg cursor-pointer bg-blue-50 hover:bg-blue-100 transition">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg class="w-8 h-8 mb-4 text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                        </svg>
                        <p class="text-sm text-gray-500"><span class="font-semibold">Klik untuk upload</span> gambar</p>
                        <p class="text-xs text-gray-400">JPG, PNG (Max 5MB)</p>
                    </div>
                    <input id="dropzone-file" name="image" type="file" class="hidden" onchange="previewImage(this)" required />
                </label>
            </div>

            <div id="preview-container" class="hidden text-center">
                <p class="text-xs text-gray-500 mb-2">Akan diupload:</p>
                <img id="preview-img" class="mx-auto h-32 rounded-lg object-cover shadow-sm">
            </div>

            <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition">
                🔍 Identifikasi Sekarang
            </button>
        </form>

        @if(session('success'))
        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4 text-center">🎉 Hasil Analisis AI</h3>
            
            <div class="flex flex-col items-center">
                <img src="{{ session('image_preview') }}" class="w-48 h-48 object-cover rounded-lg shadow-md mb-4 border-4 border-white">
                
                <div class="text-center">
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded uppercase tracking-wide">Terdeteksi</span>
                    <h2 class="text-3xl font-extrabold text-gray-900 mt-2 capitalize">{{ session('data')['prediksi'] }}</h2>
                    <p class="text-gray-500 mt-1">Tingkat Keyakinan: <span class="font-bold text-blue-600">{{ session('data')['confidence'] }}</span></p>
                </div>
            </div>
        </div>
        @endif

    </div>

    <script>
        function previewImage(input) {
            const container = document.getElementById('preview-container');
            const preview = document.getElementById('preview-img');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    container.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>