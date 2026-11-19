<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Live session invitation') }}</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #334155; max-width: 560px; margin: 0 auto; padding: 20px;">
    <p>{{ __('Hi :name,', ['name' => $user->name]) }}</p>
    <p>{!! __('You\'re invited to attend a live session for :course.', ['course' => '<strong>'.e($liveSession->course->tr('title')).'</strong>']) !!}</p>
    <p><strong>{{ $liveSession->tr('title') }}</strong></p>
    <ul style="margin: 16px 0;">
        <li><strong>{{ __('Date & time:') }}</strong> {{ $liveSession->scheduled_at->translatedFormat('l, F j, Y') }} {{ __('at') }} {{ $liveSession->scheduled_at->format('g:i A') }}</li>
        <li><strong>{{ __('Duration:') }}</strong> {{ __(':count minutes', ['count' => $liveSession->duration_minutes]) }}</li>
    </ul>
    @if($liveSession->description)
        <p>{{ $liveSession->tr('description') }}</p>
    @endif
    <p style="margin-top: 24px;">
        <a href="{{ $liveSession->meeting_url }}" style="display: inline-block; padding: 12px 24px; background: #F16029; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('Join the session') }}</a>
    </p>
    @if($liveSession->meeting_password)
        <p style="margin-top: 12px; font-size: 14px; color: #64748b;">{{ __('Meeting password:') }} <strong>{{ $liveSession->meeting_password }}</strong></p>
    @endif
    <p style="margin-top: 24px; font-size: 14px; color: #64748b;">{{ __('You can also open the course page and click "Join session" when the time comes.') }}</p>
    <p style="margin-top: 24px; font-size: 14px; color: #94a3b8;">— {{ config('app.name') }}</p>
</body>
</html>
