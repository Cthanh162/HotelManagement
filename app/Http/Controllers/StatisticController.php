<?php

namespace App\Http\Controllers;

use App\Http\Resources\RevenueStatResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use OpenApi\Attributes as OAT;

class StatisticController extends Controller
{
    // API thống kê doanh thu theo ngày cho từng khách sạn
    #[OAT\Get(
        path: '/api/statistics/revenue/daily',
        summary: 'Thống kê doanh thu theo ngày',
        tags: ['Statistics'],
        parameters: [
            new OAT\Parameter(name: 'date', in: 'query', required: true, schema: new OAT\Schema(type: 'string', format: 'date'), example: '2025-05-01'),
            new OAT\Parameter(name: 'hotelId', in: 'query', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'ID của khách sạn')
        ],
        responses: [
            new OAT\Response(
                response: HttpResponse::HTTP_OK,
                description: 'Doanh thu theo ngày của khách sạn',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(ref: '#/components/schemas/RevenueStat')
                )
            ),
            new OAT\Response(response: HttpResponse::HTTP_BAD_REQUEST, description: 'Dữ liệu không hợp lệ')
        ]
    )]
    public function revenueByDay(Request $request)
    {
        $date = $request->query('date');
        $hotelId = $request->query('hotelId');

        if (!$date) {
            return response()->json(['error' => 'Date is required'], HttpResponse::HTTP_BAD_REQUEST);
        }

        if (!$hotelId) {
            return response()->json(['error' => 'Hotel ID is required'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $data = DB::table('Bookings')
            ->join('Rooms', 'Bookings.roomId', '=', 'Rooms.id')
            ->join('Hotels', 'Rooms.hotelId', '=', 'Hotels.id')
            ->select('Hotels.id as hotel_id', 'Hotels.name as hotel_name', DB::raw('SUM(Bookings.totalPrice) as total_revenue'))
            ->whereDate('Bookings.checkinDate', $date)
            ->where('Hotels.id', $hotelId)
            ->groupBy('Hotels.id', 'Hotels.name')
            ->get()
            ->map(function ($item) use ($date) {
                $item->date = $date;
                return $item;
            });

        return RevenueStatResource::collection($data);
    }

    // API thống kê doanh thu theo tháng cho từng khách sạn
    #[OAT\Get(
        path: '/api/statistics/revenue/monthly',
        summary: 'Thống kê doanh thu theo tháng',
        tags: ['Statistics'],
        parameters: [
            new OAT\Parameter(name: 'month', in: 'query', required: true, schema: new OAT\Schema(type: 'string', example: '2025-05'), description: 'Tháng cho thống kê doanh thu (format: YYYY-MM)'),
            new OAT\Parameter(name: 'hotelId', in: 'query', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'ID của khách sạn')
        ],
        responses: [
            new OAT\Response(
                response: HttpResponse::HTTP_OK,
                description: 'Doanh thu theo tháng của khách sạn',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(ref: '#/components/schemas/RevenueStat')
                )
            ),
            new OAT\Response(response: HttpResponse::HTTP_BAD_REQUEST, description: 'Dữ liệu không hợp lệ')
        ]
    )]
    public function revenueByMonth(Request $request)
    {
        $month = $request->query('month');
        $hotelId = $request->query('hotelId');

        if (!$month || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            return response()->json(['error' => 'Month must be in format YYYY-MM'], HttpResponse::HTTP_BAD_REQUEST);
        }

        if (!$hotelId) {
            return response()->json(['error' => 'Hotel ID is required'], HttpResponse::HTTP_BAD_REQUEST);
        }

        [$year, $monthNum] = explode('-', $month);

        $data = DB::table('Bookings')
            ->join('Rooms', 'Bookings.roomId', '=', 'Rooms.id')
            ->join('Hotels', 'Rooms.hotelId', '=', 'Hotels.id')
            ->select('Hotels.id as hotel_id', 'Hotels.name as hotel_name', DB::raw('SUM(Bookings.totalPrice) as total_revenue'))
            ->whereYear('Bookings.checkinDate', $year)
            ->whereMonth('Bookings.checkinDate', $monthNum)
            ->where('Hotels.id', $hotelId)
            ->groupBy('Hotels.id', 'Hotels.name')
            ->get()
            ->map(function ($item) use ($month) {
                $item->date = $month;
                return $item;
            });

        return RevenueStatResource::collection($data);
    }
}