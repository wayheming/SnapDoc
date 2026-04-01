{{-- app/resources/views/livewire/photo-processor.blade.php --}}
<div>
    {{-- STEP 1: Upload + format selection --}}
    @if ($step === 1)
        <h1 class="text-2xl font-bold mb-6">Upload your photo</h1>

        <form wire:submit="submit" class="space-y-6">
            {{-- Photo upload --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Photo</label>
                <input type="file" wire:model="photo" accept="image/*"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                              file:border-0 file:text-sm file:font-semibold
                              file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                @error('photo') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Format selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Document Format</label>
                <select wire:model="documentFormatId"
                        class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Select format —</option>
                    @foreach ($formats as $country => $countryFormats)
                        <optgroup label="{{ $country }}">
                            @foreach ($countryFormats as $format)
                                <option value="{{ $format->id }}">
                                    {{ $format->name }} ({{ $format->width_mm }}×{{ $format->height_mm }}mm)
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('documentFormatId') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Privacy Policy --}}
            <div class="flex items-start gap-2">
                <input type="checkbox" wire:model="privacyAccepted" id="privacy" class="mt-1">
                <label for="privacy" class="text-sm text-gray-600">
                    I agree to the
                    <a href="{{ route('privacy-policy') }}" target="_blank" class="text-blue-600 underline">
                        Privacy Policy
                    </a>
                </label>
            </div>
            @error('privacyAccepted') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror

            <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                <span wire:loading.remove>Process Photo</span>
                <span wire:loading>Uploading…</span>
            </button>
        </form>
    @endif

    {{-- STEP 2: Processing + result --}}
    @if ($step === 2)
        <h1 class="text-2xl font-bold mb-6">Your Photo</h1>

        @if (!$order || in_array($order->status, ['pending', 'processing']))
            {{-- Spinner while processing --}}
            <div wire:poll.2000ms="checkStatus" class="text-center py-12">
                <div class="inline-block w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p class="text-gray-600">Processing your photo, please wait…</p>
            </div>
        @elseif ($order->status === 'failed')
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <p class="text-red-700 font-semibold">Failed to process photo</p>
                <p class="text-red-600 text-sm mt-1">Please try another photo with a clear face</p>
                <button wire:click="$set('step', 1)" class="mt-4 text-blue-600 underline text-sm">
                    Try again
                </button>
            </div>
        @elseif ($order->status === 'completed')
            {{-- Result with free download --}}
            <div class="space-y-6">
                <div class="border rounded-lg overflow-hidden">
                    <img src="{{ route('preview', $order->uuid) }}" alt="Photo preview"
                         class="w-full max-w-xs mx-auto block">
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                    <p class="text-gray-800 font-semibold mb-1">Photo ready!</p>
                    <p class="text-gray-600 text-sm mb-4">
                        Download your photo for free
                    </p>
                    <a href="{{ route('download', $order->uuid) }}"
                       class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                        Download Free
                    </a>
                </div>
            </div>
        @endif
    @endif
</div>
