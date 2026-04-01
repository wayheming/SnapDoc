{{-- app/resources/views/pages/privacy-policy.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="prose max-w-none">
    <h1 class="text-2xl font-bold mb-6">Privacy Policy</h1>

    <h2 class="text-lg font-semibold mt-6 mb-2">What we collect</h2>
    <p class="text-gray-700 mb-4">
        When processing photos we temporarily store: the uploaded photo, the processed result,
        IP address, and technical request data (User-Agent, session cookies).
    </p>

    <h2 class="text-lg font-semibold mt-6 mb-2">How we store data</h2>
    <p class="text-gray-700 mb-4">
        Files are stored on a secure server solely to provide the service.
        Access to files is via a unique link.
    </p>

    <h2 class="text-lg font-semibold mt-6 mb-2">Retention period</h2>
    <p class="text-gray-700 mb-4">
        All uploaded photos and processed results are automatically deleted after <strong>24 hours</strong>.
    </p>

    <h2 class="text-lg font-semibold mt-6 mb-2">Data deletion</h2>
    <p class="text-gray-700 mb-4">
        If you wish to delete your data sooner, please contact us.
        After the retention period, data is deleted automatically.
    </p>

    <h2 class="text-lg font-semibold mt-6 mb-2">Cookies</h2>
    <p class="text-gray-700 mb-4">
        We use session cookies to identify orders.
        Cookies are deleted when the browser is closed or after 24 hours.
    </p>
</div>
@endsection
