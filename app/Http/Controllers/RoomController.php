<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Models\Room;
use Carbon\Carbon;
use App\Models\Hotel;
use Cloudinary\Cloudinary;
use App\Models\Floor;
use App\Http\Requests\Room\CreateRoomRequest;
use App\Http\Requests\Room\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OAT;

use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Http\JsonResponse;

class RoomController extends Controller
{
    #[OAT\Post(
        path: '/api/rooms',
        tags: ['rooms'],
        summary: 'Create a new room with image and video upload using Cloudinary',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OAT\Schema(
                    type: 'object',
                    properties: [
                        new OAT\Property(
                            property: 'hotelId',
                            type: 'integer',
                            description: 'ID of the hotel',
                            example: 1
                        ),
                        new OAT\Property(
                            property: 'floorId',
                            type: 'integer',
                            description: 'ID of the floor in the hotel',
                            example: 2
                        ),
                        new OAT\Property(
                            property: 'roomName',
                            type: 'string',
                            description: 'Room Name',
                            example: 'Deluxe Room'
                        ),
                        new OAT\Property(
                            property: 'roomImages',
                            type: 'array',
                            items: new OAT\Items(type: 'string', format: 'binary'),
                            description: 'Images for the room (send as roomImages[] for multiple files)'
                        ),
                        new OAT\Property(
                            property: 'roomVideo',
                            type: 'string',
                            format: 'binary',
                            description: 'Video for the room'
                        ),
                        new OAT\Property(
                            property: 'status',
                            type: 'string',
                            description: 'Room status (e.g., available, booked)',
                            example: 'available'
                        ),
                        new OAT\Property(
                            property: 'roomType',
                            type: 'string',
                            description: 'Type of the room (e.g., deluxe, standard)',
                            example: 'luxury'
                        ),
                        new OAT\Property(
                            property: 'capacity',
                            type: 'integer',
                            description: 'Number of people the room can accommodate',
                            example: 2
                        ),
                        new OAT\Property(
                            property: 'children',
                            type: 'integer',
                            description: 'Number of children the room can accommodate',
                            example: 2
                        ),
                        new OAT\Property(
                            property: 'adults',
                            type: 'integer',
                            description: 'Number of adults the room can accommodate',
                            example: 2
                        ),
                        new OAT\Property(
                            property: 'price',
                            type: 'number',
                            format: 'float',
                            description: 'Room price per night',
                            example: 150.00
                        ),
                        new OAT\Property(
                            property: 'description',
                            type: 'string',
                            description: 'Room description',
                            example: 'A beautiful deluxe room with ocean view.'
                        )
                    ],
                    required: ['hotelId', 'floorId', 'roomName', 'status', 'roomType', 'capacity', 'children', 'adults', 'price']
                )
            )
        ),
        responses: [
            new OAT\Response(
                response: HttpResponse::HTTP_CREATED,
                description: 'Room created successfully',
                content: new OAT\JsonContent(ref: '#/components/schemas/Room')
            ),
            new OAT\Response(
                response: HttpResponse::HTTP_BAD_REQUEST,
                description: 'Invalid input data',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Invalid hotelId or floorId.'),
                        new OAT\Property(
                            property: 'errors',
                            type: 'object',
                            example: [
                                'hotelId' => ['The hotel does not exist.'],
                                'floorId' => ['The floor does not belong to the provided hotel.']
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function store(CreateRoomRequest $request)
    {
        $data = $request->validated();

        // Khởi tạo Cloudinary
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('services.cloudinary.cloud_name'),
                'api_key' => config('services.cloudinary.api_key'),
                'api_secret' => config('services.cloudinary.api_secret'),
            ],
        ]);

        // Kiểm tra tính hợp lệ của hotelId và floorId
        $hotel = Hotel::find($data['hotelId']);
        $floors = DB::table('floors')->where('id', $data['floorId'])->first();

        if (($request->children + $request->adults) > $request->capacity) {
            return response()->json([
                'message' => 'Dung lượng không hợp lệ'
            ], 400);
        }

        if (!$hotel) {
            return response()->json([
                'message' => 'hotelId hoặc floorId không hợp lệ.',
                'errors' => [
                    'hotelId' => ['Khách sạn không tồn tại.']
                ]
            ], 400);
        }

        if (!$floors || $floors->hotelId !== $hotel->hotelId) {
            return response()->json([
                'message' => 'hotelId hoặc floorId không hợp lệ.',
                'errors' => [
                    'floorId' => ['Tầng không thuộc khách sạn đã chọn.']
                ]
            ], 400);
        }

        // Xử lý ảnh
        $images = [];
        $roomImages = $request->file('roomImages');
        if ($roomImages) {
            // Chuyển thành mảng nếu là file đơn
            $roomImages = is_array($roomImages) ? $roomImages : [$roomImages];
            Log::info('Số lượng ảnh nhận được: ' . count($roomImages));
            foreach ($roomImages as $img) {
                if ($img->isValid()) {
                    $uploadResult = $cloudinary->uploadApi()->upload($img->getRealPath());
                    $images[] = $uploadResult['secure_url'];
                    Log::info('Đường dẫn ảnh Cloudinary: ' . end($images));
                } else {
                    Log::warning('File ảnh không hợp lệ: ' . $img->getClientOriginalName());
                }
            }
        } else {
            Log::info('Không nhận được ảnh nào');
            Log::info('Danh sách file trong request: ' . json_encode($request->allFiles()));
        }

        // Xử lý video
        $videoPath = null;
        if ($request->hasFile('roomVideo')) {
            $uploadResult = $cloudinary->uploadApi()->upload($request->file('roomVideo')->getRealPath(), ['resource_type' => 'video']);
            $videoPath = $uploadResult['secure_url'];
            Log::info('Đường dẫn video Cloudinary: ' . $videoPath);
        } else {
            Log::info('Không nhận được video');
        }

        // Tạo phòng
        $room = Room::create(array_merge($data, [
            'roomImages' => $images,
            'roomVideo' => $videoPath,
        ]));
        if (!empty($data['services'])) {
            $room->services()->sync($data['services']);
        }
        return response()->json(new RoomResource($room->load('services', 'roomType')), 201);
    }



    #[OAT\Get(
        path: '/api/rooms/{roomId}',
        tags: ['rooms'],
        summary: 'Lấy thông tin phòng theo ID',
        parameters: [
            new OAT\Parameter(
                name: 'roomId',
                in: 'path',
                required: true,
                description: 'ID của phòng',
                schema: new OAT\Schema(type: 'integer')
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Phòng đã được lấy',
                content: new OAT\JsonContent(ref: '#/components/schemas/Room')
            ),
            new OAT\Response(
                response: 404,
                description: 'Không tìm thấy phòng',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Không tìm thấy phòng.')
                    ]
                )
            )
        ]
    )]
    public function show($roomId)
    {
        // Tìm phòng theo ID và tải quan hệ hotel, floor
        $room = Room::with(['hotel', 'floor'])->find($roomId);

        // Kiểm tra phòng tồn tại
        if (!$room) {
            Log::warning("Không tìm thấy phòng với ID: {$roomId}");
            return response()->json([
                'message' => 'Không tìm thấy phòng.'
            ], 404);
        }

        Log::info("Lấy thông tin phòng thành công, ID: {$roomId}");

        return new RoomResource($room);
    }

    #[OAT\Put(
        path: '/api/rooms/{roomId}',
        tags: ['rooms'],
        summary: 'Cập nhật một phòng hiện có',
        parameters: [
            new OAT\Parameter(
                name: 'roomId',
                in: 'path',
                required: true,
                description: 'ID của phòng',
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
                            property: 'hotelId',
                            type: 'integer',
                            description: 'ID của khách sạn',
                            example: 1
                        ),
                        new OAT\Property(
                            property: 'floorId',
                            type: 'integer',
                            description: 'ID của tầng trong khách sạn',
                            example: 2
                        ),
                        new OAT\Property(
                            property: 'roomName',
                            type: 'string',
                            description: 'Tên phòng',
                            example: 'Deluxe Room'
                        ),
                        new OAT\Property(
                            property: 'roomImages',
                            type: 'array',
                            items: new OAT\Items(type: 'string', format: 'binary'),
                            description: 'Mảng ảnh của phòng (gửi dưới dạng roomImages[] cho nhiều file)'
                        ),
                        new OAT\Property(
                            property: 'roomVideo',
                            type: 'string',
                            format: 'binary',
                            description: 'Video của phòng'
                        ),
                        new OAT\Property(
                            property: 'status',
                            type: 'string',
                            description: 'Trạng thái phòng (ví dụ: available, booked, maintenance)',
                            example: 'available'
                        ),
                        new OAT\Property(
                            property: 'roomType',
                            type: 'string',
                            description: 'Loại phòng (ví dụ: deluxe, standard, luxury)',
                            example: 'luxury'
                        ),
                        new OAT\Property(
                            property: 'capacity',
                            type: 'integer',
                            description: 'Số người phòng có thể chứa',
                            example: 2
                        ),
                        new OAT\Property(
                            property: 'children',
                            type: 'integer',
                            description: 'Số trẻ em phòng có thể chứa',
                            example: 2
                        ),
                        new OAT\Property(
                            property: 'adults',
                            type: 'integer',
                            description: 'Số người lớn phòng có thể chứa',
                            example: 2
                        ),
                        new OAT\Property(
                            property: 'price',
                            type: 'number',
                            format: 'float',
                            description: 'Giá phòng mỗi đêm',
                            example: 150.00
                        ),
                        new OAT\Property(
                            property: 'description',
                            type: 'string',
                            description: 'Mô tả phòng',
                            example: 'Phòng deluxe tuyệt đẹp với tầm nhìn ra biển.'
                        )
                    ]
                )
            )
        ),
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Phòng đã được cập nhật',
                content: new OAT\JsonContent(ref: '#/components/schemas/Room')
            ),
            new OAT\Response(
                response: 404,
                description: 'Không tìm thấy phòng',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Không tìm thấy phòng.')
                    ]
                )
            ),
            new OAT\Response(
                response: 400,
                description: 'Dữ liệu đầu vào không hợp lệ',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Invalid hotelId or floorId.'),
                        new OAT\Property(
                            property: 'errors',
                            type: 'object',
                            example: [
                                'hotelId' => ['Khách sạn không tồn tại.'],
                                'floorId' => ['Tầng không thuộc khách sạn đã chọn.']
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function update(UpdateRoomRequest $request, $roomId)
    {
        // Tìm phòng theo ID
        $room = Room::find($roomId);
        if (!$room) {
            return response()->json([
                'message' => 'Không tìm thấy phòng.'
            ], 404);
        }

        $data = $request->validated();

        // Khởi tạo Cloudinary
        try {
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('services.cloudinary.cloud_name'),
                    'api_key' => config('services.cloudinary.api_key'),
                    'api_secret' => config('services.cloudinary.api_secret'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khởi tạo Cloudinary: {$e->getMessage()}");
            // Tiếp tục xử lý nếu Cloudinary lỗi
        }

        // Kiểm tra tính hợp lệ của hotelId và floorId
        if (isset($data['hotelId']) || isset($data['floorId'])) {
            $hotel = Hotel::find($data['hotelId'] ?? $room->hotelId);
            $floors = DB::table('floors')->where('id', $data['floorId'] ?? $room->floorId)->first();

            if (($data['children'] ?? $room->children + $data['adults'] ?? $room->adults) > ($data['capacity'] ?? $room->capacity)) {
                return response()->json([
                    'message' => 'Dung lượng không hợp lệ'
                ], 400);
            }

            if (!$hotel) {
                return response()->json([
                    'message' => 'hotelId hoặc floorId không hợp lệ.',
                    'errors' => [
                        'hotelId' => ['Khách sạn không tồn tại.']
                    ]
                ], 400);
            }

            if (!$floors || $floors->hotelId !== $hotel->hotelId) {
                return response()->json([
                    'message' => 'hotelId hoặc floorId không hợp lệ.',
                    'errors' => [
                        'floorId' => ['Tầng không thuộc khách sạn đã chọn.']
                    ]
                ], 400);
            }
        }
        // Kiểm tra dung lượng hợp lệ
    $adults = $data['adults'] ?? $room->adults;
    $children = $data['children'] ?? $room->children;
    $capacity = $data['capacity'] ?? $room->capacity;
    if (($adults + $children) > $capacity) {
        return response()->json(['message' => 'Dung lượng không hợp lệ'], 400);
    }
        // Xử lý ảnh
        $images = $room->roomImages ?? [];
        if ($request->hasFile('roomImages')) {
            // Xóa ảnh cũ từ Cloudinary
            if (isset($cloudinary)) {
                foreach ($images as $imageUrl) {
                    try {
                        $publicId = $this->getCloudinaryPublicId($imageUrl, 'image');
                        if ($publicId) {
                            $cloudinary->uploadApi()->destroy($publicId);
                            Log::info("Đã xóa ảnh cũ từ Cloudinary: {$publicId}");
                        }
                    } catch (\Exception $e) {
                        Log::warning("Lỗi khi xóa ảnh cũ từ Cloudinary: {$imageUrl}, lỗi: {$e->getMessage()}");
                    }
                }
            }

            // Upload ảnh mới
            $newImages = [];
            $roomImages = $request->file('roomImages');
            $roomImages = is_array($roomImages) ? $roomImages : [$roomImages];
            Log::info('Số lượng ảnh nhận được: ' . count($roomImages));
            foreach ($roomImages as $img) {
                if ($img->isValid()) {
                    try {
                        $uploadResult = $cloudinary->uploadApi()->upload($img->getRealPath());
                        $newImages[] = $uploadResult['secure_url'];
                        Log::info('Đường dẫn ảnh mới Cloudinary: ' . end($newImages));
                    } catch (\Exception $e) {
                        Log::warning("Lỗi khi upload ảnh lên Cloudinary: {$img->getClientOriginalName()}, lỗi: {$e->getMessage()}");
                    }
                } else {
                    Log::warning('File ảnh không hợp lệ: ' . $img->getClientOriginalName());
                }
            }
            $images = $newImages;
        }

        // Xử lý video
        $videoPath = $room->roomVideo;
        if ($request->hasFile('roomVideo')) {
            // Xóa video cũ từ Cloudinary
            if ($videoPath && isset($cloudinary)) {
                try {
                    $publicId = $this->getCloudinaryPublicId($videoPath, 'video');
                    if ($publicId) {
                        $cloudinary->uploadApi()->destroy($publicId, ['resource_type' => 'video']);
                        Log::info("Đã xóa video cũ từ Cloudinary: {$publicId}");
                    }
                } catch (\Exception $e) {
                    Log::warning("Lỗi khi xóa video cũ từ Cloudinary: {$videoPath}, lỗi: {$e->getMessage()}");
                }
            }

            // Upload video mới
            try {
                $uploadResult = $cloudinary->uploadApi()->upload($request->file('roomVideo')->getRealPath(), ['resource_type' => 'video']);
                $videoPath = $uploadResult['secure_url'];
                Log::info('Đường dẫn video mới Cloudinary: ' . $videoPath);
            } catch (\Exception $e) {
                Log::warning("Lỗi khi upload video lên Cloudinary: {$e->getMessage()}");
            }
        }
Log::info('DATA UPDATE:', $data); 
        // Cập nhật phòng
        $room->update(array_merge($data, [
            'roomImages' => $images,
            'roomVideo' => $videoPath,
        ]));
        if ($request->has('services')) {
        $room->services()->sync($request->input('services'));
        }
        Log::info('ROOM SAU KHI UPDATE:', $room->toArray());
        return new RoomResource($room->load('services', 'roomType'));
    }

    #[OAT\Delete(
        path: '/api/rooms/{roomId}',
        tags: ['rooms'],
        summary: 'Xóa một phòng theo ID',
        parameters: [
            new OAT\Parameter(
                name: 'roomId',
                in: 'path',
                required: true,
                description: 'ID của phòng',
                schema: new OAT\Schema(type: 'integer')
            )
        ],
        responses: [
            new OAT\Response(
                response: 204,
                description: 'Phòng đã được xóa'
            ),
            new OAT\Response(
                response: 404,
                description: 'Không tìm thấy phòng',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Không tìm thấy phòng.')
                    ]
                )
            )
        ]
    )]
    public function destroy($roomId)
    {
        // Tìm phòng theo ID
        $room = Room::find($roomId);
        if (!$room) {
            return response()->json([
                'message' => 'Không tìm thấy phòng.'
            ], 404);
        }

        // Khởi tạo Cloudinary
        try {
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('services.cloudinary.cloud_name'),
                    'api_key' => config('services.cloudinary.api_key'),
                    'api_secret' => config('services.cloudinary.api_secret'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Lỗi khởi tạo Cloudinary: {$e->getMessage()}");
            // Tiếp tục xóa phòng ngay cả khi Cloudinary lỗi
        }

        // Xóa ảnh từ Cloudinary
        // $images = json_decode($room->roomImages ?? '[]', true);
        // if (!empty($images) && isset($cloudinary)) {
        //     foreach ($images as $imageUrl) {
        //         try {
        //             $publicId = $this->getCloudinaryPublicId($imageUrl, 'image');
        //             if ($publicId) {
        //                 $cloudinary->uploadApi()->destroy($publicId);
        //                 Log::info("Đã xóa ảnh từ Cloudinary: {$publicId}");
        //             } else {
        //                 Log::warning("Không thể trích xuất public ID từ URL ảnh: {$imageUrl}");
        //             }
        //         } catch (\Exception $e) {
        //             Log::warning("Lỗi khi xóa ảnh từ Cloudinary: {$imageUrl}, lỗi: {$e->getMessage()}");
        //         }
        //     }
        // }

        // Xóa video từ Cloudinary
        if ($room->roomVideo && isset($cloudinary)) {
            try {
                $publicId = $this->getCloudinaryPublicId($room->roomVideo, 'video');
                if ($publicId) {
                    $cloudinary->uploadApi()->destroy($publicId, ['resource_type' => 'video']);
                    Log::info("Đã xóa video từ Cloudinary: {$publicId}");
                } else {
                    Log::warning("Không thể trích xuất public ID từ URL video: {$room->roomVideo}");
                }
            } catch (\Exception $e) {
                Log::warning("Lỗi khi xóa video từ Cloudinary: {$room->roomVideo}, lỗi: {$e->getMessage()}");
            }
        }

        // Xóa phòng từ database
        try {
            $room->delete();
            Log::info("Đã xóa phòng  từ database");
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa phòng từ database: {$e->getMessage()}");
            return response()->json([
                'message' => 'Lỗi khi xóa phòng từ database.'
            ], 500);
        }

        return response()->noContent();
    }
    private function getCloudinaryPublicId($url, $resourceType)
    {
        // URL mẫu: https://res.cloudinary.com/your_cloud_name/image/upload/v1234567890/public_id.jpg
        $pattern = $resourceType === 'video'
            ? '#cloudinary\.com/[^/]+/video/upload/v\d+/([^/]+)\.\w+#'
            : '#cloudinary\.com/[^/]+/image/upload/v\d+/([^/]+)\.\w+#';

        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    #[OAT\Get(
        path: '/api/rooms',
        tags: ['rooms'],
        summary: 'Get all rooms',
        responses: [
            new OAT\Response(
                response: HttpResponse::HTTP_OK,
                description: 'Rooms retrieved',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(ref: '#/components/schemas/Room')
                )
            )
        ]
    )]
    public function index()
    {
        $rooms = Room::with('services','roomType')->where('status', 'available')->get();
        return RoomResource::collection($rooms);
    }
    public function getAll()
    {
        $rooms = Room::with('services','roomType')->get();
        return RoomResource::collection($rooms);
    }


    #[OAT\Get(
    path: '/api/rooms/most-booked',
    summary: 'Get most booked rooms',
    tags: ['Room'],
    responses: [
        new OAT\Response(response: 200, description: 'Most booked rooms', content: new OAT\JsonContent(type: 'array', items: new OAT\Items(ref: '#/components/schemas/Room')))
    ]
)]
public function getMostBookedRooms()
{
    $rooms = Room::withCount('Bookings')
        ->having('bookings_count', '>', 0)
        ->orderByDesc('bookings_count')
        ->take(6)
        ->get();

    return RoomResource::collection($rooms);
}
public function getTopRatedRooms()
{
    $rooms = Room::withAvg('Reviews', 'rating')
        ->having('reviews_avg_rating', '>', 0)
        ->orderByDesc('reviews_avg_rating')
        ->take(6)
        ->get();

    return RoomResource::collection($rooms);
}
    #[OAT\Get(
        path: '/api/rooms/search',
        summary: 'Search for available rooms with optional filters',
        tags: ['rooms'],
        parameters: [
            new OAT\Parameter(name: 'roomType', in: 'query', schema: new OAT\Schema(type: 'string'), required: false),
            new OAT\Parameter(name: 'capacity', in: 'query', schema: new OAT\Schema(type: 'integer'), required: false),
            new OAT\Parameter(name: 'adults', in: 'query', schema: new OAT\Schema(type: 'integer'), required: false),
            new OAT\Parameter(name: 'children', in: 'query', schema: new OAT\Schema(type: 'integer'), required: false),
            new OAT\Parameter(name: 'minPrice', in: 'query', schema: new OAT\Schema(type: 'number'), required: false),
            new OAT\Parameter(name: 'maxPrice', in: 'query', schema: new OAT\Schema(type: 'number'), required: false)
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'List of matching rooms',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(ref: '#/components/schemas/Room')
                )
            )
        ]
    )]
    public function search(Request $request)
{
    $query = Room::query();

    // Chỉ lấy phòng có hotelId = 1 và status = AVAILABLE
    $query->where('hotelId', 1)
          ->where('status', 'available');

    // Tìm kiếm theo từ khoá trong tên phòng hoặc mô tả (case-insensitive)
    if ($request->has('q') && $keyword = $request->input('q')) {
        $query->where(function ($q) use ($keyword) {
            $q->whereRaw('LOWER(roomName) LIKE ?', ['%' . strtolower($keyword) . '%'])
              ->orWhereRaw('LOWER(description) LIKE ?', ['%' . strtolower($keyword) . '%']);
        });
    }

    // Tìm theo loại phòng
    if ($request->filled('roomType')) {
        $query->whereHas('roomType', function ($q) use ($request) {
            $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->input('roomType')) . '%']);
        });
    }

    // Sức chứa
    if ($request->filled('capacity')) {
        $query->where('capacity', '<=', $request->input('capacity'));
    }

    // Khoảng giá
    if ($request->filled('minPrice')) {
        $query->where('price', '>=', $request->input('minPrice'));
    }
    if ($request->filled('maxPrice')) {
        $query->where('price', '<=', $request->input('maxPrice'));
    }

    // Eager load hình ảnh
      $rooms = $query->get();

    return RoomResource::collection($rooms->load('services', 'roomType'));
}
#[OAT\Get(  
    path: '/api/rooms/suggestions',
    tags: ['rooms'],
    summary: 'Get room suggestions by booking count and rating',
    responses: [
        new OAT\Response(
            response: HttpResponse::HTTP_OK,
            description: 'Suggested rooms',
            content: new OAT\JsonContent(
                type: 'array',
                items: new OAT\Items(ref: '#/components/schemas/Room')
            )
        )
    ]
)]
public function suggestions()
{
    // Lấy top 5 phòng được đặt nhiều nhất (tính theo số booking trạng thái đã đặt)
    $popularRooms = DB::table('Rooms')
        ->join('Bookings', function ($join) {
            $join->on('Rooms.id', '=', 'Bookings.roomId')
                 ->where('Bookings.status', '=', 'booked'); // trạng thái đã đặt
        })
        ->select('Rooms.*', DB::raw('COUNT(Bookings.id) as bookings_count'))
        ->groupBy('Rooms.id')
        ->orderByDesc('bookings_count')
        ->limit(5)
        ->get();

    // Lấy top 5 phòng có đánh giá trung bình cao nhất
    $topRatedRooms = DB::table('Rooms')
        ->join('Reviews', 'Rooms.id', '=', 'Reviews.roomId')
        ->select('Rooms.*', DB::raw('AVG(Reviews.rating) as avg_rating'))
        ->groupBy('Rooms.id')
        ->orderByDesc('avg_rating')
        ->limit(5)
        ->get();

    // Gộp 2 bộ dữ liệu, ưu tiên phòng trong cả 2 danh sách, tránh trùng lặp
    $suggestedRooms = $popularRooms->merge($topRatedRooms)
        ->unique('id')
        ->values();

    return RoomResource::collection($suggestedRooms);
}
public function getServices($id): JsonResponse
{
   $room = Room::with('services')->findOrFail($id);

    $services = $room->services->map(function ($service) {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'price' => $service->price, // Lấy trực tiếp từ bảng services
        ];
    });
    return response()->json(['data' => $services]);
}
public function getAvailableRooms(Request $request)
{
    $checkin = $request->input('checkin'); // format: Y-m-d H:i
    $checkout = $request->input('checkout');
    if (!$checkin || !$checkout) {
        return response()->json(['message' => 'Vui lòng nhập checkin và checkout'], 400);
    }

    $rooms = Room::whereDoesntHave('bookings', function ($q) use ($checkin, $checkout) {
        $q->where('status', '!=', 'cancelled')
          ->where(function ($q2) use ($checkin, $checkout) {
              $q2->whereBetween('checkinTime', [$checkin, $checkout])
                 ->orWhereBetween('checkoutTime', [$checkin, $checkout])
                 ->orWhereRaw('? BETWEEN checkinTime AND checkoutTime', [$checkin])
                 ->orWhereRaw('? BETWEEN checkinTime AND checkoutTime', [$checkout]);
          });
    })->get();

    return RoomResource::collection($rooms);
}
public function searchAvailable(Request $request)
{
    $query = Room::with(['roomType', 'services']);

    if ($request->q) {
        $query->where('roomName', 'like', '%' . $request->q . '%');
    }

    if ($request->roomType) {
        $query->whereHas('roomType', function ($q) use ($request) {
            $q->where('name', $request->roomType);
        });
    }

    if ($request->capacity) {
        $query->where('capacity', '>=', $request->capacity);
    }

    if ($request->minPrice) {
        $query->where('price', '>=', $request->minPrice);
    }

    if ($request->maxPrice) {
        $query->where('price', '<=', $request->maxPrice);
    }

    $checkin = $request->checkin ? Carbon::parse($request->checkin) : null;
    $checkout = $request->checkout ? Carbon::parse($request->checkout) : null;

    if ($checkin && $checkout) {
        $query->whereDoesntHave('Bookings', function ($q) use ($checkin, $checkout) {
        $q->where(function ($q2) use ($checkin, $checkout) {
            $q2->whereBetween('checkinTime', [$checkin, $checkout])
            ->orWhereBetween('checkoutTime', [$checkin, $checkout])
            ->orWhere(function ($q3) use ($checkin, $checkout) {
                $q3->where('checkinTime', '<=', $checkin)
                    ->where('checkoutTime', '>=', $checkout);
            });
        })
        ->where('status', '!=', 'completed'); 
    });
    }

    return RoomResource::collection($query->get());
}
}
