<x-mail::message>
# First Signature Received

Hello {{ $document->ownerUser->name }},

The document **"{{ $document->title }}"** has received its first signature and is no longer revertible to draft status.

**Document Details:**
- **Title:** {{ $document->title }}
- **Status:** In Progress

The document will be completed once all required fields are filled by all signers.

<x-mail::button :url="config('app.url') . '/documents/' . $document->id">
View Document
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message> 