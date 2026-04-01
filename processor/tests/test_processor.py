import io
from unittest.mock import patch
from PIL import Image
from processor import process_photo


def make_test_image(width: int = 200, height: int = 250) -> bytes:
    img = Image.new("RGBA", (width, height), color=(200, 180, 160, 255))
    buf = io.BytesIO()
    img.save(buf, format="PNG")
    return buf.getvalue()


@patch("processor.remove_background")
@patch("processor.detect_face")
def test_process_photo_returns_correct_pixel_size(mock_face, mock_bg):
    mock_face.return_value = None
    def passthrough(img):
        return img.convert("RGBA")
    mock_bg.side_effect = passthrough

    result = process_photo(make_test_image(), width_mm=35, height_mm=45, dpi=300)
    img = Image.open(io.BytesIO(result))
    assert img.size == (int(35 / 25.4 * 300), int(45 / 25.4 * 300))  # (413, 531)


@patch("processor.remove_background")
@patch("processor.detect_face")
def test_process_photo_square_format(mock_face, mock_bg):
    mock_face.return_value = None
    def passthrough(img):
        return img.convert("RGBA")
    mock_bg.side_effect = passthrough

    result = process_photo(make_test_image(), width_mm=51, height_mm=51, dpi=300)
    img = Image.open(io.BytesIO(result))
    w, h = img.size
    assert w == h


@patch("processor.remove_background")
@patch("processor.detect_face")
def test_process_photo_default_params(mock_face, mock_bg):
    mock_face.return_value = None
    def passthrough(img):
        return img.convert("RGBA")
    mock_bg.side_effect = passthrough

    result = process_photo(make_test_image())
    img = Image.open(io.BytesIO(result))
    assert img.size == (int(35 / 25.4 * 300), int(45 / 25.4 * 300))
