import io

import mediapipe as mp
import numpy as np
from PIL import Image
from rembg import new_session, remove

# birefnet-portrait — найточніша модель для портретів, чисті краї на волоссі
_rembg_session = new_session("birefnet-portrait")

# MediaPipe Face Detection
_face_detection = mp.solutions.face_detection.FaceDetection(
    model_selection=1, min_detection_confidence=0.5
)

# Пропорції фото на документи (3:4)
DOC_PHOTO_RATIO = 3 / 4


def detect_face(image: Image.Image) -> tuple[int, int, int, int] | None:
    """Повертає bbox обличчя (x, y, w, h) або None."""
    rgb = np.array(image)
    results = _face_detection.process(rgb)

    if not results.detections:
        return None

    detection = results.detections[0]
    bbox = detection.location_data.relative_bounding_box
    w, h = image.size

    face_x = int(bbox.xmin * w)
    face_y = int(bbox.ymin * h)
    face_w = int(bbox.width * w)
    face_h = int(bbox.height * h)

    return (face_x, face_y, face_w, face_h)


def remove_background(image: Image.Image) -> Image.Image:
    """Видаляє фон з зображення через rembg з alpha matting для чистих країв."""
    return remove(
        image,
        session=_rembg_session,
        alpha_matting=True,
        alpha_matting_foreground_threshold=240,
        alpha_matting_background_threshold=10,
        alpha_matting_erode_size=10,
    )


def compose_document_photo(image: Image.Image, face: tuple[int, int, int, int]) -> Image.Image:
    """Компонує фото на документи з правильними пропорціями і кадруванням.

    Стандарт: обличчя займає ~60-70% висоти, зверху відступ ~15%.
    Пропорції 3:4 (ширина:висота).
    """
    face_x, face_y, face_w, face_h = face
    img_w, img_h = image.size

    face_center_x = face_x + face_w // 2
    face_center_y = face_y + face_h // 2

    # Висота фінального кадру: обличчя = ~35% висоти (голова + волосся ~50%)
    target_h = int(face_h / 0.35)
    target_w = int(target_h * DOC_PHOTO_RATIO)

    # Верхній край: обличчя починається на ~25% від верху
    top = face_y - int(target_h * 0.25)
    left = face_center_x - target_w // 2

    # Коригуємо якщо виходимо за межі
    top = max(0, top)
    left = max(0, left)
    if left + target_w > img_w:
        left = max(0, img_w - target_w)
    if top + target_h > img_h:
        top = max(0, img_h - target_h)

    # Фінальні розміри (обрізаємо якщо зображення менше)
    right = min(img_w, left + target_w)
    bottom = min(img_h, top + target_h)

    cropped = image.crop((left, top, right, bottom))

    # Якщо кроп менший за потрібний — вставляємо на білий фон потрібного розміру
    if cropped.width < target_w or cropped.height < target_h:
        canvas = Image.new("RGBA", (target_w, target_h), (255, 255, 255, 255))
        paste_x = (target_w - cropped.width) // 2
        paste_y = 0  # завжди зверху
        canvas.paste(cropped, (paste_x, paste_y), cropped if cropped.mode == "RGBA" else None)
        return canvas

    return cropped


def add_white_background(image: Image.Image) -> Image.Image:
    """Додає білий фон до RGBA зображення."""
    if image.mode != "RGBA":
        return image
    background = Image.new("RGBA", image.size, (255, 255, 255, 255))
    background.paste(image, mask=image.split()[3])
    return background.convert("RGB")


def process_photo(image_bytes: bytes) -> bytes:
    """Повний пайплайн для фото на документи."""
    image = Image.open(io.BytesIO(image_bytes)).convert("RGB")

    # Детект обличчя (потрібен для кадрування)
    face = detect_face(image)

    # Видалення фону (birefnet-portrait + alpha matting)
    no_bg = remove_background(image)

    # Кадрування під документне фото (3:4, обличчя по центру)
    if face:
        composed = compose_document_photo(no_bg, face)
    else:
        composed = no_bg

    # Білий фон
    result = add_white_background(composed)

    # Зберігаємо в PNG
    output = io.BytesIO()
    result.save(output, format="PNG")
    return output.getvalue()
