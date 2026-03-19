<?php

namespace App\Services\Post;

use App\Http\Resources\PostResource;
use App\Models\OrganizationMember;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PostService
{
    private const ACCOUNT_DIRECTORY = [
        'demo-instagram-account' => ['platform' => 'instagram', 'label' => '@social_maven'],
        'demo-facebook-account' => ['platform' => 'facebook', 'label' => 'Acme Marketing'],
        'account-ig-nature' => ['platform' => 'instagram', 'label' => '@nature_clicks'],
    ];

    public function list(User $user, string $context): array
    {
        $scope = $this->resolveScope($user, $context);

        $posts = $this->baseScopedQuery($scope)
            ->latest('updated_at')
            ->get();

        return PostResource::collection($posts)->resolve();
    }

    public function show(User $user, string $context, string|int $postId): array
    {
        return PostResource::make($this->findScopedPost($user, $context, $postId))->resolve();
    }

    public function create(User $user, string $context, array $payload): array
    {
        $scope = $this->resolveScope($user, $context);
        $metadata = $this->resolveAccountMetadata($payload['accountIds']);
        $timestamps = $this->derivePublishTimestamps($payload);

        $post = Post::query()->create([
            'user_id' => $user->id,
            'organization_id' => $scope['organization_id'],
            'title' => $payload['title'],
            'content' => $payload['content'],
            'status' => $timestamps['status'],
            'publish_mode' => $payload['publishMode'],
            'scheduled_at' => $timestamps['scheduled_at'],
            'published_at' => $timestamps['published_at'],
            'timezone' => $payload['timezone'],
            'platforms' => $metadata['platforms'],
            'account_ids' => $payload['accountIds'],
            'account_labels' => $metadata['labels'],
            'media_name' => $payload['mediaName'] ?? null,
            'media_type' => $payload['mediaType'] ?? null,
            'visibility' => $payload['visibility'],
            'age_min' => $payload['ageMin'],
            'age_max' => $payload['ageMax'],
            'locations' => $payload['locations'],
            'interests' => $payload['interests'],
            'lookalike_audience' => $payload['lookalikeAudience'],
            'failure_reason' => null,
        ]);

        return PostResource::make($post)->resolve();
    }

    public function update(User $user, string $context, string|int $postId, array $payload): array
    {
        $post = $this->findScopedPost($user, $context, $postId);
        $metadata = $this->resolveAccountMetadata($payload['accountIds']);
        $timestamps = $this->derivePublishTimestamps($payload);

        $post->update([
            'title' => $payload['title'],
            'content' => $payload['content'],
            'status' => $timestamps['status'],
            'publish_mode' => $payload['publishMode'],
            'scheduled_at' => $timestamps['scheduled_at'],
            'published_at' => $timestamps['published_at'],
            'timezone' => $payload['timezone'],
            'platforms' => $metadata['platforms'],
            'account_ids' => $payload['accountIds'],
            'account_labels' => $metadata['labels'],
            'media_name' => $payload['mediaName'] ?? null,
            'media_type' => $payload['mediaType'] ?? null,
            'visibility' => $payload['visibility'],
            'age_min' => $payload['ageMin'],
            'age_max' => $payload['ageMax'],
            'locations' => $payload['locations'],
            'interests' => $payload['interests'],
            'lookalike_audience' => $payload['lookalikeAudience'],
            'failure_reason' => null,
        ]);

        return PostResource::make($post->fresh())->resolve();
    }

    public function saveDraft(User $user, string $context, string|int $postId): array
    {
        $post = $this->findScopedPost($user, $context, $postId);

        $post->update([
            'status' => 'draft',
            'publish_mode' => 'draft',
            'scheduled_at' => null,
            'published_at' => null,
            'failure_reason' => null,
        ]);

        return PostResource::make($post->fresh())->resolve();
    }

    public function delete(User $user, string $context, string|int $postId): void
    {
        $this->findScopedPost($user, $context, $postId)->delete();
    }

    private function resolveScope(User $user, string $context): array
    {
        if ($user->isSuperAdmin()) {
            throw new AuthorizationException('Super admin cannot manage normal posts from this area.');
        }

        if ($context === 'personal') {
            return [
                'organization_id' => null,
                'scope' => 'personal',
                'can_view_all' => false,
            ];
        }

        $membership = $user->organizationMembers()
            ->where('status', 'active')
            ->latest()
            ->first();

        if (! $membership) {
            throw new AuthorizationException('Organization context was not found.');
        }

        return [
            'organization_id' => $membership->organization_id,
            'scope' => 'organization',
            'can_view_all' => in_array($membership->role, ['owner', 'manager'], true),
        ];
    }

    private function baseScopedQuery(array $scope): Builder
    {
        $query = Post::query();

        if ($scope['scope'] === 'personal') {
            return $query->whereNull('organization_id')->where('user_id', auth()->id());
        }

        $query->where('organization_id', $scope['organization_id']);

        if (! $scope['can_view_all']) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    private function findScopedPost(User $user, string $context, string|int $postId): Post
    {
        $scope = $this->resolveScope($user, $context);

        $post = $this->baseScopedQueryForUser($user, $scope)
            ->whereKey($postId)
            ->first();

        if (! $post) {
            throw (new ModelNotFoundException())->setModel(Post::class, [$postId]);
        }

        return $post;
    }

    private function baseScopedQueryForUser(User $user, array $scope): Builder
    {
        $query = Post::query();

        if ($scope['scope'] === 'personal') {
            return $query->whereNull('organization_id')->where('user_id', $user->id);
        }

        $query->where('organization_id', $scope['organization_id']);

        if (! $scope['can_view_all']) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private function derivePublishTimestamps(array $payload): array
    {
        if ($payload['publishMode'] === 'schedule') {
            return [
                'status' => 'scheduled',
                'scheduled_at' => $payload['scheduledAt'] ?? null,
                'published_at' => null,
            ];
        }

        if ($payload['publishMode'] === 'now') {
            return [
                'status' => 'published',
                'scheduled_at' => null,
                'published_at' => now(),
            ];
        }

        return [
            'status' => 'draft',
            'scheduled_at' => null,
            'published_at' => null,
        ];
    }

    private function resolveAccountMetadata(array $accountIds): array
    {
        $platforms = [];
        $labels = [];

        foreach ($accountIds as $accountId) {
            $known = self::ACCOUNT_DIRECTORY[$accountId] ?? null;

            if ($known) {
                $platforms[] = $known['platform'];
                $labels[] = $known['label'];
                continue;
            }

            $labels[] = $accountId;

            if (str_contains($accountId, 'instagram') || str_contains($accountId, 'ig-')) {
                $platforms[] = 'instagram';
            } elseif (str_contains($accountId, 'facebook') || str_contains($accountId, 'fb-')) {
                $platforms[] = 'facebook';
            }
        }

        return [
            'platforms' => array_values(array_unique($platforms)),
            'labels' => $labels,
        ];
    }

}
