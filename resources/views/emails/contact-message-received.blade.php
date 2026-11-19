@component('mail::message')
# {{ __('New Contact Message') }}

{{ __('A new message has been submitted through the contact form.') }}

**{{ __('From:') }}** {{ $contactMessage->name }} ({{ $contactMessage->email }})  
**{{ __('Subject:') }}** {{ $contactMessage->subject ?? __('(No subject)') }}

---

{{ nl2br(e($contactMessage->message)) }}

---

@component('mail::button', ['url' => url('/admin/dashboard')])
{{ __('View in Admin Panel') }}
@endcomponent

{{ __('Thanks,') }}  
{{ config('app.name') }}
@endcomponent
