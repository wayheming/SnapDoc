from fastapi import FastAPI, File, Form, UploadFile
from fastapi.responses import Response

from processor import process_photo

app = FastAPI(title="Photo Processor")


@app.post("/process")
async def process(
    photo: UploadFile = File(...),
    width_mm: int = Form(35),
    height_mm: int = Form(45),
    dpi: int = Form(300),
):
    """Обробляє фото: видаляє фон, кадрує, повертає PNG потрібного розміру."""
    image_bytes = await photo.read()
    result = process_photo(image_bytes, width_mm, height_mm, dpi)
    return Response(content=result, media_type="image/png")
