<x-app-layout>
    @section('title', 'Profile')
    @section('heading', 'Profile')
    @section('subheading', 'Manage your account and academic details.')

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            @include('profile.partials.update-profile-information-form')

            @if ($studentProfile)
                <div class="mt-4">
                    @include('profile.partials.update-student-information-form')
                </div>
            @endif

            <div class="mt-4">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="col-12 col-lg-5">
            @include('profile.partials.delete-user-form')

            <x-page-card class="mt-4" icon="person-badge" title="Account overview">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Name</dt>
                    <dd class="col-7">{{ $user->name }}</dd>

                    <dt class="col-5 text-muted fw-normal">Member since</dt>
                    <dd class="col-7">{{ $user->created_at->format('d M Y') }}</dd>

                    @if ($leaderProfile)
                        <dt class="col-5 text-muted fw-normal">Role</dt>
                        <dd class="col-7">DARUSO leadership</dd>
                    @endif

                    <dt class="col-5 text-muted fw-normal">Roles</dt>
                    <dd class="col-7">
                        @forelse ($user->roles as $role)
                            <span class="badge text-bg-light border">{{ $role->name }}</span>
                        @empty
                            <span class="text-muted">None</span>
                        @endforelse
                    </dd>
                </dl>
            </x-page-card>
        </div>
    </div>
</x-app-layout>