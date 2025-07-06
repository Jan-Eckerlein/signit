<x-mail::message>
# Document Completed

Hello {{ $recipient->name }},

The document **"{{ $document->title }}"** has been completed and all signatures have been collected.

<x-mail::button :url="config('app.url') . '/documents/' . $document->id">
View Completed Document
</x-mail::button>

**Document Details:**
- **Title:** {{ $document->title }}
- **Description:** {{ $document->description ?? 'No description provided' }}
- **Status:** Completed
- **Completed At:** {{ now()->format('F j, Y \a\t g:i A') }}

The document is now finalized and ready for your records.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message> 