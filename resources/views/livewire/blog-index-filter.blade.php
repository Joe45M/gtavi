<div>
    <search class="rounded-md border border-slate-200 bg-white p-5 shadow-xs">
        <div class="grid gap-4 md:grid-cols-12">
            <div class="md:col-span-4">
                <label for="blog-search" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">
                    Search Reports
                </label>
                <input
                    id="blog-search"
                    type="search"
                    wire:model.live.debounce.250ms="search"
                    placeholder="Download size, RAGE 9, Take-Two, VRAM..."
                    class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#2b124c] focus:outline-none"
                >
            </div>

            <div class="md:col-span-3">
                <label for="blog-category" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">
                    Section
                </label>
                <select id="blog-category" wire:model.live="category" class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-[#2b124c] focus:outline-none">
                    <option value="">All Sections</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->slug }}">{{ $cat->name }} ({{ $cat->posts_count }})</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3">
                <label for="blog-badge" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">
                    Source Classification
                </label>
                <select id="blog-badge" wire:model.live="verificationBadge" class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-[#2b124c] focus:outline-none">
                    <option value="">All Classifications</option>
                    <option value="official_filing">Official Filing</option>
                    <option value="confirmed">Confirmed Fact</option>
                    <option value="technical_projection">Technical Projection</option>
                    <option value="community_speculation">Community Analysis</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="blog-sort" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">
                    Sort By
                </label>
                <select id="blog-sort" wire:model.live="sort" class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-[#2b124c] focus:outline-none">
                    <option value="latest">Featured &amp; Latest</option>
                    <option value="popular">Most Read</option>
                </select>
            </div>
        </div>

        @if ($search !== '' || $category !== '' || $tag !== '' || $verificationBadge !== '')
            <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 pt-3 text-xs">
                <p class="font-medium text-slate-600">
                    Active filters applied &bull; Showing {{ $posts->total() }} matching report(s)
                </p>
                <button type="button" wire:click="resetFilters" class="cursor-pointer font-semibold text-[#be185d] hover:text-[#2b124c]">
                    Reset All Filters
                </button>
            </div>
        @endif
    </search>

    <!-- Topic Tags -->
    <div class="mt-4 flex flex-wrap gap-2">
        @foreach ($tags as $t)
            <button
                type="button"
                wire:click="$set('tag', '{{ $tag === $t->slug ? '' : $t->slug }}')"
                class="min-h-[34px] cursor-pointer rounded border px-3 py-1 text-xs font-medium transition {{ $tag === $t->slug ? 'border-[#2b124c] bg-[#2b124c] text-white' : 'border-slate-200 bg-white text-slate-700 hover:border-[#2b124c] hover:text-[#2b124c]' }}"
            >
                #{{ $t->name }} ({{ $t->posts_count }})
            </button>
        @endforeach
    </div>

    <!-- Articles Grid -->
    <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($posts as $post)
            <article class="article-list-item flex flex-col rounded-md border border-slate-200 bg-white p-5 shadow-xs transition hover:border-slate-300">
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs font-semibold">
                    <a href="{{ route('blog.category', $post->category->slug) }}" class="text-[#be185d] hover:text-[#2b124c]">{{ $post->category->name }}</a>
                    <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">{{ $post->verificationLabel() }}</span>
                </div>

                <h2 class="mt-3 text-lg font-bold leading-snug text-slate-900">
                    <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-[#2b124c]">{{ $post->title }}</a>
                </h2>
                <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-600">{{ $post->excerpt }}</p>
                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-500">
                    @php $postDate = $post->published_at ?? $post->created_at; @endphp
                    <span>@if ($postDate)<time datetime="{{ $postDate->toIso8601String() }}">{{ $postDate->format('j M Y') }}</time>@endif</span>
                    <span>{{ $post->reading_time_minutes }} min read &bull; {{ $post->word_count }} words</span>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-md border border-slate-200 bg-white p-10 text-center shadow-xs">
                <p class="text-lg font-bold text-slate-900">No reports matched your filter criteria.</p>
                <button type="button" wire:click="resetFilters" class="mt-3 cursor-pointer rounded bg-[#2b124c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#3b1a66]">
                    Show All Reports
                </button>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $posts->links() }}
    </div>
</div>
