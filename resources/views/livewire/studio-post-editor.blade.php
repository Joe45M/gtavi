<div>
    @if ($saveBanner)
        <div role="status" class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-800">
            {{ $saveBanner }}
        </div>
    @endif

    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-md border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <p class="font-bold">Please fix the following before saving:</p>
            <ul class="mt-1 list-disc ps-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @can('create posts')
        <!-- Gemini AI Article Generator Inside Studio Editor -->
        <section aria-labelledby="editor-ai-assistant-heading" class="mb-6 rounded-md border border-slate-200 border-l-3 border-l-[#2b124c] bg-white p-4 shadow-xs sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 id="editor-ai-assistant-heading" class="text-sm font-bold text-slate-900">
                        Gemini AI Post Creator
                    </h2>
                    <p id="editor-ai-topic-help" class="text-xs text-slate-600">
                        Generate a full 800&ndash;1,500 word SEO dispatch directly into this editor canvas (with interlinks, FAQs, and telemetry).
                    </p>
                </div>
                <span wire:loading wire:target="generateWithAi" class="inline-flex items-center rounded bg-purple-100 px-2.5 py-1 text-xs font-semibold text-[#2b124c]">
                    Generating article with Gemini...
                </span>
            </div>

            <div class="mt-4 grid gap-4 lg:grid-cols-12 lg:items-end">
                <fieldset class="lg:col-span-3">
                    <legend class="text-xs font-semibold uppercase tracking-wide text-slate-700">AI Mode</legend>
                    <div class="mt-1.5 grid grid-cols-2 gap-2">
                        @foreach (['evergreen' => 'Evergreen', 'news' => 'Latest News'] as $modeVal => $modeTxt)
                            <label
                                for="editor-ai-mode-{{ $modeVal }}"
                                class="flex min-h-[44px] cursor-pointer items-center gap-2 rounded border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-800 has-[:checked]:border-[#2b124c] has-[:checked]:bg-purple-50 has-[:checked]:text-[#2b124c]"
                            >
                                <input
                                    id="editor-ai-mode-{{ $modeVal }}"
                                    type="radio"
                                    value="{{ $modeVal }}"
                                    wire:model="aiMode"
                                    class="h-4 w-4 accent-[#2b124c]"
                                >
                                <span>{{ $modeTxt }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="lg:col-span-5">
                    <label for="editor-ai-topic" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">
                        Topic or News Angle <span class="font-normal text-slate-500">(Optional)</span>
                    </label>
                    <input
                        id="editor-ai-topic"
                        type="text"
                        maxlength="180"
                        wire:model="aiTopic"
                        aria-describedby="editor-ai-topic-help"
                        placeholder="e.g. PS5 Pro PSSR 60 FPS projection or latest Rockstar update"
                        class="mt-1.5 min-h-[44px] w-full rounded border border-slate-400 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-[#2b124c] focus:outline-2 focus:outline-[#2b124c]"
                    >
                </div>

                <div class="flex flex-wrap gap-2 lg:col-span-4 lg:justify-end">
                    <button
                        type="button"
                        wire:click="generateWithAi('draft')"
                        wire:loading.attr="disabled"
                        wire:target="generateWithAi"
                        class="min-h-[44px] cursor-pointer rounded border border-[#2b124c] bg-white px-4 py-2 text-xs font-semibold text-[#2b124c] transition hover:bg-purple-50 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        Generate AI Draft
                    </button>
                    <button
                        type="button"
                        wire:click="generateWithAi('published')"
                        wire:loading.attr="disabled"
                        wire:target="generateWithAi"
                        class="min-h-[44px] cursor-pointer rounded bg-[#be185d] px-4 py-2 text-xs font-semibold text-white transition hover:bg-[#9d174d] disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        Generate &amp; Publish
                    </button>
                </div>
            </div>
        </section>
    @endcan

    <!-- WordPress-Style Top Action Bar -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-md border border-slate-200 bg-white p-4 shadow-xs">
        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                command="show-modal"
                commandfor="block-inserter-dialog"
                class="inline-flex min-h-[42px] cursor-pointer items-center gap-1.5 rounded bg-[#2b124c] px-4 py-2 text-xs font-semibold text-white hover:bg-[#3b1a66]"
            >
                + Insert Block
            </button>
            <button
                type="button"
                wire:click="autoFillSeo"
                class="inline-flex min-h-[42px] cursor-pointer items-center rounded border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:border-[#2b124c] hover:text-[#2b124c]"
            >
                Auto-Fill Meta &amp; OG
            </button>
            <button
                type="button"
                command="show-modal"
                commandfor="live-preview-dialog"
                class="inline-flex min-h-[42px] cursor-pointer items-center rounded border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-[#be185d] hover:border-[#be185d] hover:bg-[#fdf2f8]"
            >
                Live Article Preview
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                wire:click="savePost('draft')"
                class="min-h-[42px] cursor-pointer rounded border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:border-slate-400 hover:bg-slate-50"
            >
                Save Draft
            </button>
            <button
                type="button"
                wire:click="savePost('published')"
                class="min-h-[42px] cursor-pointer rounded bg-[#be185d] px-5 py-2 text-xs font-semibold text-white transition hover:bg-[#9d174d]"
            >
                {{ $status === 'published' ? 'Update Published' : 'Publish Dispatch' }}
            </button>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-12">
        <!-- Main Gutenberg-Class Block Canvas -->
        <div class="space-y-6 xl:col-span-8">
            <!-- Headline & Lead Card -->
            <section class="rounded-md border border-slate-200 bg-white p-5 shadow-xs sm:p-6">
                <div class="space-y-4">
                    <div>
                        <label for="studio-title" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">
                            Article Headline (H1)
                        </label>
                        <input
                            id="studio-title"
                            type="text"
                            wire:model.live.debounce.300ms="title"
                            class="mt-1.5 w-full rounded border border-slate-300 bg-white px-4 py-3 text-xl font-bold text-slate-900 focus:border-[#2b124c] focus:outline-none"
                        >
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <div class="flex items-center justify-between">
                                <label for="studio-slug" class="text-xs font-semibold uppercase tracking-wide text-slate-600">URL Slug</label>
                                <button type="button" wire:click="generateSlug" class="cursor-pointer text-xs font-semibold text-[#be185d] hover:text-[#2b124c]">
                                    Sync from Title
                                </button>
                            </div>
                            <input
                                id="studio-slug"
                                type="text"
                                wire:model.live.debounce.300ms="slug"
                                class="mt-1.5 w-full rounded border border-slate-300 bg-slate-50 px-3.5 py-2 font-mono text-xs text-[#2b124c] focus:border-[#2b124c] focus:outline-none"
                            >
                        </div>

                        <div>
                            <label for="studio-subtitle" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Deck / Subtitle
                            </label>
                            <input
                                id="studio-subtitle"
                                type="text"
                                wire:model.live.debounce.300ms="subtitle"
                                class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-[#2b124c] focus:outline-none"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="studio-excerpt" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                            Excerpt / Lead Summary
                        </label>
                        <textarea
                            id="studio-excerpt"
                            rows="2"
                            wire:model.live.debounce.300ms="excerpt"
                            class="mt-1.5 w-full rounded border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-800 focus:border-[#2b124c] focus:outline-none"
                        ></textarea>
                    </div>
                </div>
            </section>

            <!-- Quick Inline Block Strip -->
            <div class="flex flex-wrap items-center gap-2 rounded-md border border-slate-200 bg-white px-4 py-3 shadow-xs">
                <span class="me-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Quick Add Block:</span>
                @foreach ([
                    'paragraph' => '+ Paragraph',
                    'heading' => '+ Heading',
                    'spec_table' => '+ Spec Table',
                    'callout' => '+ Callout',
                    'faq' => '+ FAQ Accordion',
                    'stat_grid' => '+ Stat Grid',
                    'calculator_embed' => '+ Calculator',
                    'quote' => '+ Quote',
                    'code' => '+ Code/JSON',
                ] as $bType => $bLabel)
                    <button
                        type="button"
                        wire:click="addBlock('{{ $bType }}')"
                        class="min-h-[34px] cursor-pointer rounded border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:border-[#2b124c] hover:text-[#2b124c]"
                    >
                        {{ $bLabel }}
                    </button>
                @endforeach
            </div>

            <!-- Block Canvas List -->
            <div class="space-y-4">
                @foreach ($contentBlocks as $idx => $block)
                    @php
                        $blockType = (string) ($block['type'] ?? 'paragraph');
                    @endphp
                    <section wire:key="studio-block-{{ $block['id'] ?? $idx }}" class="rounded-md border border-slate-200 bg-white p-4 shadow-xs sm:p-5">
                        <!-- Block Header Toolbar -->
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                            <div class="flex items-center gap-2">
                                <span class="rounded border border-[#fbcfe8] bg-[#fdf2f8] px-2 py-0.5 font-mono text-xs font-semibold uppercase text-[#9d174d]">
                                    #{{ $idx + 1 }} &bull; {{ str_replace('_', ' ', $blockType) }}
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-1.5">
                                <button type="button" wire:click="moveBlockUp({{ $idx }})" class="cursor-pointer rounded border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-700 hover:border-[#2b124c]" aria-label="Move block up">
                                    &uarr;
                                </button>
                                <button type="button" wire:click="moveBlockDown({{ $idx }})" class="cursor-pointer rounded border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-700 hover:border-[#2b124c]" aria-label="Move block down">
                                    &darr;
                                </button>
                                <button type="button" wire:click="duplicateBlock({{ $idx }})" class="cursor-pointer rounded border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-[#2b124c] hover:border-[#2b124c]">
                                    Duplicate
                                </button>
                                <button type="button" wire:click="addBlock('paragraph', {{ $idx }})" class="cursor-pointer rounded border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-700 hover:border-[#2b124c]">
                                    + Below
                                </button>
                                <button type="button" wire:click="removeBlock({{ $idx }})" class="cursor-pointer rounded border border-rose-200 bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-600 hover:text-white">
                                    Remove
                                </button>
                            </div>
                        </div>

                        <!-- Block Editors by Type -->
                        @if ($blockType === 'heading')
                            <div class="grid gap-3 sm:grid-cols-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase text-slate-600">Level</label>
                                    <select wire:model.live="contentBlocks.{{ $idx }}.level" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900">
                                        <option value="2">H2 Section</option>
                                        <option value="3">H3 Subheading</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="block text-xs font-semibold uppercase text-slate-600">Heading Text</label>
                                    <input type="text" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.text" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-base font-bold text-slate-900">
                                </div>
                            </div>
                        @elseif ($blockType === 'paragraph')
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex flex-wrap gap-1.5">
                                        <button type="button" wire:click="insertMarkdownToken({{ $idx }}, 'bold')" class="cursor-pointer rounded border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-bold text-slate-800 hover:bg-slate-100">B</button>
                                        <button type="button" wire:click="insertMarkdownToken({{ $idx }}, 'italic')" class="cursor-pointer rounded border border-slate-200 bg-slate-50 px-2 py-1 text-xs italic text-slate-800 hover:bg-slate-100">I</button>
                                        <button type="button" wire:click="insertMarkdownToken({{ $idx }}, 'code')" class="cursor-pointer rounded border border-slate-200 bg-slate-50 px-2 py-1 font-mono text-xs text-[#2b124c] hover:bg-slate-100">&lt;/&gt;</button>
                                        <button type="button" wire:click="insertMarkdownToken({{ $idx }}, 'link_calc')" class="cursor-pointer rounded border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-medium text-[#be185d] hover:bg-slate-100">+ Calc Link</button>
                                        <button type="button" wire:click="insertMarkdownToken({{ $idx }}, 'link_prices')" class="cursor-pointer rounded border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-medium text-[#be185d] hover:bg-slate-100">+ Prices Link</button>
                                    </div>
                                    <label class="flex cursor-pointer items-center gap-2 text-xs text-slate-600">
                                        <input type="checkbox" wire:model.live="contentBlocks.{{ $idx }}.drop_cap" class="accent-[#2b124c]">
                                        <span>Drop Cap</span>
                                    </label>
                                </div>
                                <textarea rows="4" wire:model.live.debounce.300ms="contentBlocks.{{ $idx }}.text" class="w-full rounded border border-slate-300 bg-white px-3.5 py-2.5 text-sm leading-relaxed text-slate-900 focus:border-[#2b124c] focus:outline-none"></textarea>
                            </div>
                        @elseif ($blockType === 'callout')
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div>
                                    <label class="block text-xs font-semibold uppercase text-slate-600">Tone</label>
                                    <select wire:model.live="contentBlocks.{{ $idx }}.tone" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900">
                                        <option value="info">Telemetry Info</option>
                                        <option value="confirmed">Verified Filing</option>
                                        <option value="warning">Projection Warning</option>
                                        <option value="disclaimer">Non-Affiliation Disclaimer</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold uppercase text-slate-600">Callout Title</label>
                                    <input type="text" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.title" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm font-bold text-slate-900">
                                </div>
                                <div class="sm:col-span-3">
                                    <textarea rows="2" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.text" class="w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800"></textarea>
                                </div>
                            </div>
                        @elseif ($blockType === 'spec_table')
                            <div class="space-y-3">
                                <div class="flex flex-wrap items-end justify-between gap-3">
                                    <div class="flex-1">
                                        <label class="block text-xs font-semibold uppercase text-slate-600">Table Caption (Accessible Name)</label>
                                        <input type="text" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.caption" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm font-bold text-slate-900">
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" wire:click="addTableColumn({{ $idx }})" class="cursor-pointer rounded border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">+ Col</button>
                                        <button type="button" wire:click="addTableRow({{ $idx }})" class="cursor-pointer rounded bg-[#2b124c] px-3 py-2 text-xs font-semibold text-white hover:bg-[#3b1a66]">+ Row</button>
                                    </div>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="w-full text-xs">
                                        <thead>
                                            <tr>
                                                @foreach (($block['headers'] ?? []) as $hIdx => $header)
                                                    <th class="p-1">
                                                        <input type="text" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.headers.{{ $hIdx }}" class="w-full rounded border border-slate-300 bg-[#f8f7fb] px-2 py-1.5 font-semibold text-[#2b124c]">
                                                    </th>
                                                @endforeach
                                                <th class="w-10"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach (($block['rows'] ?? []) as $rIdx => $row)
                                                <tr>
                                                    @foreach (($block['headers'] ?? []) as $cIdx => $header)
                                                        <td class="p-1">
                                                            <input type="text" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.rows.{{ $rIdx }}.{{ $cIdx }}" class="w-full rounded border border-slate-200 bg-white px-2 py-1.5 text-slate-800">
                                                        </td>
                                                    @endforeach
                                                    <td class="p-1">
                                                        <button type="button" wire:click="removeTableRow({{ $idx }}, {{ $rIdx }})" class="cursor-pointer rounded px-2 py-1 text-rose-600 hover:bg-rose-50">&times;</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @elseif ($blockType === 'faq')
                            <div class="space-y-3">
                                <div class="flex items-end justify-between gap-3">
                                    <div class="flex-1">
                                        <label class="block text-xs font-semibold uppercase text-slate-600">FAQ Section Heading</label>
                                        <input type="text" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.heading" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm font-bold text-slate-900">
                                    </div>
                                    <button type="button" wire:click="addFaqItem({{ $idx }})" class="cursor-pointer rounded bg-[#2b124c] px-3 py-2 text-xs font-semibold text-white hover:bg-[#3b1a66]">+ Add Q&amp;A</button>
                                </div>
                                @foreach (($block['items'] ?? []) as $fIdx => $faq)
                                    <div class="space-y-2 rounded border border-slate-200 bg-[#f8f7fb] p-3">
                                        <div class="flex items-center justify-between gap-2">
                                            <input type="text" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.items.{{ $fIdx }}.question" class="flex-1 rounded border border-slate-300 bg-white px-2.5 py-1.5 text-sm font-semibold text-slate-900">
                                            <button type="button" wire:click="removeFaqItem({{ $idx }}, {{ $fIdx }})" class="cursor-pointer text-xs font-semibold text-rose-600 hover:text-rose-800">Delete</button>
                                        </div>
                                        <textarea rows="2" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.items.{{ $fIdx }}.answer" class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-700"></textarea>
                                    </div>
                                @endforeach
                            </div>
                        @elseif ($blockType === 'calculator_embed')
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div>
                                    <label class="block text-xs font-semibold uppercase text-slate-600">Calculator Tab</label>
                                    <select wire:model.live="contentBlocks.{{ $idx }}.calculator_type" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900">
                                        <option value="download">Download Time</option>
                                        <option value="pc">PC Rig &amp; FPS</option>
                                        <option value="edition">Edition Value</option>
                                        <option value="map">Map Scale</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold uppercase text-slate-600">Widget Headline</label>
                                    <input type="text" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.title" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm font-bold text-slate-900">
                                </div>
                                <div class="sm:col-span-2">
                                    <input type="text" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.summary" class="w-full rounded border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700">
                                </div>
                                <div>
                                    <input type="text" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.cta_label" class="w-full rounded border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-[#2b124c]">
                                </div>
                            </div>
                        @elseif ($blockType === 'quote')
                            <div class="space-y-2">
                                <textarea rows="2" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.text" class="w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm italic text-slate-900"></textarea>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    <input type="text" placeholder="Attribution / Source" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.source" class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-800">
                                    <input type="text" placeholder="Citation URL (optional)" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.cite_url" class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs text-[#2b124c]">
                                </div>
                            </div>
                        @elseif ($blockType === 'code')
                            <div class="space-y-2">
                                <div class="grid gap-2 sm:grid-cols-3">
                                    <input type="text" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.caption" class="sm:col-span-2 rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-900">
                                    <input type="text" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.language" class="rounded border border-slate-300 bg-slate-50 px-3 py-1.5 font-mono text-xs text-[#2b124c]">
                                </div>
                                <textarea rows="4" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.code" class="w-full rounded border border-slate-300 bg-[#1b1429] px-3 py-2 font-mono text-xs text-purple-100"></textarea>
                            </div>
                        @elseif ($blockType === 'stat_grid')
                            <div class="space-y-3">
                                <div class="flex items-end justify-between gap-2">
                                    <input type="text" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.title" class="flex-1 rounded border border-slate-300 bg-white px-3 py-2 text-sm font-bold text-slate-900">
                                    <button type="button" wire:click="addStatItem({{ $idx }})" class="cursor-pointer rounded bg-[#2b124c] px-3 py-2 text-xs font-semibold text-white hover:bg-[#3b1a66]">+ Stat</button>
                                </div>
                                <div class="grid gap-2 sm:grid-cols-3">
                                    @foreach (($block['items'] ?? []) as $sIdx => $stat)
                                        <div class="space-y-1.5 rounded border border-slate-200 bg-[#f8f7fb] p-2.5">
                                            <input type="text" placeholder="Label" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.items.{{ $sIdx }}.label" class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs text-slate-600">
                                            <input type="text" placeholder="Value" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.items.{{ $sIdx }}.value" class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-sm font-bold text-slate-900">
                                            <div class="flex gap-1">
                                                <input type="text" placeholder="Note" wire:model.live.debounce.250ms="contentBlocks.{{ $idx }}.items.{{ $sIdx }}.note" class="flex-1 rounded border border-slate-300 bg-white px-2 py-1 text-xs text-[#be185d]">
                                                <button type="button" wire:click="removeStatItem({{ $idx }}, {{ $sIdx }})" class="cursor-pointer px-1.5 text-xs text-rose-600">&times;</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </section>
                @endforeach
            </div>
        </div>

        <!-- WordPress Yoast/RankMath-Style Admin Sidebar Inspector -->
        <aside aria-label="Admin SEO, Document Settings and Revisions" class="space-y-5 xl:col-span-4">
            <!-- Admin-Only Score Gauges -->
            <div class="rounded-md border border-slate-200 border-t-2 border-t-[#2b124c] bg-white p-5 shadow-xs">
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded border border-slate-200 bg-[#f8f7fb] p-3 text-center">
                        <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Admin SEO Score</span>
                        <strong class="mt-1 block text-3xl font-bold {{ $this->seoAnalysis['seo_score'] >= 80 ? 'text-emerald-700' : ($this->seoAnalysis['seo_score'] >= 55 ? 'text-amber-700' : 'text-rose-700') }}">
                            {{ $this->seoAnalysis['seo_score'] }}<span class="text-sm font-medium text-slate-400">/100</span>
                        </strong>
                    </div>
                    <div class="rounded border border-slate-200 bg-[#f8f7fb] p-3 text-center">
                        <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Readability</span>
                        <strong class="mt-1 block text-3xl font-bold text-[#2b124c]">
                            {{ $this->seoAnalysis['readability_score'] }}<span class="text-sm font-medium text-slate-400">/100</span>
                        </strong>
                    </div>
                </div>
                <p class="mt-3 text-center text-xs text-slate-500">
                    {{ $this->seoAnalysis['word_count'] }} words &bull; ~{{ $this->seoAnalysis['reading_time_minutes'] }} min read &bull; AI GEO <strong class="font-semibold text-[#be185d]">{{ $this->seoAnalysis['geo_score'] }}/100</strong>
                </p>

                <!-- Inspector Subtabs -->
                <div class="mt-4 grid grid-cols-3 gap-1 rounded border border-slate-200 bg-slate-100 p-1 text-xs font-semibold">
                    <button type="button" wire:click="$set('sidebarTab', 'seo')" class="cursor-pointer rounded py-2 {{ $sidebarTab === 'seo' ? 'bg-[#2b124c] text-white' : 'text-slate-600 hover:text-slate-900' }}">
                        SEO &amp; SERP
                    </button>
                    <button type="button" wire:click="$set('sidebarTab', 'settings')" class="cursor-pointer rounded py-2 {{ $sidebarTab === 'settings' ? 'bg-[#2b124c] text-white' : 'text-slate-600 hover:text-slate-900' }}">
                        Document
                    </button>
                    <button type="button" wire:click="$set('sidebarTab', 'revisions')" class="cursor-pointer rounded py-2 {{ $sidebarTab === 'revisions' ? 'bg-[#2b124c] text-white' : 'text-slate-600 hover:text-slate-900' }}">
                        Revisions ({{ $this->revisions->count() }})
                    </button>
                </div>
            </div>

            @if ($sidebarTab === 'seo')
                <div class="space-y-4 rounded-md border border-slate-200 bg-white p-5 shadow-xs">
                    <div>
                        <label for="seo-kw" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">Focus Keyword</label>
                        <input id="seo-kw" type="text" wire:model.live.debounce.250ms="focusKeyword" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900">
                    </div>
                    <div>
                        <label for="seo-meta-title" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Meta Title</label>
                        <input id="seo-meta-title" type="text" wire:model.live.debounce.250ms="metaTitle" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900">
                    </div>
                    <div>
                        <label for="seo-meta-desc" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Meta Description</label>
                        <textarea id="seo-meta-desc" rows="3" wire:model.live.debounce.250ms="metaDescription" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700"></textarea>
                    </div>
                    <div>
                        <label for="seo-schema" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">JSON-LD Schema Type</label>
                        <select id="seo-schema" wire:model.live="schemaType" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900">
                            <option value="BlogPosting">BlogPosting</option>
                            <option value="TechArticle">TechArticle</option>
                            <option value="NewsArticle">NewsArticle</option>
                            <option value="FAQPage">FAQPage</option>
                        </select>
                    </div>

                    <!-- Google SERP Preview -->
                    <div class="rounded border border-slate-200 bg-[#f8f7fb] p-4">
                        <div class="mb-2 flex items-center justify-between text-[11px]">
                            <span class="font-semibold uppercase tracking-wide text-[#2b124c]">Google SERP Preview</span>
                            <div class="flex gap-1">
                                <button type="button" wire:click="$set('serpDevice', 'desktop')" class="cursor-pointer rounded px-2 py-0.5 {{ $serpDevice === 'desktop' ? 'bg-[#2b124c] text-white' : 'text-slate-600' }}">Desktop</button>
                                <button type="button" wire:click="$set('serpDevice', 'mobile')" class="cursor-pointer rounded px-2 py-0.5 {{ $serpDevice === 'mobile' ? 'bg-[#2b124c] text-white' : 'text-slate-600' }}">Mobile</button>
                            </div>
                        </div>
                        <div class="{{ $serpDevice === 'mobile' ? 'max-w-[300px]' : '' }}">
                            <p class="truncate text-xs text-emerald-700">https://gtavi.pro/gta/{{ $slug }}</p>
                            <p class="mt-0.5 text-base font-bold text-blue-700">{{ $metaTitle ?: $title }}</p>
                            <p class="mt-1 text-xs leading-snug text-slate-600">{{ \Illuminate\Support\Str::limit($metaDescription ?: $excerpt, 155) }}</p>
                        </div>
                    </div>

                    <!-- Checklist -->
                    <ul class="space-y-2 text-xs">
                        @foreach ($this->seoAnalysis['checks'] as $check)
                            <li class="rounded border border-slate-200 bg-slate-50 p-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-semibold {{ $check['status'] === 'pass' ? 'text-emerald-700' : ($check['status'] === 'warn' ? 'text-amber-700' : 'text-rose-700') }}">
                                        {{ strtoupper($check['status']) }} &bull; {{ $check['label'] }}
                                    </span>
                                    <span class="font-mono text-slate-500">{{ $check['points'] }}/{{ $check['max_points'] }}</span>
                                </div>
                                <p class="mt-1 text-[11px] text-slate-600">{{ $check['detail'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @elseif ($sidebarTab === 'settings')
                <div class="space-y-4 rounded-md border border-slate-200 bg-white p-5 shadow-xs">
                    <div>
                        <label for="doc-cat" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">Category</label>
                        <select id="doc-cat" wire:model.live="categoryId" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900">
                            @foreach ($this->categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="doc-badge" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Verification Badge</label>
                        <select id="doc-badge" wire:model.live="verificationBadge" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900">
                            <option value="official_filing">Official Filing</option>
                            <option value="confirmed">Confirmed Fact</option>
                            <option value="technical_projection">Technical Projection</option>
                            <option value="community_speculation">Community Analysis</option>
                        </select>
                    </div>
                    <div>
                        <label for="doc-tags" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Tags (comma separated)</label>
                        <input id="doc-tags" type="text" wire:model.live="tagsInput" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900">
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="doc-author" class="block text-xs font-semibold uppercase text-slate-600">Author</label>
                            <input id="doc-author" type="text" wire:model.live="authorName" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-xs text-slate-900">
                        </div>
                        <div>
                            <label for="doc-role" class="block text-xs font-semibold uppercase text-slate-600">Role</label>
                            <input id="doc-role" type="text" wire:model.live="authorRole" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-xs text-slate-900">
                        </div>
                    </div>
                    <div>
                        <label for="doc-note" class="block text-xs font-semibold uppercase text-slate-600">Revision Changelog Note</label>
                        <input id="doc-note" type="text" wire:model.live="editorNote" placeholder="e.g., Added PS5 Pro PSSR table" class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-xs text-slate-900">
                    </div>
                    <label class="flex cursor-pointer items-center gap-2 text-xs font-semibold text-slate-800">
                        <input type="checkbox" wire:model.live="isFeatured" class="accent-[#2b124c]">
                        <span>Pin as Featured Dispatch on Homepage</span>
                    </label>
                </div>
            @else
                <div class="space-y-3 rounded-md border border-slate-200 bg-white p-5 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#2b124c]">Saved Revision Snapshots</p>
                    @forelse ($this->revisions as $rev)
                        <div class="rounded border border-slate-200 bg-[#f8f7fb] p-3 text-xs">
                            <p class="font-bold text-slate-900">#{{ $rev->id }} &bull; SEO {{ $rev->seo_score }}/100</p>
                            <p class="mt-0.5 text-slate-600">{{ $rev->editor_note ?: 'Studio Snapshot' }}</p>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-[11px] text-slate-500">{{ $rev->created_at?->diffForHumans() }}</span>
                                <button type="button" wire:click="restoreRevision({{ $rev->id }})" class="cursor-pointer font-semibold text-[#be185d] hover:text-[#2b124c]">
                                    Restore
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500">Save this dispatch to start recording revision snapshots.</p>
                    @endforelse
                </div>
            @endif
        </aside>
    </div>

    <!-- Native Modal Dialog: Gutenberg-style Block Inserter -->
    <dialog id="block-inserter-dialog" wire:ignore.self closedby="any" aria-labelledby="block-inserter-title" class="vice-dialog">
        <div class="p-6">
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-3">
                <h2 id="block-inserter-title" class="text-lg font-bold text-slate-900">Insert Structured Content Block</h2>
                <form method="dialog">
                    <button type="submit" autofocus class="cursor-pointer rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Close
                    </button>
                </form>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                @foreach ([
                    ['type' => 'paragraph', 'name' => 'Rich Paragraph', 'desc' => 'Supports bold, italic, inline code, internal links, and drop-cap.'],
                    ['type' => 'heading', 'name' => 'Section Heading', 'desc' => 'Auto-populates article Table of Contents anchors.'],
                    ['type' => 'spec_table', 'name' => 'Responsive Spec Table', 'desc' => 'Container-query stacked table for console/PC comparisons.'],
                    ['type' => 'faq', 'name' => 'FAQ Accordion', 'desc' => 'Generates JSON-LD FAQPage schema automatically.'],
                    ['type' => 'stat_grid', 'name' => 'Telemetry Stat Grid', 'desc' => 'High-contrast KPI metric cards.'],
                    ['type' => 'calculator_embed', 'name' => 'Calculator Embed', 'desc' => 'Deep-links to Download, PC, Edition, or Map tools.'],
                    ['type' => 'callout', 'name' => 'Editorial Callout', 'desc' => 'Confirmed filing, warning, or disclaimer callout box.'],
                    ['type' => 'quote', 'name' => 'Attributed Quote', 'desc' => 'Semantic blockquote with citation source.'],
                    ['type' => 'code', 'name' => 'Code / Telemetry Block', 'desc' => 'Keyboard-scrollable syntax region.'],
                ] as $item)
                    <form method="dialog">
                        <button
                            type="submit"
                            wire:click="addBlock('{{ $item['type'] }}')"
                            class="w-full cursor-pointer rounded border border-slate-200 bg-slate-50 p-3.5 text-start transition hover:border-[#2b124c] hover:bg-purple-50"
                        >
                            <span class="block text-sm font-bold text-[#2b124c]">{{ $item['name'] }}</span>
                            <span class="mt-1 block text-xs text-slate-600">{{ $item['desc'] }}</span>
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </dialog>

    <!-- Native Modal Dialog: Full Live Article Preview -->
    <dialog id="live-preview-dialog" wire:ignore.self closedby="any" aria-labelledby="live-preview-title" class="vice-dialog">
        <div class="max-h-[82dvh] overflow-y-auto p-6 sm:p-8">
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-4">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wide text-[#be185d]">Live Rendered Output</span>
                    <h2 id="live-preview-title" class="text-2xl font-bold text-slate-900">{{ $title }}</h2>
                </div>
                <form method="dialog">
                    <button type="submit" autofocus class="cursor-pointer rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Close Preview
                    </button>
                </form>
            </div>
            <div class="article-prose mt-6">
                {!! $this->previewHtml !!}
            </div>
        </div>
    </dialog>
</div>
