<!-- Toggle button -->
<button data-drawer-target="sidebar" data-drawer-toggle="sidebar" aria-controls="sidebar" type="button"
        class="inline-flex items-center p-2 mt-2 ms-3 text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100">
    ☰
</button>

<!-- Sidebar -->
<aside id="sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full md:translate-x-0 bg-gray-50 border-r">
    <div class="h-full px-3 py-4 overflow-y-auto">
        <ul class="space-y-2 font-medium">
            <li>
                <a href="{{ route('dashboard') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('room.index_page') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                    <i class="bi bi-door-open me-2"></i> Rooms
                </a>
            </li>
        </ul>
    </div>
</aside>
