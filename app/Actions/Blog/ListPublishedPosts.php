<?php

namespace App\Actions\Blog;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ListPublishedPosts
{
    /**
     * @return LengthAwarePaginator<int, Post>
     */
    public function execute(
        ?string $search = null,
        ?Category $category = null,
        ?Tag $tag = null,
        int $perPage = 12,
    ): LengthAwarePaginator {
        $query = Post::query()
            ->published()
            ->withCardRelations()
            ->latest('published_at')
            ->latest('id');

        if ($category instanceof Category) {
            $query->whereHas('categories', function (Builder $builder) use ($category): void {
                $builder->where('categories.id', $category->id);
            });
        }

        if ($tag instanceof Tag) {
            $query->whereHas('tags', function (Builder $builder) use ($tag): void {
                $builder->where('tags.id', $tag->id);
            });
        }

        if ($search !== null && trim($search) !== '') {
            $query->search($search);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @return Collection<int, Category>
     */
    public function categoriesWithPublishedCount(): Collection
    {
        return Category::query()
            ->select(['id', 'name', 'slug'])
            ->whereHas('posts', function (Builder $builder): void {
                $builder->published();
            })
            ->withCount(['posts' => function (Builder $builder): void {
                $builder->published();
            }])
            ->orderByDesc('posts_count')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Tag>
     */
    public function popularTags(int $limit = 16): Collection
    {
        return Tag::query()
            ->select(['id', 'name', 'slug'])
            ->whereHas('posts', function (Builder $builder): void {
                $builder->published();
            })
            ->withCount(['posts' => function (Builder $builder): void {
                $builder->published();
            }])
            ->orderByDesc('posts_count')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }
}
