<x-app-layout>
    @section('title', 'Reports')
    @section('heading', 'Reports and statistics')
    @section('subheading', 'Aggregate figures computed directly from the database.')

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-card icon="people" label="Students" :value="$overview['students']['total']" tone="primary" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="inbox" label="Open complaints" :value="$overview['complaints']['open']" tone="warning" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="megaphone" label="Announcements" :value="$overview['announcements']['total']" tone="info" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="bell" label="Notifications" :value="$overview['notifications']['total']" tone="secondary" />
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <x-page-card icon="exclamation-triangle" title="Complaints by status">
                @if (empty($overview['complaints']['by_status']))
                    <p class="text-muted small mb-0">No complaints recorded.</p>
                @else
                    @php($max = max($overview['complaints']['by_status']))
                    @foreach ($overview['complaints']['by_status'] as $status => $count)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small">
                                <span>{{ \App\Enums\ComplaintStatus::from($status)->label() }}</span>
                                <span class="fw-semibold">{{ $count }}</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ round($count / $max * 100) }}%"
                                     aria-valuenow="{{ $count }}" aria-valuemin="0" aria-valuemax="{{ $max }}"></div>
                            </div>
                        </div>
                    @endforeach

                    <hr>
                    <dl class="row mb-0 small">
                        <dt class="col-7 text-muted fw-normal">Unassigned</dt>
                        <dd class="col-5 text-end">{{ $overview['complaints']['unassigned'] }}</dd>
                        <dt class="col-7 text-muted fw-normal">Resolved</dt>
                        <dd class="col-5 text-end">{{ $overview['complaints']['resolved'] }}</dd>
                    </dl>
                @endif
            </x-page-card>

            <x-page-card class="mt-4" icon="tags" title="Complaints by category">
                @if (empty($overview['complaints']['by_category']))
                    <p class="text-muted small mb-0">No complaint categories recorded.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Category</th>
                                    <th scope="col" class="text-end">Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($overview['complaints']['by_category'] as $category => $count)
                                    <tr>
                                        <td>{{ \App\Enums\ComplaintCategory::from($category)->label() }}</td>
                                        <td class="text-end">{{ $count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-page-card>
        </div>

        <div class="col-12 col-lg-6">
            <x-page-card icon="people" title="Students">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="text-muted small">Total</div>
                        <div class="fs-4 fw-bold">{{ $overview['students']['total'] }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Active</div>
                        <div class="fs-4 fw-bold">
                            {{ $overview['students']['by_status']['active'] ?? 0 }}
                        </div>
                    </div>
                </div>

                <h3 class="h6 fw-semibold">By year of study</h3>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @forelse ($overview['students']['by_year'] as $year => $count)
                        <span class="badge text-bg-light border">
                            Year {{ $year }}: <strong>{{ $count }}</strong>
                        </span>
                    @empty
                        <span class="text-muted small">No data.</span>
                    @endforelse
                </div>

                <h3 class="h6 fw-semibold">By college</h3>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">College</th>
                                <th scope="col" class="text-end">Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($overview['students']['by_college'] as $college => $count)
                                <tr>
                                    <td>{{ $college }}</td>
                                    <td class="text-end">{{ $count }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-muted small">No data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-page-card>
        </div>

        <div class="col-12 col-lg-6">
            <x-page-card icon="megaphone" title="Announcement activity">
                <dl class="row mb-3 small">
                    <dt class="col-8 text-muted fw-normal">Total</dt>
                    <dd class="col-4 text-end">{{ $overview['announcements']['total'] }}</dd>
                    <dt class="col-8 text-muted fw-normal">Published in last 30 days</dt>
                    <dd class="col-4 text-end">{{ $overview['announcements']['published_last_30_days'] }}</dd>
                </dl>

                <h3 class="h6 fw-semibold">Published by priority</h3>
                <div class="d-flex flex-wrap gap-2">
                    @forelse ($overview['announcements']['by_priority'] as $priority => $count)
                        <span class="badge text-bg-light border">
                            {{ \App\Enums\Priority::from($priority)->label() }}: <strong>{{ $count }}</strong>
                        </span>
                    @empty
                        <span class="text-muted small">No published announcements.</span>
                    @endforelse
                </div>
            </x-page-card>

            <x-page-card class="mt-4" icon="bell" title="Notification activity">
                <dl class="row mb-0 small">
                    <dt class="col-8 text-muted fw-normal">Total delivered</dt>
                    <dd class="col-4 text-end">{{ $overview['notifications']['total'] }}</dd>
                    <dt class="col-8 text-muted fw-normal">Still unread</dt>
                    <dd class="col-4 text-end">{{ $overview['notifications']['unread'] }}</dd>
                    <dt class="col-8 text-muted fw-normal">Sent in last 30 days</dt>
                    <dd class="col-4 text-end">{{ $overview['notifications']['sent_last_30_days'] }}</dd>
                    <dt class="col-8 text-muted fw-normal">Distinct senders</dt>
                    <dd class="col-4 text-end">{{ $overview['notifications']['senders'] }}</dd>
                </dl>
            </x-page-card>
        </div>

        <div class="col-12 col-lg-6">
            <x-page-card icon="calendar-event" title="Meetings, events and documents">
                <dl class="row mb-0 small">
                    <dt class="col-7 text-muted fw-normal">Meetings (total / upcoming / completed)</dt>
                    <dd class="col-5 text-end">
                        {{ $overview['meetings']['total'] }} /
                        {{ $overview['meetings']['upcoming'] }} /
                        {{ $overview['meetings']['completed'] }}
                    </dd>

                    <dt class="col-7 text-muted fw-normal">Events (total / upcoming / completed)</dt>
                    <dd class="col-5 text-end">
                        {{ $overview['events']['total'] }} /
                        {{ $overview['events']['upcoming'] }} /
                        {{ $overview['events']['completed'] }}
                    </dd>

                    <dt class="col-7 text-muted fw-normal">Documents</dt>
                    <dd class="col-5 text-end">{{ $overview['documents']['total'] }}</dd>

                    <dt class="col-7 text-muted fw-normal">Total document size</dt>
                    <dd class="col-5 text-end">
                        {{ number_format($overview['documents']['total_size'] / 1024, 1) }} KB
                    </dd>
                </dl>
            </x-page-card>

            <x-page-card class="mt-4" icon="diagram-3" title="Organisation">
                <dl class="row mb-0 small">
                    <dt class="col-7 text-muted fw-normal">Leadership terms (active)</dt>
                    <dd class="col-5 text-end">
                        {{ $overview['leadership']['terms'] }} ({{ $overview['leadership']['active_terms'] }})
                    </dd>
                    <dt class="col-7 text-muted fw-normal">Positions</dt>
                    <dd class="col-5 text-end">{{ $overview['leadership']['positions'] }}</dd>
                    <dt class="col-7 text-muted fw-normal">Ministries</dt>
                    <dd class="col-5 text-end">{{ $overview['leadership']['ministries'] }}</dd>
                    <dt class="col-7 text-muted fw-normal">Committees</dt>
                    <dd class="col-5 text-end">{{ $overview['leadership']['committees'] }}</dd>
                    <dt class="col-7 text-muted fw-normal">Assignments</dt>
                    <dd class="col-5 text-end">{{ $overview['leadership']['assignments'] }}</dd>
                    <dt class="col-7 text-muted fw-normal">Leaders</dt>
                    <dd class="col-5 text-end">{{ $overview['leadership']['leaders'] }}</dd>
                </dl>
            </x-page-card>
        </div>

        <div class="col-12">
            <x-page-card icon="journal-text" title="Audit activity (last 14 days)">
                @if (empty($auditActivity))
                    <p class="text-muted small mb-0">No recorded audit activity in this window.</p>
                @else
                    @php($maxActivity = max($auditActivity))
                    <div class="row g-2">
                        @foreach ($auditActivity as $day => $count)
                            <div class="col-6 col-md-2">
                                <div class="text-center border rounded p-2 h-100">
                                    <div class="small text-muted">{{ \Illuminate\Support\Carbon::parse($day)->format('d M') }}</div>
                                    <div class="fs-5 fw-bold">{{ $count }}</div>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar" role="progressbar"
                                             style="width: {{ round($count / $maxActivity * 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-page-card>
        </div>
    </div>
</x-app-layout>