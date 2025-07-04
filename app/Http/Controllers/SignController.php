<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSignRequest;
use App\Http\Requests\UpdateSignRequest;
use App\Http\Resources\SignResource;
use App\Models\Sign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class SignController extends Controller
{
    /**
     * @group Signs
     * @title "List Signs"
     * @description "List all signs owned by the user"
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection<\App\Http\Resources\SignResource>
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Sign::class);
        $signs = Sign::ownedBy($request->user())
            ->with(['user'])
            ->paginate();
        return SignResource::collection($signs);
    }

    /**
     * @group Signs
     * @title "Create Sign"
     * @description "Create a new sign"
     * Store a newly created sign in storage.
     */
    public function store(StoreSignRequest $request): SignResource
    {
        Gate::authorize('create', Sign::class);
        $sign = Sign::create($request->validated() + ['user_id' => $request->user()->id]);
        return new SignResource($sign->load(['user']));
    }

    /**
     * @group Signs
     * @title "Show Sign"
     * @description "Show a sign"
     * Display the specified sign.
     */
    public function show(Request $request, Sign $sign): SignResource
    {
        Gate::authorize('view', $sign);
        return new SignResource($sign->load(['user']));
    }

    /**
     * @group Signs
     * @title "Update Sign"
     * @description "Update a sign"
     * Update the specified sign in storage.
     */
    public function update(UpdateSignRequest $request, Sign $sign): SignResource
    {
        Gate::authorize('update', $sign);
        $sign->update($request->validated());
        return new SignResource($sign->load(['user']));
    }

    /**
     * @group Signs
     * @title "Delete Sign"
     * @description "Delete a sign"
     * Remove the specified sign from storage.
     */
    public function destroy(Request $request, Sign $sign): JsonResponse
    {
        Gate::authorize('delete', $sign);
        $deletionStatus = $sign->delete();
        return response()->json(['message' => 'Sign deleted successfully', 'status' => $deletionStatus]);
    }

    /**
     * @group Signs
     * @title "Force Delete Sign"
     * @description "Permanently delete a sign (only if not being used)"
     * Force delete a sign (only if not being used).
     */
    public function forceDelete(Request $request, Sign $sign): JsonResponse
    {
        Gate::authorize('forceDelete', $sign);
        try {
            $sign->forceDeleteIfNotUsed();
            return response()->json(['message' => 'Sign permanently deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * @group Signs
     * @title "Restore Sign"
     * @description "Restore a soft deleted sign"
     * Restore a soft deleted sign.
     */
    public function restore(Request $request, Sign $sign): JsonResponse
    {
        Gate::authorize('restore', $sign);
        $sign->restore();
        return response()->json(['message' => 'Sign restored successfully']);
    }
} 