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
use App\Jobs\ProcessSignatureImage;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use App\Services\SignService;

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
    #[ResponseFromApiResource(SignResource::class, Sign::class)]
    public function store(StoreSignRequest $request): SignResource
    {
        $name = $request->input('name') 
            ?? 'Sign-' . Sign::ownedBy($request->user())->count() + 1;

        $merge = [
            'user_id' => $request->user()->id,
            'name' => $name,
        ];

        $request->merge($merge);

        Gate::authorize('create', Sign::class);
        $data = $request->validated() + $merge;
        $sign = Sign::create($data);

        ProcessSignatureImage::dispatch($request->file('image'), $sign->id);

        return new SignResource($sign);
    }

    /**
     * Show Sign
     * 
     * Display the specified sign.
     */
    #[ResponseFromApiResource(SignResource::class, Sign::class)]
    public function show(Request $request, Sign $sign): SignResource
    {
        Gate::authorize('view', $sign);
        return new SignResource($sign);
    }

    /**
     * Update Sign
     * 
     * Update the specified sign in storage.
     */
    #[ResponseFromApiResource(SignResource::class, Sign::class)]
    public function update(UpdateSignRequest $request, Sign $sign): SignResource
    {
        Gate::authorize('update', $sign);
        $sign->update($request->validated());
        return new SignResource($sign);
    }

    /**
     * Delete Sign
     * 
     * Remove the specified sign from storage.
     */
    public function destroy(Request $request, Sign $sign): JsonResponse
    {
        Gate::authorize('delete', $sign);
        if ($sign->isBeingUsed()) {
            $sign->archive();
            return response()->json([
                'status' => 'archived',
                'message' => 'Sign is being used by document fields, archived instead'
            ]);
        }
        $sign->delete();
        return response()->json([
            'status' => 'deleted',
            'message' => 'Sign deleted successfully',
        ]);
    }

    /**
     * Restore Sign
     * 
     * Restore a soft deleted sign.
     */
    public function unarchive(Request $request, Sign $sign): JsonResponse
    {
        Gate::authorize('update', $sign);
        $sign->unarchive();
        return response()->json(['message' => 'Sign unarchived successfully']);
    }
} 