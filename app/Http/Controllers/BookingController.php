<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\CreateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use OpenApi\Attributes as OAT;

class BookingController extends Controller
{
    #[OAT\Post(
        path: '/api/bookings',
        summary: 'Create a new booking',
        tags: ['Booking'],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/CreateBookingRequest')
        ),
        responses: [
            new OAT\Response(response: HttpResponse::HTTP_CREATED, description: 'Booking created', content: new OAT\JsonContent(ref: '#/components/schemas/Booking')),
            new OAT\Response(response: HttpResponse::HTTP_BAD_REQUEST, description: 'Bad Request'),
        ]
    )]
    public function store(CreateBookingRequest $request)
    {
        $data = $request->validated();

        $room = Room::find($data['roomId']);

        if (!$room) {
            return response()->json(['message' => 'Room not found.'], 404);
        }

        // Kiểm tra trạng thái phòng
        if ($room->status !== 'a') {
            return response()->json(['message' => 'Room is not available for booking.'], 400);
        }

        // Kiểm tra trùng lịch đặt phòng
        $overlap = Booking::where('roomId', $room->id)
            ->where('status', 'CONFIRMED')
            ->where(function ($query) use ($data) {
                $query->whereBetween('checkinTime', [$data['checkinTime'], $data['checkoutTime']])
                      ->orWhereBetween('checkoutTime', [$data['checkinTime'], $data['checkoutTime']])
                      ->orWhere(function ($query2) use ($data) {
                          $query2->where('checkinTime', '<', $data['checkinTime'])
                                 ->where('checkoutTime', '>', $data['checkoutTime']);
                      });
            })
            ->exists();

        if ($overlap) {
            return response()->json(['message' => 'Room is already booked during this time.'], 400);
        }

        $booking = Booking::create([
            ...$data,
            'status' => 'PENDING',
            'paymentStatus' => 'UNPAID',
            'create_at' => now(),
            'createdBy' => auth()->user()?->id ?? null,
        ]);

        return (new BookingResource($booking))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    #[OAT\Patch(
        path: '/api/bookings/{id}/confirm',
        summary: 'Confirm a booking',
        tags: ['Booking'],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer'))
        ],
        responses: [
            new OAT\Response(response: HttpResponse::HTTP_OK, description: 'Booking confirmed', content: new OAT\JsonContent(ref: '#/components/schemas/Booking')),
            new OAT\Response(response: HttpResponse::HTTP_NOT_FOUND, description: 'Booking not found'),
        ]
    )]
    public function confirm($id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        if ($booking->status !== 'PENDING') {
            return response()->json(['message' => 'Only pending bookings can be confirmed.'], 400);
        }

        $booking->update(['status' => 'CONFIRMED']);

        $booking->room->update(['status' => 'BOOKED']);

        return new BookingResource($booking);
    }

    #[OAT\Patch(
        path: '/api/bookings/{id}/cancel',
        summary: 'Cancel a booking',
        tags: ['Booking'],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer'))
        ],
        responses: [
            new OAT\Response(response: HttpResponse::HTTP_OK, description: 'Booking canceled', content: new OAT\JsonContent(ref: '#/components/schemas/Booking')),
            new OAT\Response(response: HttpResponse::HTTP_NOT_FOUND, description: 'Booking not found'),
        ]
    )]
    public function cancel($id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        if (!in_array($booking->status, ['PENDING', 'CONFIRMED'])) {
            return response()->json(['message' => 'Only pending or confirmed bookings can be canceled.'], 400);
        }

        $booking->update(['status' => 'CANCELED']);

        if ($booking->room->status === 'BOOKED') {
            $booking->room->update(['status' => 'AVAILABLE']);
        }

        return new BookingResource($booking);
    }
    #[OAT\Get(
        path: '/api/bookings',
        summary: 'Get all bookings',
        tags: ['Booking'],
        responses: [
            new OAT\Response(response: HttpResponse::HTTP_OK, description: 'Booking list retrieved', content: new OAT\JsonContent(type: 'array', items: new OAT\Items(ref: '#/components/schemas/Booking'))),
        ]
    )]
    public function getAll()
    {
        $bookings = Booking::all();
        return BookingResource::collection($bookings);
    }
    #[OAT\Get(
        path: '/api/bookings/{id}',
        summary: 'Get booking by ID',
        tags: ['Booking'],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer'))
        ],
        responses: [
            new OAT\Response(response: HttpResponse::HTTP_OK, description: 'Booking retrieved', content: new OAT\JsonContent(ref: '#/components/schemas/Booking')),
            new OAT\Response(response: HttpResponse::HTTP_NOT_FOUND, description: 'Booking not found'),
        ]
    )]
    public function get($id)
    {
        $booking = Booking::find($id);
    
        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }
    
        return new BookingResource($booking);
    }
    #[OAT\Put(
        path: '/api/bookings/{id}',
        summary: 'Update a booking',
        tags: ['Booking'],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer'))
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/CreateBookingRequest')
        ),
        responses: [
            new OAT\Response(response: HttpResponse::HTTP_OK, description: 'Booking updated', content: new OAT\JsonContent(ref: '#/components/schemas/Booking')),
            new OAT\Response(response: HttpResponse::HTTP_NOT_FOUND, description: 'Booking not found'),
        ]
    )]
    public function update($id, CreateBookingRequest $request)
    {
        $booking = Booking::find($id);
    
        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }
    
        // Kiểm tra và cập nhật booking
        $data = $request->validated();
    
        $room = Room::find($data['roomId']);
        if (!$room) {
            return response()->json(['message' => 'Room not found.'], 404);
        }
    
        if ($room->status !== 'AVAILABLE') {
            return response()->json(['message' => 'Room is not available for booking.'], 400);
        }
    
        // Kiểm tra lại thời gian booking
        $overlap = Booking::where('roomId', $room->id)
            ->where('status', 'CONFIRMED')
            ->where(function ($query) use ($data) {
                $query->whereBetween('checkinTime', [$data['checkinTime'], $data['checkoutTime']])
                      ->orWhereBetween('checkoutTime', [$data['checkinTime'], $data['checkoutTime']])
                      ->orWhere(function ($query2) use ($data) {
                          $query2->where('checkinTime', '<', $data['checkinTime'])
                                 ->where('checkoutTime', '>', $data['checkoutTime']);
                      });
            })
            ->exists();
    
        if ($overlap) {
            return response()->json(['message' => 'Room is already booked during this time.'], 400);
        }
    
        $booking->update($data);
    
        return new BookingResource($booking);
    }
    #[OAT\Get(
        path: '/api/bookings/search',
        summary: 'Search bookings by hotel or floor',
        tags: ['Booking'],
        parameters: [
            new OAT\Parameter(name: 'hotelId', in: 'query', required: false, schema: new OAT\Schema(type: 'integer')),
            new OAT\Parameter(name: 'floor', in: 'query', required: false, schema: new OAT\Schema(type: 'integer')),
        ],
        responses: [
            new OAT\Response(response: HttpResponse::HTTP_OK, description: 'Bookings found', content: new OAT\JsonContent(type: 'array', items: new OAT\Items(ref: '#/components/schemas/Booking'))),
        ]
    )]
    public function search(Request $request)
    {
        $query = Booking::query();
    
        if ($request->has('hotelId')) {
            $query->whereHas('room', function ($q) use ($request) {
                $q->where('hotel_id', $request->hotelId);
            });
        }
    
        if ($request->has('floor')) {
            $query->whereHas('room', function ($q) use ($request) {
                $q->where('floor', $request->floor);
            });
        }
    
        $bookings = $query->get();
    
        return BookingResource::collection($bookings);
    }
}