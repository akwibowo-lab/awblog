<?php

namespace App\Actions\Blog;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class GetPublishedPost
{
    /**
     * @return array{post: Post, related: Collection<int, Post>}
     */
    public function execute(string $slug, int $relatedLimit = 3): array
    {
        $post = Post::query()
            ->published()
            ->withCardRelations()
            ->where('slug', $slug)
            ->firstOrFail();

        $categoryIds = $post->categories->pluck('id')->all();

        $related = new Collection;

        if ($categoryIds !== []) {
            $related = Post::query()
                ->published()
                ->withCardRelations()
                ->whereKeyNot($post->getKey())
                ->whereHas('categories', function (Builder $builder) use ($categoryIds): void {
                    $builder->whereIn('categories.id', $categoryIds);
                })
                ->latest('published_at')
                ->limit($relatedLimit)
                ->get();
        }

        return [
            'post' => $post,
            'related' => $related,
        ];
    }
}
