@extends('customer.layouts.hotel')

@section('content')

<h1 class="mb-4 text-center">Our Rooms</h1>

@php
    $rooms = [
        [
            'name' => 'Single Room',
            'description' => 'Perfect for solo travelers who want comfort and privacy. Includes a soft queen bed, workspace, air-conditioning, fast Wi-Fi, and a modern bathroom.',
            'image' => asset('images/single_room.jpg')
        ],
        [
            'name' => 'Double Room',
            'description' => 'Designed for two guests, offering more space and comfort. Features twin or queen beds, a seating area, LED TV, Wi-Fi, and a cozy ambiance ideal for couples or friends.',
            'image' => asset('images/double_room.jpg')
        ],
        [
            'name' => 'Deluxe Room',
            'description' => 'A luxurious retreat with premium furnishings, minibar, elegant décor, and a stylish bathroom with hotel-grade amenities. Perfect for guests wanting extra comfort.',
            'image' => asset('images/deluxe_room.jpg')
        ],
        [
            'name' => 'Suite Room',
            'description' => 'Our most exclusive room, featuring a separate living area, balcony with a view, premium bedding, large bathroom, and thoughtful, personalized service touches.',
            'image' => asset('images/suite_room.jpg')
        ]
    ];
@endphp

<div class="d-flex flex-column gap-4">

    @foreach ($rooms as $room)
    <div class="card border-0 shadow-sm">

        <div class="row g-0">

            <!-- Image -->
            <div class="col-md-4">
                <img src="{{ $room['image'] }}"
                     class="img-fluid rounded-start w-100 h-100 object-fit-cover"
                     alt="{{ $room['name'] }}">
            </div>

            <!-- Content -->
            <div class="col-md-8 d-flex flex-column p-4">

                <h3 class="fw-semibold">{{ $room['name'] }}</h3>

                <p class="text-muted mb-4">{{ $room['description'] }}</p>

                <div class="mt-auto">
                    <a href="{{ route('hotel.book.room') }}" class="btn btn-primary px-4 py-2">
                        Book Now
                    </a>
                </div>

            </div>
        </div>

    </div>
    @endforeach

</div>

@endsection
