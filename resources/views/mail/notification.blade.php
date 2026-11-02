@component('mail::message')
# {{ $notification->title }}

{{ $notification->message }}

@if($notification->action_url)
@component('mail::button', ['url' => $notification->action_url])
Open DARUSO
@endcomponent
@endif

@if($notification->sender)
{{ $notification->sender->name }}
@endif

Thanks,
DARUSO
@endcomponent
