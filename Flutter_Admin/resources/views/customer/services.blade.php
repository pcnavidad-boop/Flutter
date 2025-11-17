@extends('customer.layouts.hotel')

@section('content')

<h1 class="mb-4 text-center">Our Services</h1>

@php
    $services = [
        [
            'name' => 'Spa',
            'description' => 'Relax and rejuvenate with our signature spa treatments, aromatherapy, massage services, and calming ambiance designed to melt your stress away.',
            'image' => asset('images/spa.jpg')
        ],
        [
            'name' => 'Gym',
            'description' => 'Enjoy our fully equipped fitness center open 24/7, featuring modern machines, free weights, and a spacious workout area for all fitness levels.',
            'image' => asset('images/gym.jpg')
        ],
        [
            'name' => 'Event Hall',
            'description' => 'A spacious hall perfect for weddings, birthdays, and corporate events. Designed with premium interiors and complete event amenities.',
            'image' => asset('images/events_hall.jpg')
        ]
    ];
@endphp

<div class="d-flex flex-column gap-4">

    @foreach ($services as $service)
    <div class="card border-0 shadow-sm">

        <div class="row g-0">

            <!-- Image -->
            <div class="col-md-4">
                <img src="{{ $service['image'] }}"
                     class="img-fluid rounded-start w-100 h-100 object-fit-cover"
                     alt="{{ $service['name'] }}">
            </div>

            <!-- Content -->
            <div class="col-md-8 d-flex flex-column p-4">

                <h3 class="fw-semibold">{{ $service['name'] }}</h3>

                <p class="text-muted mb-4">{{ $service['description'] }}</p>

                <div class="mt-auto">
                    <a href="{{ route('hotel.book.service') }}" class="btn btn-primary px-4 py-2">
                        Book Now
                    </a>
                </div>

            </div>
        </div>

    </div>
    @endforeach

</div>

@endsection
