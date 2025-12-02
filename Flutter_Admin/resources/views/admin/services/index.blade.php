<x-admin.layout title="Services">

    <!-- PAGE TITLE -->
    <h2 class="fw-bold mb-3" style="color: var(--accent-brown-deep);">Services</h2>

    <!-- SEARCH + FILTER BAR -->
    <div class="content-card mb-4">
        <form method="GET"
              action="{{ route('admin.services.index') }}"
              class="d-flex justify-content-between align-items-center flex-wrap gap-2">

            <div class="d-flex align-items-center flex-wrap gap-2 filter-row">

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="form-control rounded-pill filter-input"
                       placeholder="Search services...">

                <select name="type" class="form-select rounded-pill filter-input">
                    <option value="">All Types</option>
                    <option value="restaurant"      {{ request('type')=='restaurant'?'selected':'' }}>Restaurant</option>
                    <option value="spa"             {{ request('type')=='spa'?'selected':'' }}>Spa</option>
                    <option value="gym"             {{ request('type')=='gym'?'selected':'' }}>Gym</option>
                    <option value="swimming_pool"   {{ request('type')=='swimming_pool'?'selected':'' }}>Swimming Pool</option>
                    <option value="bar"             {{ request('type')=='bar'?'selected':'' }}>Bar</option>
                </select>

                <select name="status" class="form-select rounded-pill filter-input">
                    <option value="">All Status</option>
                    <option value="available"   {{ request('status')=='available'?'selected':'' }}>Available</option>
                    <option value="maintenance" {{ request('status')=='maintenance'?'selected':'' }}>Maintenance</option>
                </select>

                <select name="archived" class="form-select rounded-pill filter-input">
                    <option value="">Archived?</option>
                    <option value="0" {{ request('archived')=='0'?'selected':'' }}>Active</option>
                    <option value="1" {{ request('archived')=='1'?'selected':'' }}>Archived</option>
                </select>

                <button class="btn btn-outline-coffee rounded-pill px-4" type="submit">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>

                <a href="{{ route('admin.services.index') }}"
                   class="btn btn-light rounded-pill px-4">
                    Reset
                </a>
            </div>

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

                        <td>{{ ucfirst(str_replace('_', ' ', $service->service_type)) }}</td>

                        <td>{{ $service->location ?? '—' }}</td>

                        <td>
                            ₱{{ $service->formatted_base_price }}
                            <small class="text-muted">{{ $service->formatted_price_type }}</small>
                        </td>

                        <td>{{ $service->capacity ?? '—' }}</td>

                        <td>{{ $service->start_time }} - {{ $service->end_time }}</td>

                        <td>
                            @if ($service->status === 'available')
                                <span class="badge bg-success">Available</span>
                            @else
                                <span class="badge bg-warning text-dark">Maintenance</span>
                            @endif
                        </td>

                        <td>
                            @if ($service->is_archived)
                                <span class="badge bg-secondary">Archived</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </td>

                        <td class="text-end">

                            <button class="btn btn-sm btn-primary"
                                    data-id="{{ $service->id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalViewService">
                                <i class="bi bi-eye"></i>
                            </button>

                            @if (!$service->is_archived)
                                <button class="btn btn-sm btn-warning"
                                        data-id="{{ $service->id }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditService">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            @else
                                <button class="btn btn-sm btn-secondary" disabled>
                                    <i class="bi bi-pencil"></i>
                                </button>
                            @endif

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
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bi bi-info-circle me-1"></i> No services found.
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    /* SERVICE RULE MAPS */
    const capacityRules = {
        restaurant: [1,500],
        bar: [1,500],
        spa: [1,40],
        gym: [1,500],
        swimming_pool: [1,500],
    };

    const priceRules = {
        restaurant: "per person",
        bar: "per person",
        spa: "per hour",
        gym: "per day",
        swimming_pool: "per day",
    };

    function updateServiceUI(prefix, type) {
        const capHint = document.getElementById(`${prefix}-capacity-hint`);
        if (capacityRules[type] && capHint) {
            const [min,max] = capacityRules[type];
            capHint.innerText = `Allowed: ${min}–${max}`;
        }

        const pField = document.getElementById(`${prefix}-price-type`);
        if (pField) pField.value = priceRules[type] ?? "";
    }

    /* ADD SERVICE */
    const addType = document.getElementById('add-service-type');
    if (addType) {
        addType.addEventListener('change', e => updateServiceUI('add', e.target.value));
        updateServiceUI('add', addType.value);
    }

    /* VIEW SERVICE */
    document.getElementById('modalViewService').addEventListener('show.bs.modal', event => {

        const id = event.relatedTarget.getAttribute('data-id');

        fetch(`/admin/services/${id}`)
            .then(r => r.json())
            .then(svc => {

                document.getElementById('viewServiceImage').src = svc.image_url;
                document.getElementById('viewServiceName').innerText = svc.name;
                document.getElementById('viewServiceType').innerText =
                    svc.service_type.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase());
                document.getElementById('viewServiceLocation').innerText = svc.location ?? '—';
                document.getElementById('viewServicePrice').innerText = svc.base_price;
                document.getElementById('viewServicePriceType').innerText = svc.price_type;
                document.getElementById('viewServiceCapacity').innerText = svc.capacity ?? '—';
                document.getElementById('viewServiceHours').innerText = `${svc.start_time} - ${svc.end_time}`;
                document.getElementById('viewServiceDescription').innerText = svc.description ?? '';
            });
    });

    /* EDIT SERVICE */
    document.getElementById('modalEditService').addEventListener('show.bs.modal', event => {
        const id = event.relatedTarget.getAttribute('data-id');
        const form = document.getElementById('formEditService');
        if (form) form.action = `/admin/services/${id}`;

        fetch(`/admin/services/${id}`)
            .then(r => r.json())
            .then(svc => {

                document.getElementById('edit-name').value = svc.name;
                document.getElementById('edit-location').value = svc.location ?? '';
                document.getElementById('edit-service-type').value = svc.service_type;
                document.getElementById('edit-capacity').value = svc.capacity ?? '';
                document.getElementById('edit-base-price').value = svc.base_price.replace(/,/g,'');
                document.getElementById('edit-start-time').value = svc.start_time;
                document.getElementById('edit-end-time').value = svc.end_time;
                document.getElementById('edit-status').value = svc.status;
                document.getElementById('edit-description').value = svc.description ?? '';

                // svc.price_type is formatted (e.g. "per hour"), convert to raw machine value
                const priceTypeMap = {
                    'per person': 'per_person',
                    'per hour': 'per_hour',
                    'per day': 'per_day'
                };

                const normalizedPriceType = priceTypeMap[svc.price_type.toLowerCase()] || svc.price_type;
                document.getElementById('edit-price-type').value = normalizedPriceType;

                updateServiceUI('edit', svc.service_type);
            });
    });

    /* When service-type changes in edit modal, update hints and price-type */
    document.getElementById('edit-service-type')?.addEventListener('change', e => {
        updateServiceUI('edit', e.target.value);
        const map = {
            restaurant: 'per_person',
            bar: 'per_person',
            spa: 'per_hour',
            gym: 'per_day',
            swimming_pool: 'per_day'
        };
        document.getElementById('edit-price-type').value = map[e.target.value] ?? '';
    });

    /* ARCHIVE SERVICE */
    document.getElementById('modalArchiveService').addEventListener('show.bs.modal', event => {
        const id = event.relatedTarget.getAttribute('data-id');
        document.getElementById('formArchiveService').action = `/admin/services/${id}/archive`;

        fetch(`/admin/services/${id}`)
            .then(r => r.json())
            .then(svc => {
                // Changed to show opposite option (matching Rooms logic)
                document.getElementById('archiveSelectService').value = svc.is_archived ? "0" : "1";
            });
    });
});

</script>
@endpush

</x-admin.layout>
