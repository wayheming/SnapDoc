<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PhotoController extends Controller
{
    public function preview(string $uuid)
    {
        // TODO: Task 8 — serve preview image
        abort(404);
    }

    public function download(string $uuid)
    {
        // TODO: Task 8 — serve download
        abort(404);
    }
}
