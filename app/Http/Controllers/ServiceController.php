<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Resources\ServiceResource;

class ServiceController extends Controller
{
    public function index()
    {
        return ServiceResource::collection(Service::all());
    }

    public function show($id)
    {
        $service = Service::findOrFail($id);
        return new ServiceResource($service);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'status' => 'nullable|in:active,inactive',
            'price'  => 'nullable|numeric|min:0',
        ]);

        $service = Service::create($data);
        return response()->json(new ServiceResource($service), 201);
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'status' => 'nullable|in:active,inactive',
            'price'  => 'nullable|numeric|min:0',
        ]);

        $service->update($data);
        return new ServiceResource($service);
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();
        return response()->json(['message' => 'Xoá dịch vụ thành công']);
    }
    
}