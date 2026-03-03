<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live session invitation</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #334155; max-width: 560px; margin: 0 auto; padding: 20px;">
    <p>Hi {{ $user->name }},</p>
    <p>You're invited to attend a live session for <strong>{{ $liveSession->course->title }}</strong>.</p>
    <p><strong>{{ $liveSession->title }}</strong></p>
    <ul style="margin: 16px 0;">
        <li><strong>Date & time:</strong> {{ $liveSession->scheduled_at->format('l, F j, Y \a\t g:i A') }}</li>
        <li><strong>Duration:</strong> {{ $liveSession->duration_minutes }} minutes</li>
    </ul>
    @if($liveSession->description)
        <p>{{ $liveSession->description }}</p>
    @endif
    <p style="margin-top: 24px;">
        <a href="{{ $liveSession->meeting_url }}" style="display: inline-block; padding: 12px 24px; background: #d97706; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">Join the session</a>
    </p>
    @if($liveSession->meeting_password)
        <p style="margin-top: 12px; font-size: 14px; color: #64748b;">Meeting password: <strong>{{ $liveSession->meeting_password }}</strong></p>
    @endif
    <p style="margin-top: 24px; font-size: 14px; color: #64748b;">You can also open the course page and click "Join session" when the time comes.</p>
    <p style="margin-top: 24px; font-size: 14px; color: #94a3b8;">— {{ config('app.name') }}</p>
</body>
</html>
