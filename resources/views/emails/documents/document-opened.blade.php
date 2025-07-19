<x-mail::message>
# Document In Progress

Hello {{ $recipient->name }},

The document **"{{ $document->title }}"** is now in progress and ready for signing.

<x-mail::button :url="config('app.url') . '/documents/' . $document->id">
View Document
</x-mail::button>

**Document Details:**
- **Title:** {{ $document->title }}
- **Description:** {{ $document->description ?? 'No description provided' }}
- **Status:** In Progress

Please review and sign the document as soon as possible.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message> 