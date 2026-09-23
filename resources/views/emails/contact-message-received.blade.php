@component('mail::message')
# New Contact Message

A new message has been submitted through the contact form.

**From:** {{ $contactMessage->name }} ({{ $contactMessage->email }})  
**Subject:** {{ $contactMessage->subject ?? '(No subject)' }}

---

{{ nl2br(e($contactMessage->message)) }}

---

@component('mail::button', ['url' => url('/admin/dashboard')])
View in Admin Panel
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent
