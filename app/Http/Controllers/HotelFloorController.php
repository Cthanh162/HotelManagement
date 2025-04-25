<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Floor;
use Illuminate\Http\Request;
use App\Http\Requests\Floor\CreateFloorRequest;
use App\Http\Requests\Floor\UpdateFloorRequest;
use App\Http\Resources\FloorResource;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use OpenApi\Attributes as OAT;

class HotelFloorController extends Controller
{
    #[OAT\Get(
        path: '/api/floors',
        tags: ['floors'],
        summary: 'Get all floors for a hotel',
        parameters: [
            new OAT\Parameter(name: 'hotelId', in: 'query', required: true, schema: new OAT\Schema(type: 'integer'))
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'List of floors',
                content: new OAT\JsonContent(type: 'array', items: new OAT\Items(ref: '#/components/schemas/Floor'))
            )
        ]
    )]
    public function index(Request $request)
    {
        $hotelId = $request->query('hotelId');
        
        if (!$hotelId) {
            return response()->json(['message' => 'Hotel ID is required.'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $floors = Floor::where('hotelId', $hotelId)->get();
        return FloorResource::collection($floors);
    }

    #[OAT\Post(
        path: '/api/floors',
        tags: ['floors'],
        summary: 'Create a new floor for a hotel',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/CreateFloorRequest')
        ),
        responses: [
            new OAT\Response(
                response: HttpResponse::HTTP_CREATED,
                description: 'Floor created',
                content: new OAT\JsonContent(ref: '#/components/schemas/Floor')
            )
        ]
    )]
    public function store(CreateFloorRequest $request)
    {
        $data = $request->validated();
        $hotelId = $data['hotelId'];

        $hotel = Hotel::find($hotelId);
        if (!$hotel) {
            return response()->json(['message' => 'Hotel not found.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $floor = Floor::create($data);
        return (new FloorResource($floor))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }
    #[OAT\Get(path: '/api/floors/{id}', tags: ['floors'], summary: 'Get floor by ID',
        parameters: [new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer'))],
        responses: [new OAT\Response(response: 200, description: 'Floor found', content: new OAT\JsonContent(ref: '#/components/schemas/Floor'))]
    )]
    public function show(Floor $floor)
    {
        return new FloorResource($floor);
    }

    #[OAT\Put(path: '/api/floors/{id}', tags: ['floors'], summary: 'Update floor',
        requestBody: new OAT\RequestBody(required: true, content: new OAT\JsonContent(ref: '#/components/schemas/UpdateFloorRequest')),
        parameters: [new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer'))],
        responses: [new OAT\Response(response: 200, description: 'Floor updated', content: new OAT\JsonContent(ref: '#/components/schemas/Floor'))]
    )]
    public function update(UpdateFloorRequest $request, Floor $floor)
    {
        $floor->update($request->validated());
        return new FloorResource($floor);
    }

    #[OAT\Delete(path: '/api/floors/{id}', tags: ['floors'], summary: 'Delete floor',
        parameters: [new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer'))],
        responses: [new OAT\Response(response: 204, description: 'Floor deleted')]
    )]
    public function destroy(Floor $floor)
    {
        $floor->delete();
        return response()->noContent();
    }
}
