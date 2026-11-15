@extends('layouts.admin')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <a href="{{ route('studio.index') }}" class="text-xs font-semibold text-[#be185d] hover:text-[#2b124c]">
                &larr; Back to Admin Dashboard
            </a>
            <h1 id="main-heading" tabindex="-1" class="mt-1 text-2xl font-bold text-slate-900 sm:text-3xl">
                {{ $post ? 'Edit Article: '.$post->title : 'Compose New GTA VI Dispatch' }}
            </h1>
        </div>
        @if ($post && $post->status === 'published')
            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-[40px] items-center rounded border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:border-[#2b124c] hover:text-[#2b124c]">
                View Published Article &nearr;
            </a>
        @endif
    </div>

    <livewire:studio-post-editor :post="$post" />
@endsection
