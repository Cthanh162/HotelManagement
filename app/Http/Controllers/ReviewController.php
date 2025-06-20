<?php

namespace App\Http\Controllers;

use App\Http\Requests\Review\CreateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use OpenApi\Attributes as OAT;

class ReviewController extends Controller
{
   #[OAT\Post(
        path: '/api/reviews',
        summary: 'Create a new review after checkout',
        tags: ['reviews'],
        operationId: 'ReviewController.store',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/CreateReviewRequest')
        ),
        responses: [
            new OAT\Response(
                response: HttpResponse::HTTP_CREATED,
                description: 'Review created',
                content: new OAT\JsonContent(ref: '#/components/schemas/Review')
            )
        ]
    )]
    public function store(CreateReviewRequest $request)
    {
        $hasCheckedOut = \App\Models\Booking::where('userId', Auth::id())
            ->where('roomId', $request->roomId)
            ->where('checkoutTime', '<=', now())
            ->where('paymentStatus', 'paid')
            ->exists();

        // if (!$hasCheckedOut) {
        //     return response()->json([
        //         'message' => 'You can only review a room after you have checked out and paid for it.'
        //     ], HttpResponse::HTTP_FORBIDDEN);
        // }

        $review = Review::create([
            'userId' => Auth::id(),
            'roomId' => $request->roomId,
            'rating' => $request->rating,
            'des' => $request->des,
            'createdAt' => now(),
        ]);

        return response(new ReviewResource($review), HttpResponse::HTTP_CREATED);
    }

    #[OAT\Get(
        path: '/api/reviews',
        summary: 'List all reviews or filter by roomId',
        tags: ['reviews'],
        operationId: 'ReviewController.index',
        parameters: [
            new OAT\Parameter(
                name: 'roomId',
                in: 'query',
                required: false,
                schema: new OAT\Schema(type: 'integer')
            )
        ],
        responses: [
            new OAT\Response(
                response: HttpResponse::HTTP_OK,
                description: 'List of reviews',
                content: new OAT\JsonContent(type: 'array', items: new OAT\Items(ref: '#/components/schemas/Review'))
            )
        ]
    )]
    public function index(Request $request)
    {
       $roomId = $request->query('roomId');
    $query = Review::query();

    if ($roomId) {
        $query->where('roomId', $roomId);
    }
 $reviews = Review::with(['user', 'room'])->get();
    return ReviewResource::collection($reviews);
    // return ReviewResource::collection($query->with('user')->get());
    }

    #[OAT\Get(
    path: '/api/reviews/{roomId}',
    summary: 'Lấy danh sách đánh giá theo phòng',
    tags: ['reviews'],
    operationId: 'ReviewController.getByRoom',
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
            response: HttpResponse::HTTP_OK,
            description: 'Danh sách đánh giá của phòng',
            content: new OAT\JsonContent(type: 'array', items: new OAT\Items(ref: '#/components/schemas/Review'))
        ),
        new OAT\Response(response: HttpResponse::HTTP_NOT_FOUND, description: 'Không có đánh giá nào')
    ]
)]
public function show($roomId)
{
    $reviews = \App\Models\Review::with('user', 'room')
        ->where('roomId', $roomId)
        ->orderByDesc('createdAt')
        ->get();

    if ($reviews->isEmpty()) {
        return response()->json(['message' => 'Không có đánh giá nào cho phòng này.'], HttpResponse::HTTP_NOT_FOUND);
    }

    return ReviewResource::collection($reviews);
}

    #[OAT\Delete(
        path: '/api/reviews/{id}',
        summary: 'Delete a review',
        tags: ['reviews'],
        operationId: 'ReviewController.destroy',
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer'))
        ],
        responses: [
            new OAT\Response(response: HttpResponse::HTTP_NO_CONTENT, description: 'Deleted')
        ]
    )]
    public function destroy($id)
    {
        Review::findOrFail($id)->delete();
        return response(null, HttpResponse::HTTP_NO_CONTENT);
    }
}