<x-mail::message>
# Document Ready for Signing

Hello {{ $recipient->name }},

The document **"{{ $document->title }}"** is ready for you to sign.

<x-mail::button :url="config('app.url') . '/magic-link/' . $magicLinkToken">
Sign Document
</x-mail::button>

**Document Details:**
- **Title:** {{ $document->title }}
- **Description:** {{ $document->description ?? 'No description provided' }}
- **Status:** In Progress

**Important:** This magic link will expire in 24 hours for security reasons.

If you have any questions, please contact the document owner.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message> 