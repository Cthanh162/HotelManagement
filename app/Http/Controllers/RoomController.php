<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Hotel;
use App\Models\Floor;
use App\Http\Requests\Room\CreateRoomRequest;
use App\Http\Requests\Room\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OAT;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class RoomController extends Controller
{
    #[OAT\Post(
        path: '/api/rooms',
        tags: ['rooms'],
        summary: 'Create a new room with image upload',
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
                            description: 'Images for the room'
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
                            property: 'price',
                            type: 'number',
                            format: 'float',
                            description: 'Room price',
                            example: 150.00
                        ),
                        new OAT\Property(
                            property: 'description',
                            type: 'string',
                            description: 'Room description',
                            example: 'A beautiful deluxe room with ocean view.'
                        )
                    ]
                )
            )
        ),
        responses: [
            new OAT\Response(
                response: HttpResponse::HTTP_CREATED,
                description: 'Room created with images',
                content: new OAT\JsonContent(ref: '#/components/schemas/Room')
            ),
            new OAT\Response(
                response: HttpResponse::HTTP_BAD_REQUEST,
                description: 'Invalid hotelId or floorId',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Invalid hotelId or floorId.'),
                        new OAT\Property(property: 'errors', type: 'object', example:  "hotelId:The hotel does not exist.")
                    ]
                )
            )
        ]
    )]
    public function store(CreateRoomRequest $request)
    {
        $data = $request->validated();

        // Kiểm tra tính hợp lệ của hotelId và floorId
        $hotel = Hotel::find($data['hotelId']);
        $floors = DB::table('floors')->where('id', $data['floorId'])->first();


        if (!$hotel) {
            return response()->json([
                'message' => 'Invalid hotelId or floorId.',
                'errors' => [
                    'hotelId' => ['The hotel does not exist.']
                ]
            ], 400);
        }

        if (!$floors || $floors->hotelId !== $hotel->id) {
            return response()->json([
                'message' => 'Invalid hotelId or floorId.',
                'errors' => [
                    'floorId' => ['The floor does not belong to the provided hotel.']
                ]
            ], 400);
        }


        // Upload ảnh
        $images = [];
        if ($request->hasFile('roomImages')) {
            foreach ($request->file('roomImages') as $img) {
                $images[] = $img->store('rooms/images', 'public');
            }
        }

        // Upload video
        $video = $request->hasFile('roomVideo')
            ? $request->file('roomVideo')->store('rooms/videos', 'public')
            : null;

        $room = Room::create(array_merge($data, [
           'roomImages' => json_encode($images),
            'roomVideo' => $video,
        ]));

        return response()->json(new RoomResource($room), 201);
    }

    #[OAT\Get(
        path: '/api/rooms/{roomId}',
        tags: ['rooms'],
        summary: 'Get a room by ID',
        parameters: [
            new OAT\Parameter(
                name: 'roomId',
                in: 'path',
                required: true,
                description: 'Room ID',
                schema: new OAT\Schema(type: 'integer')
            )
        ],
        responses: [
            new OAT\Response(
                response: HttpResponse::HTTP_OK,
                description: 'Room retrieved',
                content: new OAT\JsonContent(ref: '#/components/schemas/Room')
            ),
            new OAT\Response(
                response: HttpResponse::HTTP_NOT_FOUND,
                description: 'Room not found'
            )
        ]
    )]
    public function show(Room $room)
    {
        return new RoomResource($room->load(['hotel', 'floor']));
    }

    #[OAT\Put(
        path: '/api/rooms/{roomId}',
        tags: ['rooms'],
        summary: 'Update an existing room',
        parameters: [
            new OAT\Parameter(
                name: 'roomId',
                in: 'path',
                required: true,
                description: 'Room ID',
                schema: new OAT\Schema(type: 'integer')
            )
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/UpdateRoomRequest')
        ),
        responses: [
            new OAT\Response(
                response: HttpResponse::HTTP_OK,
                description: 'Room updated',
                content: new OAT\JsonContent(ref: '#/components/schemas/Room')
            ),
            new OAT\Response(
                response: HttpResponse::HTTP_NOT_FOUND,
                description: 'Room not found'
            )
        ]
    )]
    public function update(UpdateRoomRequest $request, Room $room)
    {
        $data = $request->validated();

        // Xoá ảnh cũ nếu có
        if ($request->hasFile('roomImages')) {
            foreach (json_decode($room->roomImages ?? '[]') as $oldImg) {
                if (Storage::disk('public')->exists($oldImg)) {
                    Storage::disk('public')->delete($oldImg);
                }
            }
            $newImages = [];
            foreach ($request->file('roomImages') as $img) {
                $newImages[] = $img->store('rooms/images', 'public');
            }
            $room->roomImages = $newImages;
        }

        // Xoá video cũ nếu có
        if ($request->hasFile('roomVideo')) {
            if ($room->roomVideo && Storage::disk('public')->exists($room->roomVideo)) {
                Storage::disk('public')->delete($room->roomVideo);
            }
            $room->roomVideo = $request->file('roomVideo')->store('rooms/videos', 'public');
        }

        $room->update($data);
        $room->save();

        return new RoomResource($room);
    }

    #[OAT\Delete(
        path: '/api/rooms/{roomId}',
        tags: ['rooms'],
        summary: 'Delete a room by ID',
        parameters: [
            new OAT\Parameter(
                name: 'roomId',
                in: 'path',
                required: true,
                description: 'Room ID',
                schema: new OAT\Schema(type: 'integer')
            )
        ],
        responses: [
            new OAT\Response(
                response: HttpResponse::HTTP_NO_CONTENT,
                description: 'Room deleted'
            ),
            new OAT\Response(
                response: HttpResponse::HTTP_NOT_FOUND,
                description: 'Room not found'
            )
        ]
    )]
    public function destroy(Room $room)
    {
        // Xoá file vật lý
        foreach (json_decode($room->roomImages ?? '[]') as $img) {
            if (Storage::disk('public')->exists($img)) {
                Storage::disk('public')->delete($img);
            }
        }

        if ($room->roomVideo && Storage::disk('public')->exists($room->roomVideo)) {
            Storage::disk('public')->delete($room->roomVideo);
        }

        $room->delete();

        return response()->noContent();
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
        $rooms = Room::all();
        return RoomResource::collection($rooms);
    }
}
