# Gunakan Python versi 3.11 yang stabil
FROM python:3.11-slim

# Atur folder kerja di dalam server
WORKDIR /app

# Salin file requirements dan install library
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Salin seluruh kode main.py Anda
COPY . .

# Hugging Face Spaces WAJIB menggunakan port 7860
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "7860"]