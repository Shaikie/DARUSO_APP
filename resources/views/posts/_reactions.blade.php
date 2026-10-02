@php
    $reactionLabels = [
        'love' => ['icon' => '❤️', 'label' => 'Love'],
        'celebrate' => ['icon' => '🎉', 'label' => 'Celebrate'],
        'support' => ['icon' => '🙌', 'label' => 'Support'],
        'insightful' => ['icon' => '💡', 'label' => 'Insightful'],
    ];
    $currentReaction = $post->reactions->first()?->type;
@endphp

<details class="daruso-reaction-menu">
    <summary class="{{ $currentReaction ? 'has-reaction' : '' }}">
        <i class="bi bi-emoji-smile"></i>
        {{ $currentReaction ? $reactionLabels[$currentReaction]['label'] : 'React' }}
    </summary>
    <div class="daruso-reaction-picker">
        @foreach($reactionLabels as $type => $reaction)
            <form method="POST" action="{{ route('student.posts.react', $post) }}">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <button type="submit" class="{{ $currentReaction === $type ? 'selected' : '' }}" title="{{ $reaction['label'] }}">
                    <span>{{ $reaction['icon'] }}</span>
                    <small>{{ $reaction['label'] }}</small>
                </button>
            </form>
        @endforeach
    </div>
</details>