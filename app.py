from flask import Flask, request, jsonify
import torch
import torch.nn as nn
from torchvision import models, transforms
from PIL import Image
import io

app = Flask(__name__)

# --- KONFIGURASI ---
MODEL_PATH = 'model_resnet50_pytorch_final.pth' # Pastikan nama file sesuai
CLASS_NAMES = ['cardboard', 'compost', 'glass', 'metal', 'paper', 'plastic', 'trash']

# Pakai CPU saja untuk API (lebih aman biar gak error CUDA di laptop biasa)
device = torch.device('cpu') 

# --- 1. SETUP MODEL ARCHITECTURE ---
def load_model():
    print(" Membangun ulang arsitektur model...")
    try:
        # Load ResNet50 kosongan
        model = models.resnet50(weights=None)
        
        # Ganti Head (SAMA PERSIS dengan saat training)
        num_ftrs = model.fc.in_features
        model.fc = nn.Sequential(
            nn.Linear(num_ftrs, 512),
            nn.ReLU(),
            nn.Dropout(0.5),
            nn.Linear(512, len(CLASS_NAMES))
        )
        
        # Load Bobot .pth
        # map_location='cpu' penting agar bisa jalan di laptop non-NVIDIA
        checkpoint = torch.load(MODEL_PATH, map_location=device)
        model.load_state_dict(checkpoint)
        
        # Set mode Evaluasi (Matikan Dropout & Batch Norm update)
        model.eval()
        print("✅ Model PyTorch berhasil dimuat!")
        return model
        
    except Exception as e:
        print(f"❌ Error Load Model: {e}")
        return None

# Load model saat aplikasi start
model = load_model()

# --- 2. PREPROCESSING PIPELINE ---
# Standar ImageNet (Wajib SAMA dengan training)
preprocess_transform = transforms.Compose([
    transforms.Resize((224, 224)),
    transforms.ToTensor(),
    transforms.Normalize(
        mean=[0.485, 0.456, 0.406],
        std=[0.229, 0.224, 0.225]
    )
])

# --- ROUTES ---

@app.route('/', methods=['GET'])
def index():
    return jsonify({
        "status": "online",
        "message": "API Klasifikasi Sampah (PyTorch Engine) Siap!"
    })

@app.route('/predict', methods=['POST'])
def predict():
    if model is None:
        return jsonify({'error': 'Model gagal dimuat saat startup'}), 500

    if 'image' not in request.files:
        return jsonify({'error': 'Tidak ada file gambar yang diupload'}), 400
    
    file = request.files['image']
    
    try:
        # 1. Buka Gambar dari Memory (Tanpa simpan ke disk)
        image_bytes = file.read()
        image = Image.open(io.BytesIO(image_bytes)).convert('RGB')
        
        # 2. Preprocessing
        # Tambah dimensi batch: [3, 224, 224] -> [1, 3, 224, 224]
        image_tensor = preprocess_transform(image)
        image_tensor = preprocess_transform(image).unsqueeze(0)
        
        # 3. Prediksi (Inference)
        with torch.no_grad(): # Matikan gradien biar hemat memory
            outputs = model(image_tensor)
            
            # Hitung Probabilitas dengan Softmax
            probs = torch.nn.functional.softmax(outputs, dim=1)
            
            # Ambil nilai tertinggi
            top_prob, top_idx = torch.max(probs, 1)
            
            confidence = top_prob.item() * 100
            predicted_class = CLASS_NAMES[top_idx.item()]
            
            # (Opsional) Ambil semua skor untuk debugging
            all_scores = {
                cls: f"{probs[0][i].item()*100:.2f}%" 
                for i, cls in enumerate(CLASS_NAMES)
            }

        return jsonify({
            'status': 'success',
            'prediksi': predicted_class,
            'confidence': f"{confidence:.2f}%",
            'detail_scores': all_scores
        })

    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    # Jalankan di port 5000 (default Flask) atau 5001
    app.run(host='0.0.0.0', port=5001, debug=True)