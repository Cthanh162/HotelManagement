<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\CreateBookingRequest;
use App\Http\Requests\Booking\UploadPaymentRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Cloudinary\Cloudinary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use OpenApi\Attributes as OAT;

class BookingController extends Controller
{
    #[OAT\Post(
    path: '/api/bookings',
    summary: 'Tạo một đặt phòng mới',
    tags: ['Booking'],
    requestBody: new OAT\RequestBody(
        required: true,
        content: new OAT\MediaType(
            mediaType: 'application/json',
            schema: new OAT\Schema(
                type: 'object',
                properties: [
                    new OAT\Property(
                        property: 'roomId',
                        type: 'integer',
                        description: 'ID của phòng',
                        example: 17
                    ),
                    new OAT\Property(
                        property: 'userId',
                        type: 'integer',
                        description: 'ID người dùng',
                        example: 1
                    ),
                    new OAT\Property(
                        property: 'checkinTime',
                        type: 'string',
                        format: 'date-time',
                        description: 'Thời gian check-in',
                        example: '2025-06-11T14:00:00Z'
                    ),
                    new OAT\Property(
                        property: 'checkoutTime',
                        type: 'string',
                        format: 'date-time',
                        description: 'Thời gian check-out',
                        example: '2025-06-12T12:00:00Z'
                    ),
                    new OAT\Property(
                        property: 'totalPrice',
                        type: 'number',
                        format: 'float',
                        description: 'Tổng giá (có thể được tính lại bởi server)',
                        example: 150.00
                    ),
                    new OAT\Property(
                        property: 'name',
                        type: 'string',
                        description: 'Tên người đặt',
                        example: 'Nguyen Van A'
                    ),
                    new OAT\Property(
                        property: 'phoneNumber',
                        type: 'string',
                        description: 'Số điện thoại',
                        example: '0901234567'
                    ),
                    new OAT\Property(
                        property: 'cccd',
                        type: 'string',
                        description: 'Số CCCD',
                        example: '123456789'
                    )
                ],
                required: ['roomId', 'checkinTime', 'checkoutTime', 'name', 'phoneNumber', 'cccd']
            )
        )
    ),
    responses: [
        new OAT\Response(response: HttpResponse::HTTP_CREATED, description: 'Đặt phòng được tạo', content: new OAT\JsonContent(ref: '#/components/schemas/Booking')),
        new OAT\Response(response: HttpResponse::HTTP_BAD_REQUEST, description: 'Yêu cầu không hợp lệ'),
        new OAT\Response(response: HttpResponse::HTTP_NOT_FOUND, description: 'Phòng không tồn tại'),
        new OAT\Response(response: HttpResponse::HTTP_CONFLICT, description: 'Phòng đã được đặt trong thời gian này')
    ]
    )]
    public function store(CreateBookingRequest $request)
    {
        $data = $request->validated();

        // Tìm phòng
        $room = Room::find($data['roomId']);
        if (!$room) {
            return response()->json(['message' => 'Phòng không tồn tại.'], 404);
        }

        // Kiểm tra trạng thái phòng
        if ($room->status !== 'available') {
            return response()->json(['message' => 'Phòng không khả dụng để đặt.'], 400);
        }

        // Chuyển đổi thời gian sang Carbon
        $checkinTime = Carbon::parse($data['checkinTime']);
        $checkoutTime = Carbon::parse($data['checkoutTime']);

        // Kiểm tra thời gian hợp lệ
        if ($checkoutTime->lessThanOrEqualTo($checkinTime)) {
            return response()->json(['message' => 'Thời gian check-out phải sau thời gian check-in.'], 400);
        }

        // Kiểm tra trùng lịch đặt phòng
        $overlap = Booking::where('roomId', $room->id)
            ->where('status', 'CONFIRMED')
            ->where(function ($query) use ($checkinTime, $checkoutTime) {
                $query->whereBetween('checkinTime', [$checkinTime, $checkoutTime])
                    ->orWhereBetween('checkoutTime', [$checkinTime, $checkoutTime])
                    ->orWhere(function ($query2) use ($checkinTime, $checkoutTime) {
                        $query2->where('checkinTime', '<', $checkinTime)
                                ->where('checkoutTime', '>', $checkoutTime);
                    });
            })
            ->exists();

        if ($overlap) {
            return response()->json(['message' => 'Phòng đã được đặt trong thời gian này.'], 409);
        }

        // Tính tổng giá (giữ nguyên logic từ trước)
        $basePrice = $room->price;
        $totalPrice = 0;

        $standardCheckin = Carbon::parse($checkinTime->format('Y-m-d') . ' 14:00:00');
        $standardCheckout = Carbon::parse($checkoutTime->format('Y-m-d') . ' 12:00:00')->addDay();
        if ($checkinTime->greaterThan($standardCheckin)) {
            $standardCheckin = $standardCheckin->addDay();
        }
        if ($checkoutTime->lessThan($standardCheckout)) {
            $standardCheckout = $standardCheckout->subDay();
        }
        $days = max(0, $standardCheckout->diffInDays($standardCheckin));
        $totalPrice += $days * $basePrice;

        $earlyCheckinFee = 0;
        if ($checkinTime->lessThan($standardCheckin)) {
            $earlyHour = $standardCheckin->diffInHours($checkinTime);
            if ($earlyHour >= 5 && $earlyHour < 9) {
                $earlyCheckinFee = $basePrice * 0.5;
                Log::info("Phí check-in sớm 50%: {$earlyCheckinFee}");
            } elseif ($earlyHour >= 0 && $earlyHour < 5) {
                $earlyCheckinFee = $basePrice * 0.3;
                Log::info("Phí check-in sớm 30%: {$earlyCheckinFee}");
            }
        }
        $totalPrice += $earlyCheckinFee;

        $lateCheckoutFee = 0;
        if ($checkoutTime->greaterThan($standardCheckout)) {
            $lateHour = $checkoutTime->diffInHours($standardCheckout);
            if ($lateHour > 0 && $lateHour <= 3) {
                $lateCheckoutFee = $basePrice * 0.3;
                Log::info("Phí check-out trễ 30%: {$lateCheckoutFee}");
            } elseif ($lateHour > 3 && $lateHour <= 6) {
                $lateCheckoutFee = $basePrice * 0.5;
                Log::info("Phí check-out trễ 50%: {$lateCheckoutFee}");
            } elseif ($lateHour > 6) {
                $lateCheckoutFee = $basePrice;
                Log::info("Phí check-out trễ 100%: {$lateCheckoutFee}");
            }
        }
        $totalPrice += $lateCheckoutFee;

        // Tạo booking với paymentProof null ban đầu
        $booking = Booking::create([
            'roomId' => $data['roomId'],
            'userId' => $data['userId'],
            'checkinTime' => $checkinTime,
            'checkoutTime' => $checkoutTime,
            'status' => 'pending_payment',
            'paymentStatus' => 'pending',
            'totalPrice' => $totalPrice,
            'paymentProof' => null,
            'Name' => $data['Name'],
            'phone' => $data['phone'],
            'cccd' => $data['cccd'],
            'createdBy' => auth()->id(),
            'created_at' => now(),
        ]);

        // Cập nhật trạng thái phòng
        $room->update(['status' => 'pending_payment']);

        Log::info("Đặt phòng thành công, Booking ID: {$booking->id}, Tổng giá: {$totalPrice}");

        return (new BookingResource($booking))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    


    #[OAT\Post(
        path: '/api/bookings/{id}/upload-payment',
        summary: 'Upload payment proof for a booking',
        tags: ['Booking'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID of the booking',
                schema: new OAT\Schema(type: 'integer')
            )
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OAT\Schema(
                    type: 'object',
                    properties: [
                        new OAT\Property(
                            property: 'paymentProof',
                            type: 'string',
                            format: 'binary',
                            description: 'Ảnh bằng chứng thanh toán',
                            example: 'image.jpg'
                        )
                    ],
                    required: ['paymentProof']
                )
            )
        ),
        responses: [
            new OAT\Response(response: HttpResponse::HTTP_OK, description: 'Payment proof uploaded', content: new OAT\JsonContent(ref: '#/components/schemas/Booking')),
            new OAT\Response(response: HttpResponse::HTTP_NOT_FOUND, description: 'Booking not found'),
            new OAT\Response(response: HttpResponse::HTTP_BAD_REQUEST, description: 'Invalid request or payment already uploaded')
        ]
    )]
    public function uploadPayment(UploadPaymentRequest $request, $bookingId)
    {
        $booking = Booking::find($bookingId);
        if (!$booking) {
            return response()->json(['message' => 'Đặt phòng không tồn tại.'], 404);
        }

        if ($booking->paymentProof) {
            return response()->json(['message' => 'Bằng chứng thanh toán đã được upload.'], 400);
        }

        if ($booking->status !== 'pending_payment') {
            return response()->json(['message' => 'Trạng thái đặt phòng không cho phép upload thanh toán.'], 400);
        }

        // Upload ảnh lên Cloudinary
        try {
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('services.cloudinary.cloud_name'),
                    'api_key' => config('services.cloudinary.api_key'),
                    'api_secret' => config('services.cloudinary.api_secret'),
                ],
            ]);

            $uploadResult = $cloudinary->uploadApi()->upload($request->file('paymentProof')->getRealPath(), [
                'folder' => 'payment_proofs',
            ]);
            $paymentProofUrl = $uploadResult['secure_url'];

            // Cập nhật booking
            $booking->update([
                'paymentProof' => $paymentProofUrl,
                'paymentStatus' => 'pending_approval',
            ]);

            Log::info("Upload bằng chứng thanh toán thành công, Booking ID: {$bookingId}, URL: {$paymentProofUrl}");

            return new BookingResource($booking);
        } catch (\Exception $e) {
            Log::error("Lỗi khi upload bằng chứng thanh toán: {$e->getMessage()}");
            return response()->json(['message' => 'Lỗi khi upload bằng chứng thanh toán.'], 500);
        }
    }



    #[OAT\Get(
    path: '/api/bookings/pending',
    summary: 'Get bookings with uploaded payment proof and pending approval',
    tags: ['Booking'],
    responses: [
        new OAT\Response(
            response: HttpResponse::HTTP_OK,
            description: 'List of bookings with payment proof and pending status',
            content: new OAT\JsonContent(type: 'array', items: new OAT\Items(ref: '#/components/schemas/Booking'))
        )
    ]
    )]
    public function getPendingPayments(Request $request)
    {
        $bookings = Booking::whereNotNull('paymentProof')
            ->where('paymentStatus', 'pending_approval')
            ->get();

        return BookingResource::collection($bookings);
    }
    #[OAT\Get(
    path: '/api/bookings/user/{userId}',
    summary: 'Lấy danh sách booking theo user',
    tags: ['Booking'],
    parameters: [
        new OAT\Parameter(name: 'userId', in: 'path', required: true, schema: new OAT\Schema(type: 'integer'))
    ],
    responses: [
        new OAT\Response(
            response: 200,
            description: 'Danh sách đặt phòng',
            content: new OAT\JsonContent(type: 'array', items: new OAT\Items(ref: '#/components/schemas/Booking'))
        )
    ]
)]
public function getByUser($userId)
{
    $bookings = Booking::with('room')->where('userId', $userId)->orderBy('checkinTime', 'desc')->get();
    return BookingResource::collection($bookings);
}
    #[OAT\Get(
    path: '/api/bookings/test',
    summary: 'Get bookings with uploaded payment proof and pending approval',
    tags: ['Booking'],
    responses: [
        new OAT\Response(
            response: HttpResponse::HTTP_OK,
            description: 'List of bookings with payment proof and pending status',
            content: new OAT\JsonContent(type: 'array', items: new OAT\Items(ref: '#/components/schemas/Booking'))
        )
    ]
    )]
    public function getTest(Request $request)
    {
        Log::info('Hit getTest'); 
        $bookings = Booking::whereNotNull('paymentProof')
            ->where('paymentStatus', 'pending_payment')
            ->get();

        return BookingResource::collection($bookings);
    }
    #[OAT\Put(
        path: '/api/bookings/{id}/approve',
        summary: 'Duyệt thanh toán cho đặt phòng',
        tags: ['Booking'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID của đặt phòng',
                schema: new OAT\Schema(type: 'integer')
            )
        ],
        responses: [
            new OAT\Response(response: HttpResponse::HTTP_OK, description: 'Thanh toán được duyệt', content: new OAT\JsonContent(ref: '#/components/schemas/Booking')),
            new OAT\Response(response: HttpResponse::HTTP_NOT_FOUND, description: 'Đặt phòng không tồn tại'),
            new OAT\Response(response: HttpResponse::HTTP_BAD_REQUEST, description: 'Trạng thái không hợp lệ để duyệt')
        ]
    )]
    public function approvePayment($bookingId)
    {
        $booking = Booking::find($bookingId);
        if (!$booking) {
            return response()->json(['message' => 'Đặt phòng không tồn tại.'], 404);
        }

        if ($booking->status !== 'pending_payment' || !$booking->paymentProof) {
            return response()->json(['message' => 'Trạng thái không hợp lệ để duyệt thanh toán.'], 400);
        }

        $room = Room::find($booking->roomId);
        $booking->update([
            'status' => 'confirmed',
            'paymentStatus' => 'paid',
        ]);
        $room->update(['status' => 'booked']);

        Log::info("Duyệt thanh toán thành công, Booking ID: {$bookingId}");

        return new BookingResource($booking);
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
        $bookings = Booking::with('room')->get();
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
        $booking = Booking::with('room')->find($id);
        
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
            new OAT\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer')
            )
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/CreateBookingRequest')
        ),
        responses: [
            new OAT\Response(
                response: HttpResponse::HTTP_OK,
                description: 'Booking updated',
                content: new OAT\JsonContent(ref: '#/components/schemas/Booking')
            ),
            new OAT\Response(
                response: HttpResponse::HTTP_NOT_FOUND,
                description: 'Booking not found'
            ),
            new OAT\Response(
                response: HttpResponse::HTTP_BAD_REQUEST,
                description: 'Invalid booking update (room not available or overlap)'
            )
        ]
    )]
    public function update($id, CreateBookingRequest $request)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $data = $request->validated();

        $room = Room::find($data['roomId']);
        if (!$room) {
            return response()->json(['message' => 'Room not found.'], HttpResponse::HTTP_NOT_FOUND);
        }

        // if ($room->status !== 'AVAILABLE') {
        //     return response()->json(['message' => 'Room is not available.'], HttpResponse::HTTP_BAD_REQUEST);
        // }

        $overlap = Booking::where('roomId', $room->roomId)
            ->where('id', '!=', $booking->id)
            ->where('status', 'CONFIRMED')
            ->where(function ($query) use ($data) {
                $query->whereBetween('checkinTime', [$data['checkinTime'], $data['checkoutTime']])
                    ->orWhereBetween('checkoutTime', [$data['checkinTime'], $data['checkoutTime']])
                    ->orWhere(function ($q) use ($data) {
                        $q->where('checkinTime', '<', $data['checkinTime'])
                            ->where('checkoutTime', '>', $data['checkoutTime']);
                    });
            })
            ->exists();

        if ($overlap) {
            return response()->json(['message' => 'Room is already booked during this time.'], HttpResponse::HTTP_BAD_REQUEST);
        }
    if (isset($data['status'])) {
        $booking->status = $data['status'];
    }

    if (isset($data['paymentStatus'])) {
        $booking->paymentStatus = $data['paymentStatus'];
    }
    $booking->fill($data);
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
     #[OAT\Put(
        path: '/api/bookings/{id}/approve',
        summary: 'Admin duyệt đặt phòng',
        tags: ['Booking'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID đặt phòng',
                schema: new OAT\Schema(type: 'integer')
            )
        ],
        responses: [
            new OAT\Response(
                response: HttpResponse::HTTP_OK,
                description: 'Duyệt đặt phòng thành công',
                content: new OAT\JsonContent(ref: '#/components/schemas/Booking')
            ),
            new OAT\Response(
                response: HttpResponse::HTTP_BAD_REQUEST,
                description: 'Dữ liệu không hợp lệ'
            )
        ]
    )]
    public function approve(int $bookingId)
    {
        $booking = Booking::find($bookingId);

        if (!$booking || $booking->status !== 'pending_payment') {
            return response()->json(['message' => 'Invalid booking'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $room = Room::find($booking->roomId);
        if (!$room) {
            return response()->json(['message' => 'Room not found'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $booking->update([
            'status' => 'confirmed',
            'paymentStatus' => 'paid'
        ]);

        $room->update(['status' => 'booked']);

        return response()->json(new BookingResource($booking), HttpResponse::HTTP_OK);
    }

}