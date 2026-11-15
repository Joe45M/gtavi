@extends('layouts.app')

@section('content')
    <header class="rounded-md border border-slate-200 bg-white p-6 shadow-xs sm:p-8">
        <span class="inline-flex rounded border border-[#fbcfe8] bg-[#fdf2f8] px-2.5 py-0.5 text-xs font-semibold text-[#9d174d]">
            {{ $archiveType }} Archive
        </span>
        <h1 id="main-heading" tabindex="-1" class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
            {{ $archiveTitle }}
        </h1>
        <p class="mt-3 max-w-2xl text-base leading-relaxed text-slate-600">
            {{ $archiveDescription }}
        </p>
        <div class="mt-5 flex flex-wrap gap-2">
            <a href="{{ route('blog.index') }}" class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-800 hover:border-[#2b124c] hover:text-[#2b124c]">
                &larr; All News Articles
            </a>
            @foreach ($categories as $cat)
                <a href="{{ route('blog.category', $cat->slug) }}" class="rounded border border-slate-200 bg-[#f8f7fb] px-3 py-1.5 text-xs font-medium text-[#2b124c] hover:border-[#2b124c] hover:bg-purple-50">
                    {{ $cat->name }} ({{ $cat->posts_count }})
                </a>
            @endforeach
        </div>
    </header>

    <section aria-label="Archive Dispatches" class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($posts as $post)
            <article class="article-list-item flex flex-col rounded-md border border-slate-200 bg-white p-5 shadow-xs transition hover:border-slate-300">
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs font-semibold">
                    <span class="text-[#be185d]">{{ $post->category->name }}</span>
                    <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">{{ $post->verificationLabel() }}</span>
                </div>

                <h2 class="mt-3 text-lg font-bold leading-snug text-slate-900">
                    <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-[#2b124c]">{{ $post->title }}</a>
                </h2>
                <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-600">{{ $post->excerpt }}</p>
                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-500">
                    <span>{{ ($post->published_at ?? $post->created_at)?->format('j M Y') }}</span>
                    <span>{{ $post->reading_time_minutes }} min read</span>
                </div>
            </article>
        @empty
            <p class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-slate-600">
                No published dispatches in this archive yet.
            </p>
        @endforelse
    </section>

    <div class="mt-8">
        {{ $posts->links() }}
    </div>
@endsection
