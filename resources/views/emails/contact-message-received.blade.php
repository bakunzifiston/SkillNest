@component('mail::message')
# New Contact Message

A new message has been submitted through the contact form.

**From:** {{ $message->name }} ({{ $message->email }})  
**Subject:** {{ $message->subject ?? '(No subject)' }}

---

{{ nl2br(e($message->message)) }}

---

@component('mail::button', ['url' => url('/admin/dashboard')])
View in Admin Panel
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent
