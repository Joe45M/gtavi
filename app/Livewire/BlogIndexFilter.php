<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\MicrositeDataService;
use App\Services\PostService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class BlogIndexFilter extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'category')]
    public string $category = '';

    #[Url(as: 'tag')]
    public string $tag = '';

    #[Url(as: 'badge')]
    public string $verificationBadge = '';

    #[Url(as: 'sort')]
    public string $sort = 'latest';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedTag(): void
    {
        $this->resetPage();
    }

    public function updatedVerificationBadge(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'category', 'tag', 'verificationBadge', 'sort']);
        $this->sort = 'latest';
        $this->resetPage();
    }

    public function render(PostService $postService, MicrositeDataService $dataService): View
    {
        $posts = $postService->listPublished([
            'search' => $this->search,
            'category' => $this->category,
            'tag' => $this->tag,
            'verification_badge' => $this->verificationBadge,
            'sort' => $this->sort,
        ], 9);

        return view('livewire.blog-index-filter', [
            'posts' => $posts,
            'categories' => $dataService->categories(),
            'tags' => $dataService->tags(),
        ]);
    }
}
