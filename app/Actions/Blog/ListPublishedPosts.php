<?php

namespace App\Actions\Blog;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator as ConcreteLengthAwarePaginator;
use Illuminate\Pagination\Paginator;

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
        bool $leadOnFirstPage = false,
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

        if (! $leadOnFirstPage) {
            return $query->paginate($perPage)->withQueryString();
        }

        $page = max(1, Paginator::resolveCurrentPage());
        $total = (clone $query)->count();
        $firstPageCount = $perPage + 1;
        $offset = $page === 1 ? 0 : $firstPageCount + (($page - 2) * $perPage);
        $limit = $page === 1 ? $firstPageCount : $perPage;
        $items = $query->offset($offset)->limit($limit)->get();

        // Adjust paginator total by -1 when >13 posts so lastPage() matches 13 on page 1 + 12 on page 2+.
        $paginatorTotal = $total <= $firstPageCount ? min($total, $perPage) : $total - 1;

        return (new ConcreteLengthAwarePaginator(
            $items,
            $paginatorTotal,
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ],
        ))->withQueryString();
    }

    public function publishedCount(): int
    {
        return Post::query()->published()->count();
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
