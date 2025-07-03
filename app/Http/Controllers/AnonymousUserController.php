<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnonymousUserRequest;
use App\Http\Requests\UpdateAnonymousUserRequest;
use App\Http\Resources\AnonymousUserResource;
use App\Models\AnonymousUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AnonymousUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $anonymousUsers = AnonymousUser::paginate();
        return AnonymousUserResource::collection($anonymousUsers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnonymousUserRequest $request): AnonymousUserResource
    {
        $anonymousUser = AnonymousUser::create($request->validated());
        return new AnonymousUserResource($anonymousUser);
    }

    /**
     * Display the specified resource.
     */
    public function show(AnonymousUser $anonymousUser): AnonymousUserResource
    {
        return new AnonymousUserResource($anonymousUser);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnonymousUserRequest $request, AnonymousUser $anonymousUser): AnonymousUserResource
    {
        $anonymousUser->update($request->validated());
        return new AnonymousUserResource($anonymousUser);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnonymousUser $anonymousUser): JsonResponse
    {
        $anonymousUser->delete();
        return response()->json(['message' => 'Anonymous user deleted successfully']);
    }
} 