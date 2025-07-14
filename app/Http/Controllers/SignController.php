<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSignRequest;
use App\Http\Requests\UpdateSignRequest;
use App\Http\Resources\SignResource;
use App\Models\Sign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Gate;
use App\Attributes\SharedPaginationParams;

/**
 * @group Signs
 */
class SignController extends Controller
{
    /**
     * List Signs
     * 
     * List all signs owned by the user
     * @return \Illuminate\Http\Resources\Json\ResourceCollection<\App\Http\Resources\SignResource>
     */
    #[SharedPaginationParams]
    public function index(Request $request): ResourceCollection
    {
        Gate::authorize('viewAny', Sign::class);
        return Sign::ownedBy($request->user())->with(['user'])->paginateOrGetAll($request);
    }

    /**
     * Create Sign
     * 
     * Store a newly created sign for a document in storage.
     */
    public function store(StoreSignRequest $request): SignResource
    {
        Gate::authorize('create', Sign::class);
        $sign = Sign::create($request->validated() + ['user_id' => $request->user()->id]);
        return new SignResource($sign->load(['user']));
    }

    /**
     * Show Sign
     * 
     * Display the specified sign.
     */
    public function show(Request $request, Sign $sign): SignResource
    {
        Gate::authorize('view', $sign);
        return new SignResource($sign->load(['user']));
    }

    /**
     * Update Sign
     * 
     * Update the specified sign in storage.
     */
    public function update(UpdateSignRequest $request, Sign $sign): SignResource
    {
        Gate::authorize('update', $sign);
        $sign->update($request->validated());
        return new SignResource($sign->load(['user']));
    }

    /**
     * Delete Sign
     * 
     * Remove the specified sign from storage.
     */
    public function destroy(Request $request, Sign $sign): JsonResponse
    {
        Gate::authorize('delete', $sign);
        $deletionStatus = $sign->delete();
        return response()->json(['message' => 'Sign deleted successfully', 'status' => $deletionStatus]);
    }

    /**
     * Force Delete Sign
     * 
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
     * Restore Sign
     * 
     * Restore a soft deleted sign.
     */
    public function restore(Request $request, Sign $sign): JsonResponse
    {
        Gate::authorize('restore', $sign);
        $sign->restore();
        return response()->json(['message' => 'Sign restored successfully']);
    }
} 