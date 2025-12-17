<x-admin.layout title="Services">

    <!-- PAGE TITLE -->
    <h2 class="fw-bold mb-3" style="color: var(--accent-brown-deep);">Services</h2>

    <!-- SEARCH + FILTER BAR CARD -->
    <div class="content-card mb-4">

        <form method="GET"
              action="{{ route('admin.services.index') }}"
              class="filter-bar">

            <!-- LEFT SIDE FILTERS -->
            <div class="filter-controls">

                <!-- SEARCH -->
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="form-control rounded-pill"
                       placeholder="Search services...">

                <!-- TYPE -->
                <select name="type" class="form-select rounded-pill">
                    <option value="">All Types</option>
                    @foreach(['restaurant','spa','gym','swimming_pool','bar'] as $type)
                        <option value="{{ $type }}" @selected(request('type') === $type)>
                            {{ ucfirst(str_replace('_',' ',$type)) }}
                        </option>
                    @endforeach
                </select>

                <!-- STATUS -->
                <select name="status" class="form-select rounded-pill">
                    <option value="">All Status</option>
                    <option value="available" @selected(request('status')==='available')>
                        Available
                    </option>
                    <option value="maintenance" @selected(request('status')==='maintenance')>
                        Maintenance
                    </option>
                </select>

                <!-- ARCHIVED -->
                <select name="archived" class="form-select rounded-pill">
                    <option value="">Archived?</option>
                    <option value="0" @selected(request('archived')==='0')>Active</option>
                    <option value="1" @selected(request('archived')==='1')>Archived</option>
                </select>

                <!-- FILTER BUTTON -->
                <button class="btn btn-outline-coffee rounded-pill px-4">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>

                <!-- RESET -->
                <a href="{{ route('admin.services.index') }}"
                   class="btn btn-light rounded-pill px-4">
                    Reset
                </a>

            </div>

            <!-- RIGHT SIDE BUTTON -->
            <button class="btn btn-coffee rounded-pill px-4"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#modalAddService">
                <i class="bi bi-plus-lg me-1"></i> Add Service
            </button>

        </form>

    </div>

    <!-- TABLE -->
    <div class="content-card">
        <div class="table-responsive">

            <table class="table table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Base Price</th>
                    <th>Capacity</th>
                    <th>Hours</th>
                    <th>Status</th>
                    <th>Archived</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>

                <tbody>

                @forelse ($services as $service)
                    <tr>

                        <td>{{ $service->name }}</td>

                        <td>{{ ucfirst(str_replace('_',' ',$service->service_type)) }}</td>

                        <td>{{ $service->location ?? '—' }}</td>

                        <td>
                            ₱{{ $service->formatted_base_price }}
                            <small class="text-muted">
                                {{ $service->formatted_price_type }}
                            </small>
                        </td>

                        <td>{{ $service->capacity ?? '—' }}</td>

                        <td>{{ $service->start_time }} - {{ $service->end_time }}</td>

                        <td>
                            <span class="badge {{ $service->status === 'available' ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ ucfirst($service->status) }}
                            </span>
                        </td>

                        <td>
                            <span class="badge {{ $service->is_archived ? 'bg-secondary' : 'bg-success' }}">
                                {{ $service->is_archived ? 'Archived' : 'Active' }}
                            </span>
                        </td>

                        <td class="text-end">

                            <!-- VIEW -->
                            <button class="btn btn-sm btn-primary"
                                    data-id="{{ $service->id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalViewService">
                                <i class="bi bi-eye"></i>
                            </button>

                            <!-- EDIT -->
                            <button class="btn btn-sm btn-warning"
                                    data-id="{{ $service->id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditService"
                                    {{ $service->is_archived ? 'disabled' : '' }}>
                                <i class="bi bi-pencil"></i>
                            </button>

                            <!-- ARCHIVE -->
                            <button class="btn btn-sm btn-danger"
                                    data-id="{{ $service->id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalArchiveService">
                                <i class="bi bi-box-arrow-down"></i>
                            </button>

                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="9"
                            class="text-center py-4 text-muted"
                            style="font-size:1.1rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            No services found.
                        </td>
                    </tr>
                @endforelse

                </tbody>
            </table>

        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $services->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>

    {{-- MODALS --}}
    @include('admin.services.modals.add')
    @include('admin.services.modals.edit')
    @include('admin.services.modals.archive')
    @include('admin.services.modals.view')

    {{-- AUTO-OPEN ADD MODAL IF VALIDATION FAILED --}}
    @if ($errors->addService->any() && session('open_add_modal'))
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        new bootstrap.Modal(document.getElementById('modalAddService')).show();
    });
    </script>
    @endif

    {{-- AUTO-OPEN EDIT MODAL IF VALIDATION FAILED --}}
    @if ($errors->editService->any() && session('edit_id'))
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const id = "{{ session('edit_id') }}";
        const modal = new bootstrap.Modal(document.getElementById('modalEditService'));

        fetch(`/admin/services/${id}`)
            .then(r => r.json())
            .then(svc => {

                const old = @json(old());

                document.getElementById("formEditService").action = `/admin/services/${id}`;

                document.getElementById("edit-name").value =
                    old.name ?? svc.name;

                document.getElementById("edit-location").value =
                    old.location ?? svc.location ?? "";

                document.getElementById("edit-service-type").value =
                    old.service_type ?? svc.service_type;

                document.getElementById("edit-capacity").value =
                    old.capacity ?? svc.capacity ?? "";

                document.getElementById("edit-base-price").value =
                    old.base_price ?? svc.base_price;

                document.getElementById("edit-start-time").value =
                    old.start_time ?? svc.start_time;

                document.getElementById("edit-end-time").value =
                    old.end_time ?? svc.end_time;

                document.getElementById("edit-description").value =
                    old.description ?? svc.description ?? "";

                document.getElementById("edit-status").value =
                    old.status ?? svc.status;

                const priceTypeField = document.getElementById("edit-price-type");

                priceTypeField.value =
                    old.price_type ??
                    svc.formatted_price_type ??
                    "";

                window.ServicesCommon.updateHints("edit", svc.service_type);

                modal.show();
            });
    });
    </script>
    @endif

    @push('scripts')
        <script src="/admin/js/pages/services/common.js" defer></script>
        <script src="/admin/js/pages/services/add.js" defer></script>
        <script src="/admin/js/pages/services/edit.js" defer></script>
        <script src="/admin/js/pages/services/view.js" defer></script>
        <script src="/admin/js/pages/services/archive.js" defer></script>
    @endpush

</x-admin.layout>
