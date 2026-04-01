<?php
// app/app/Livewire/PhotoProcessor.php

namespace App\Livewire;

use App\Jobs\ProcessPhotoJob;
use App\Models\DocumentFormat;
use App\Models\PhotoOrder;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class PhotoProcessor extends Component
{
    use WithFileUploads;

    #[Validate('required|image|max:10240')]
    public mixed $photo = null;

    #[Validate('required|exists:document_formats,id')]
    public int $documentFormatId = 0;

    #[Validate('accepted')]
    public bool $privacyAccepted = false;

    public int $step = 1;
    public ?string $orderUuid = null;

    public function submit(): void
    {
        $this->validate();

        $path = $this->photo->store('originals', 'local');

        $order = PhotoOrder::create([
            'document_format_id' => $this->documentFormatId,
            'original_path'      => $path,
        ]);

        $this->orderUuid = $order->uuid;
        $this->step      = 2;

        ProcessPhotoJob::dispatch($order);
    }

    public function checkStatus(): void
    {
        if (!$this->orderUuid || $this->step !== 2) {
            return;
        }

        $order = PhotoOrder::where('uuid', $this->orderUuid)->firstOrFail();

        if (in_array($order->status, ['completed', 'failed'])) {
            // Component stays on step 2; template shows different UI based on status
            $this->dispatch('status-updated');
        }
    }

    public function render(): \Illuminate\View\View
    {
        $formats = DocumentFormat::active()->get()->groupBy('country');
        $order   = $this->orderUuid
            ? PhotoOrder::where('uuid', $this->orderUuid)->first()
            : null;

        return view('livewire.photo-processor', compact('formats', 'order'));
    }
}
