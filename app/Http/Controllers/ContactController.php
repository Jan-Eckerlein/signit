<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $contacts = Contact::with(['fromUser', 'knowsUser', 'knowsAnonymousUser'])->paginate();
        return ContactResource::collection($contacts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactRequest $request): ContactResource
    {
        $contact = Contact::create($request->validated());
        return new ContactResource($contact->load(['fromUser', 'knowsUser', 'knowsAnonymousUser']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact): ContactResource
    {
        return new ContactResource($contact->load(['fromUser', 'knowsUser', 'knowsAnonymousUser']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContactRequest $request, Contact $contact): ContactResource
    {
        $contact->update($request->validated());
        return new ContactResource($contact->load(['fromUser', 'knowsUser', 'knowsAnonymousUser']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();
        return response()->json(['message' => 'Contact deleted successfully']);
    }
} 