<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;
use App\Http\Requests\Hotel\CreateHotelRequest;
use App\Http\Resources\HotelResource;
use OpenApi\Attributes as OAT;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class HotelController extends Controller
{
    #[OAT\Get(
        path: '/api/hotels',
        tags: ['hotels'],
        summary: 'List all hotels',
        operationId: 'HotelController.get',
        responses: [
            new OAT\Response(
                response: 200,
                description: 'List of hotels',
                content: new OAT\JsonContent(type: 'array', items: new OAT\Items(ref: '#/components/schemas/Hotel'))
            )
        ]
    )]
    public function index()
    {
        return HotelResource::collection(Hotel::all());
    }

    #[OAT\Post(
        path: '/api/hotels',
        tags: ['hotels'],
        summary: 'Create a new hotel',
        operationId: 'HotelController.create',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/CreateHotelRequest')
        ),
        responses: [
            new OAT\Response(
                response: HttpResponse::HTTP_CREATED,
                description: 'Hotel created',
                content: new OAT\JsonContent(ref: '#/components/schemas/Hotel')
            )
        ]
    )]
    public function store(CreateHotelRequest $request)
    {
        $hotel = Hotel::create($request->validated());
        return new HotelResource($hotel);
    }

    #[OAT\Get(
        path: '/api/hotels/{id}',
        tags: ['hotels'],
        summary: 'Get a specific hotel',
        operationId: 'HotelController.getById',
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer'))
        ],
        responses: [
            new OAT\Response(response: 200, description: 'Hotel detail', content: new OAT\JsonContent(ref: '#/components/schemas/Hotel')),
            new OAT\Response(response: 404, description: 'Not found')
        ]
    )]
    public function show($id)
    {
        $hotel = Hotel::findOrFail($id);
        return new HotelResource($hotel);
    }

    #[OAT\Put(
        path: '/api/hotels/{id}',
        tags: ['hotels'],
        summary: 'Update hotel info',
        operationId: 'HotelController.update',
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer'))
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/CreateHotelRequest')
        ),
        responses: [
            new OAT\Response(response: 200, description: 'Hotel updated', content: new OAT\JsonContent(ref: '#/components/schemas/Hotel')),
            new OAT\Response(response: 404, description: 'Not found')
        ]
    )]
    public function update(CreateHotelRequest $request, $id)
    {
        $hotel = Hotel::findOrFail($id);
        $hotel->update($request->validated());
        return new HotelResource($hotel);
    }

    #[OAT\Delete(
        path: '/api/hotels/{id}',
        tags: ['hotels'],
        summary: 'Delete a hotel',
        operationId: 'HotelController.delete',
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer'))
        ],
        responses: [
            new OAT\Response(response: 204, description: 'No Content'),
            new OAT\Response(response: 404, description: 'Not found')
        ]
    )]
    public function destroy($id)
    {
        $hotel = Hotel::findOrFail($id);
        $hotel->delete();
        return response()->noContent();
    }
}
