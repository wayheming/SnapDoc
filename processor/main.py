from fastapi import FastAPI, File, UploadFile
from fastapi.responses import Response

from processor import process_photo

app = FastAPI(title="Photo Processor")


@app.post("/process")
async def process(photo: UploadFile = File(...)):
    """Обробляє фото: видаляє фон, кропає, додає білий фон."""
    image_bytes = await photo.read()
    result = process_photo(image_bytes)
    return Response(content=result, media_type="image/png")
