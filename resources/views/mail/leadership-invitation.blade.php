<x-mail::message>
# You have been invited to DARUSO leadership

Hello,

{{ $invitation->inviter?->name ?? 'A DARUSO leader' }} has invited you to join the DARUSO leadership team as **{{ \App\Enums\RoleName::tryFrom($invitation->role_name)?->label() ?? $invitation->role_name }}**.

@if ($invitation->ministry)
Ministry: **{{ $invitation->ministry->name }}**
@endif

@if ($invitation->committee)
Committee: **{{ $invitation->committee->name }}**
@endif

@if ($invitation->position)
Position: **{{ $invitation->position->name }}**
@endif

This link can only be used once and expires on {{ $invitation->expires_at->format('d M Y H:i') }}. It must be accepted while signed in with **{{ $invitation->email }}**.

<x-mail::button :url="$url">
Review invitation
</x-mail::button>

If you were not expecting this invitation you can ignore this email. No access is granted until the link is accepted.

Thanks,<br>
DARUSO
</x-mail::message>
