<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSignRequest;
use App\Http\Requests\UpdateSignRequest;
use App\Http\Resources\SignResource;
use App\Models\Sign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $signs = Sign::with(['user', 'anonymousUser'])->paginate();
        return SignResource::collection($signs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSignRequest $request): SignResource
    {
        $sign = Sign::create($request->validated());
        return new SignResource($sign->load(['user', 'anonymousUser']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Sign $sign): SignResource
    {
        return new SignResource($sign->load(['user', 'anonymousUser']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSignRequest $request, Sign $sign): SignResource
    {
        $sign->update($request->validated());
        return new SignResource($sign->load(['user', 'anonymousUser']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sign $sign): JsonResponse
    {
        $sign->delete();
        return response()->json(['message' => 'Sign deleted successfully']);
    }
} 