<?php
// app/tests/Unit/Models/DocumentFormatTest.php

use App\Models\DocumentFormat;

it('scope active returns only active formats ordered by sort_order', function () {
    DocumentFormat::factory()->create(['is_active' => true, 'sort_order' => 2]);
    DocumentFormat::factory()->create(['is_active' => true, 'sort_order' => 1]);
    DocumentFormat::factory()->create(['is_active' => false, 'sort_order' => 0]);

    $active = DocumentFormat::active()->get();

    expect($active)->toHaveCount(2)
        ->and($active->first()->sort_order)->toBe(1);
});
