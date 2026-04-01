{{-- resources/views/filament/pages/stats-page.blade.php --}}
<x-filament-panels::page>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Total Orders</p>
            <p class="text-3xl font-bold text-gray-800">{{ $total }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Today</p>
            <p class="text-3xl font-bold text-blue-600">{{ $today }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">This Week</p>
            <p class="text-3xl font-bold text-gray-700">{{ $this_week }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">This Month</p>
            <p class="text-3xl font-bold text-gray-700">{{ $this_month }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Failed</p>
            <p class="text-3xl font-bold text-red-500">{{ $failed }}</p>
        </div>
    </div>
</x-filament-panels::page>
