FROM python:3.11-slim

WORKDIR /app

# системні залежності для MediaPipe і rembg
RUN apt-get update && apt-get install -y \
    libgl1 libglib2.0-0 && \
    rm -rf /var/lib/apt/lists/*

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# pre-download модель щоб не качалась при кожному старті
RUN python -c "from rembg import new_session; new_session('birefnet-portrait')"

COPY . .

CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000"]
