<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\PollStatus;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Poll lifecycle and voting.
 *
 * Voting runs inside a transaction with a row lock plus a database unique
 * constraint, so two simultaneous requests cannot record two votes. Only a
 * verified, active student may vote.
 */
class PollService
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $options
     */
    public function create(array $data, array $options, User $creator, ?Request $request = null): Poll
    {
        return DB::transaction(function () use ($data, $options, $creator, $request): Poll {
            $poll = Poll::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'created_by' => $creator->getKey(),
                'status' => $data['status'] ?? PollStatus::Draft->value,
                'is_anonymous' => (bool) ($data['is_anonymous'] ?? false),
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
            ]);

            foreach (array_values($options) as $index => $label) {
                $poll->options()->create([
                    'label' => $label,
                    'sort_order' => $index,
                ]);
            }

            $this->audit->log(AuditAction::Created, $poll, null, ['title' => $poll->title], $request);

            return $poll->load('options');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $options
     */
    public function update(Poll $poll, array $data, array $options, User $actor, ?Request $request = null): Poll
    {
        if ($poll->votes()->exists()) {
            throw new \DomainException('A poll that already has votes cannot be edited.');
        }

        return DB::transaction(function () use ($poll, $data, $options, $actor, $request): Poll {
            $old = $poll->only(['title', 'status']);

            $poll->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? $poll->status->value,
                'is_anonymous' => (bool) ($data['is_anonymous'] ?? $poll->is_anonymous),
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
            ]);

            $poll->options()->delete();

            foreach (array_values($options) as $index => $label) {
                $poll->options()->create(['label' => $label, 'sort_order' => $index]);
            }

            $this->audit->log(AuditAction::Updated, $poll, $old, $poll->only(['title', 'status']), $request);

            return $poll->fresh(['options']);
        });
    }

    public function open(Poll $poll, User $actor, ?Request $request = null): Poll
    {
        $poll->update([
            'status' => PollStatus::Open,
            'starts_at' => $poll->starts_at ?? now(),
        ]);

        $this->audit->log(AuditAction::Published, $poll, null, ['status' => PollStatus::Open->value], $request);

        return $poll->fresh();
    }

    public function close(Poll $poll, User $actor, ?Request $request = null): Poll
    {
        $poll->update([
            'status' => PollStatus::Closed,
            'ends_at' => $poll->ends_at ?? now(),
        ]);

        $this->audit->log(AuditAction::StatusChanged, $poll, ['status' => PollStatus::Open->value], ['status' => PollStatus::Closed->value], $request);

        return $poll->fresh();
    }

    /**
     * @throws \DomainException when the poll is closed or the user already voted
     */
    public function vote(Poll $poll, PollOption $option, User $voter, ?Request $request = null): PollVote
    {
        if (! $voter->canParticipate()) {
            throw new \DomainException('Only verified, active students can vote.');
        }

        if ($option->poll_id !== $poll->getKey()) {
            throw new \DomainException('That option does not belong to this poll.');
        }

        try {
            return DB::transaction(function () use ($poll, $option, $voter, $request): PollVote {
                /** @var Poll $locked */
                $locked = Poll::query()->whereKey($poll->getKey())->lockForUpdate()->firstOrFail();

                if (! $locked->isOpen()) {
                    throw new \DomainException('This poll is not open for voting.');
                }

                if (PollVote::query()->where('poll_id', $locked->getKey())->where('user_id', $voter->getKey())->exists()) {
                    throw new \DomainException('You have already voted in this poll.');
                }

                $vote = PollVote::create([
                    'poll_id' => $locked->getKey(),
                    'poll_option_id' => $option->getKey(),
                    'user_id' => $voter->getKey(),
                ]);

                $this->audit->log(AuditAction::Voted, $locked, null, [
                    'poll_option_id' => $option->getKey(),
                ], $request);

                return $vote;
            });
        } catch (UniqueConstraintViolationException) {
            throw new \DomainException('You have already voted in this poll.');
        }
    }

    /**
     * @return array<int, array{option: PollOption, votes: int, percentage: float}>
     */
    public function results(Poll $poll): array
    {
        $counts = $poll->votes()
            ->select('poll_option_id')
            ->selectRaw('count(*) as aggregate')
            ->groupBy('poll_option_id')
            ->pluck('aggregate', 'poll_option_id');

        $total = (int) $counts->sum();

        return $poll->options->map(function (PollOption $option) use ($counts, $total): array {
            $votes = (int) ($counts[$option->getKey()] ?? 0);

            return [
                'option' => $option,
                'votes' => $votes,
                'percentage' => $total === 0 ? 0.0 : round($votes / $total * 100, 1),
            ];
        })->all();
    }
}
