{{-- app/resources/views/livewire/photo-processor.blade.php --}}
<div>
    {{-- STEP 1: Upload + format selection --}}
    @if ($step === 1)
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Upload your photo</h2>

        <form wire:submit="submit" class="space-y-6">
            {{-- Photo upload --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Photo</label>

                @if ($photo)
                    {{-- Preview of uploaded photo --}}
                    <div class="relative rounded-xl overflow-hidden border border-indigo-500/[0.15] bg-gray-50 mb-3">
                        <img src="{{ $photo->temporaryUrl() }}" alt="Preview"
                             class="w-full max-h-64 object-contain mx-auto block p-2">
                        <button type="button" wire:click="$set('photo', null)"
                                class="absolute top-2 right-2 w-8 h-8 rounded-full bg-white/90 shadow text-gray-500 hover:text-red-500 flex items-center justify-center transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mb-1">{{ $photo->getClientOriginalName() }}</p>
                @else
                    <div class="relative border-2 border-dashed border-indigo-500/[0.25] rounded-xl p-6 text-center hover:border-indigo-500 transition-colors">
                        <input type="file" wire:model="photo" accept="image/*"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="space-y-2">
                            <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center mx-auto">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-600">
                                <span class="font-semibold text-indigo-600">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-xs text-gray-400">PNG, JPG up to 20MB</p>
                        </div>
                    </div>
                    <div wire:loading wire:target="photo" class="mt-2 text-sm text-indigo-600 flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Uploading...
                    </div>
                @endif
                @error('photo') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
            </div>

            {{-- Format selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Document Format</label>
                <select wire:model="documentFormatId"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-[#f9fafb] hover:bg-white transition">
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
                @error('documentFormatId') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
            </div>

            {{-- Privacy Policy --}}
            <div class="flex items-start gap-3">
                <input type="checkbox" wire:model="privacyAccepted" id="privacy"
                       class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="privacy" class="text-sm text-gray-600">
                    I agree to the
                    <a href="{{ route('privacy-policy') }}" target="_blank" class="text-indigo-600 underline hover:text-indigo-800">
                        Privacy Policy
                    </a>
                </label>
            </div>
            @error('privacyAccepted') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror

            <button type="submit"
                    class="w-full gradient-bg text-white py-4 rounded-[14px] font-bold text-lg
                           shadow-[0_4px_16px_rgba(99,102,241,0.3)] hover:shadow-[0_8px_24px_rgba(99,102,241,0.4)]
                           transition-all duration-300 transform hover:-translate-y-0.5">
                <span wire:loading.remove>Process Photo</span>
                <span wire:loading class="flex items-center justify-center gap-2">
                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Uploading...
                </span>
            </button>
        </form>
    @endif

    {{-- STEP 2: Processing + result --}}
    @if ($step === 2)
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Your Photo</h2>

        @if (!$order || in_array($order->status, ['pending', 'processing']))
            {{-- Spinner while processing --}}
            <div wire:poll.2000ms="checkStatus" class="text-center py-16">
                <div class="relative w-16 h-16 mx-auto mb-6">
                    <div class="absolute inset-0 rounded-full border-4 border-indigo-100"></div>
                    <div class="absolute inset-0 rounded-full border-4 border-indigo-500 border-t-transparent animate-spin"></div>
                </div>
                <p class="text-gray-700 font-medium">Processing your photo...</p>
                <p class="text-gray-400 text-sm mt-2">This usually takes about 15-30 seconds</p>
            </div>
        @elseif ($order->status === 'failed')
            <div class="bg-red-50 border border-red-100 rounded-2xl p-8 text-center">
                <div class="w-14 h-14 rounded-full bg-red-100 text-red-500 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <p class="text-red-800 font-semibold text-lg">Failed to process photo</p>
                <p class="text-red-600 text-sm mt-2">Please try another photo with a clear face</p>
                <button wire:click="$set('step', 1)"
                        class="mt-6 inline-flex items-center gap-2 text-indigo-600 font-medium hover:text-indigo-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Try again
                </button>
            </div>
        @elseif ($order->status === 'completed')
            {{-- Result with free download --}}
            <div class="space-y-6">
                <div class="rounded-2xl overflow-hidden bg-gray-50 border border-gray-100 p-4">
                    <img src="{{ route('preview', $order->uuid) }}" alt="Photo preview"
                         class="w-full max-w-xs mx-auto block rounded-lg shadow-sm">
                </div>
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 rounded-2xl p-8 text-center">
                    <div class="w-14 h-14 rounded-full bg-green-100 text-green-600 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-gray-900 font-bold text-xl mb-1">Photo Ready!</p>
                    <p class="text-gray-500 text-sm mb-6">Your document photo has been processed successfully</p>
                    <a href="{{ route('download', $order->uuid) }}"
                       class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-green-600 text-white px-8 py-4 rounded-[14px] font-bold text-lg
                              hover:from-emerald-600 hover:to-green-700 shadow-[0_4px_16px_rgba(16,185,129,0.3)] hover:shadow-[0_8px_24px_rgba(16,185,129,0.4)]
                              transition-all duration-300 transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Free
                    </a>
                </div>
                <div class="text-center">
                    <button wire:click="$set('step', 1)"
                            class="inline-flex items-center gap-2 text-indigo-600 font-medium hover:text-indigo-800 transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Process another photo
                    </button>
                </div>
            </div>
        @endif
    @endif
</div>
