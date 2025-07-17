<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Gate;
use App\Attributes\SharedPaginationParams;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

/**
 * @group Contacts
 */
class ContactController extends Controller
{
    /**
     * List Contacts
     * 
     * List all contacts owned by the authenticated user.
     */
    #[SharedPaginationParams]
    #[ResponseFromApiResource(ContactResource::class, Contact::class, collection: true, paginate: 20)]
    public function index(Request $request): ResourceCollection
    {
        Gate::authorize('viewAny', Contact::class);
        return Contact::ownedBy()->with(['user'])->paginateOrGetAll($request);
    }

    /**
     * Create Contact
     * 
     * Store a newly created contact in storage.
     */
    #[ResponseFromApiResource(ContactResource::class, Contact::class)]
    public function store(StoreContactRequest $request): ContactResource
    {
        Gate::authorize('create', Contact::class);
        $contact = Contact::create($request->validated() + ['user_id' => $request->user()->id]);
        return new ContactResource($contact->load(['user']));
    }

    /**
     * Show Contact
     * 
     * Display the specified contact.
     */
    #[ResponseFromApiResource(ContactResource::class, Contact::class)]
    public function show(Contact $contact): ContactResource
    {
        Gate::authorize('view', $contact);
        return new ContactResource($contact->load(['user']));
    }

    /**
     * Update Contact
     * 
     * Update the specified contact in storage.
     */
    #[ResponseFromApiResource(ContactResource::class, Contact::class)]
    public function update(UpdateContactRequest $request, Contact $contact): ContactResource
    {
        Gate::authorize('update', $contact);
        $contact->update($request->validated());
        return new ContactResource($contact->load(['user']));
    }

    /**
     * Delete Contact
     * 
     * Remove the specified contact from storage.
     */
    #[ResponseFromApiResource(ContactResource::class, Contact::class)]
    public function destroy(Contact $contact): JsonResponse
    {
        Gate::authorize('delete', $contact);
        $contact->delete();
        return response()->json(['message' => 'Contact deleted successfully']);
    }
} 