<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ContactController extends Controller
{
    /**
     * @group Contacts
     * @title "List Contacts"
     * @description "List all contacts owned by the authenticated user"
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection<\App\Http\Resources\ContactResource>
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Contact::class);
        $contacts = Contact::ownedBy()->with(['user'])->paginate();
        return ContactResource::collection($contacts);
    }

    /**
     * @group Contacts
     * @title "Create Contact"
     * @description "Create a new contact"
     * Store a newly created resource in storage.
     */
    public function store(StoreContactRequest $request): ContactResource
    {
        Gate::authorize('create', Contact::class);
        $contact = Contact::create($request->validated() + ['user_id' => $request->user()->id]);
        return new ContactResource($contact->load(['user']));
    }

    /**
     * @group Contacts
     * @title "Show Contact"
     * @description "Show a contact"
     * Display the specified resource.
     */
    public function show(Contact $contact): ContactResource
    {
        Gate::authorize('view', $contact);
        return new ContactResource($contact->load(['user']));
    }

    /**
     * @group Contacts
     * @title "Update Contact"
     * @description "Update a contact"
     * Update the specified resource in storage.
     */
    public function update(UpdateContactRequest $request, Contact $contact): ContactResource
    {
        Gate::authorize('update', $contact);
        $contact->update($request->validated());
        return new ContactResource($contact->load(['user']));
    }

    /**
     * @group Contacts
     * @title "Delete Contact"
     * @description "Delete a contact"
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact): JsonResponse
    {
        Gate::authorize('delete', $contact);
        $contact->delete();
        return response()->json(['message' => 'Contact deleted successfully']);
    }
} 