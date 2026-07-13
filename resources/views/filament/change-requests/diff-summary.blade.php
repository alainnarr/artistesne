@php
    $labels = [
        'name' => "Nom d'artiste",
        'discipline' => 'Domaine principal',
        'secondary_discipline' => 'Domaine secondaire',
        'city' => 'Ville / Commune',
        'biography' => 'Biographie',
        'links' => 'Liens',
        'activities' => 'Activités principales',
        'secondary_activities' => 'Activités secondaires',
        'keywords' => 'Mots-clés',
        'collaborations' => 'Collaborations',
        'email' => 'E-mail',
        'phone' => 'Téléphone',
        'display_contact_button' => 'Afficher le bouton de contact',
    ];

    $normalizeLinks = function ($links): array {
        if (! is_array($links)) {
            return [];
        }

        $out = [];
        foreach ($links as $link) {
            if (! is_array($link)) {
                continue;
            }
            $url = trim((string) ($link['url'] ?? ''));
            $label = trim((string) ($link['label'] ?? '')) ?: $url;
            if ($url === '') {
                continue;
            }
            $out[$url] = $label;
        }

        return $out;
    };

    $normalizeCollaborations = function ($items): array {
        if (! is_array($items)) {
            return [];
        }
        $out = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $out[] = $name.(isset($item['url']) && $item['url'] ? ' ('.$item['url'].')' : '');
        }
        return $out;
    };
@endphp

<style>
        .crf {
            --crf-bg: #ffffff;
            --crf-border: rgb(228 228 231);
            --crf-header-bg: rgb(244 244 245);
            --crf-text: rgb(39 39 42);
            --crf-muted: rgb(113 113 122);
            --crf-add-bg: rgb(220 252 231);
            --crf-add-text: rgb(22 101 52);
            --crf-add-strong: rgb(21 128 61);
            --crf-del-bg: rgb(254 226 226);
            --crf-del-text: rgb(153 27 27);
            --crf-del-strong: rgb(185 28 28);
        }
        :where(.dark, [data-theme="dark"]) .crf,
        .fi-dark .crf,
        .dark .crf {
            --crf-bg: rgb(24 24 27 / 0.6);
            --crf-border: rgb(63 63 70);
            --crf-header-bg: rgb(39 39 42);
            --crf-text: rgb(228 228 231);
            --crf-muted: rgb(161 161 170);
            --crf-add-bg: rgba(34, 197, 94, 0.18);
            --crf-add-text: rgb(187 247 208);
            --crf-add-strong: rgb(134 239 172);
            --crf-del-bg: rgba(239, 68, 68, 0.18);
            --crf-del-text: rgb(254 202 202);
            --crf-del-strong: rgb(252 165 165);
        }
        .crf { display: flex; flex-direction: column; gap: 1rem; color: var(--crf-text); }
        .crf-card { border: 1px solid var(--crf-border); border-radius: 0.75rem; overflow: hidden; background: var(--crf-bg); }
        .crf-head { display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 1rem; background: var(--crf-header-bg); border-bottom: 1px solid var(--crf-border); font-size: 0.875rem; font-weight: 600; }
        .crf-badge { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--crf-muted); font-weight: 500; }
        .crf-body { padding: 1rem; font-size: 0.875rem; line-height: 1.5; }
        .crf-empty { font-style: italic; color: var(--crf-muted); margin: 0; }
        .crf-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 0.35rem; }
        .crf-row { display: flex; align-items: baseline; gap: 0.5rem; }
        .crf-marker { display: inline-block; width: 1rem; text-align: center; font-weight: 700; }
        .crf-url { font-size: 0.75rem; color: var(--crf-muted); }
        .crf-row-kept { color: var(--crf-muted); }
        .crf-row-add { color: var(--crf-add-strong); }
        .crf-row-del { color: var(--crf-del-strong); }
        .crf-row-del .crf-label { text-decoration: line-through; }
        .crf-row-add .crf-label { font-weight: 500; }
        .crf-diff ins { background: var(--crf-add-bg); color: var(--crf-add-text); text-decoration: none; padding: 0 .15rem; border-radius: .15rem; }
        .crf-diff del { background: var(--crf-del-bg); color: var(--crf-del-text); padding: 0 .15rem; border-radius: .15rem; }
        .crf-diff p { margin: 0 0 .5rem 0; }
        .crf-diff p:last-child { margin-bottom: 0; }
        /* Side-by-side */
        .crf-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
        .crf-col { padding: 1rem; font-size: 0.875rem; line-height: 1.5; }
        .crf-col + .crf-col { border-left: 1px solid var(--crf-border); }
        .crf-col-head { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--crf-muted); font-weight: 600; margin-bottom: .5rem; }
        .crf-col-old { background: color-mix(in srgb, var(--crf-del-bg) 30%, var(--crf-bg)); }
        .crf-col-new { background: color-mix(in srgb, var(--crf-add-bg) 30%, var(--crf-bg)); }
        /* Tag chips */
        .crf-tags { display: flex; flex-wrap: wrap; gap: .4rem; margin: 0; padding: 0; list-style: none; }
        .crf-tag { display: inline-flex; align-items: center; gap: .2rem; padding: .2rem .55rem; border-radius: 999px; font-size: .8rem; font-weight: 500; }
        .crf-tag-kept { background: var(--crf-header-bg); color: var(--crf-muted); }
        .crf-tag-add { background: var(--crf-add-bg); color: var(--crf-add-strong); }
        .crf-tag-del { background: var(--crf-del-bg); color: var(--crf-del-strong); }
        @media (max-width: 640px) {
            .crf-cols { grid-template-columns: 1fr; }
            .crf-col + .crf-col { border-left: none; border-top: 1px solid var(--crf-border); }
        }
    </style>

<div class="crf">
    @forelse ($payload as $field => $newValue)
        @php
            $oldValue = $artist?->{$field};
            $label = $labels[$field] ?? $field;
            $isArray = is_array($newValue) || is_array($oldValue);
        @endphp

        <div class="crf-card">
            <div class="crf-head">
                <span>{{ $label }}</span>
                <span class="crf-badge">Diff</span>
            </div>

            <div class="crf-body">
                @if ($field === 'biography')
                    <div class="crf-diff">{!! \App\Support\RichTextDiff::html((string) $oldValue, (string) $newValue) !!}</div>

                @elseif ($field === 'links')
                    @php
                        $oldLinks = $normalizeLinks($oldValue);
                        $newLinks = $normalizeLinks($newValue);
                        $removed = array_diff_key($oldLinks, $newLinks);
                        $added = array_diff_key($newLinks, $oldLinks);
                        $kept = array_intersect_key($oldLinks, $newLinks);
                    @endphp
                    @if (empty($removed) && empty($added))
                        <p class="crf-empty">Aucun changement effectif.</p>
                    @else
                        <ul class="crf-tags">
                            @foreach ($kept as $url => $lbl)
                                <li class="crf-tag crf-tag-kept" title="{{ $url }}">{{ $lbl }}</li>
                            @endforeach
                            @foreach ($removed as $url => $lbl)
                                <li class="crf-tag crf-tag-del" title="{{ $url }}">− {{ $lbl }}</li>
                            @endforeach
                            @foreach ($added as $url => $lbl)
                                <li class="crf-tag crf-tag-add" title="{{ $url }}">+ {{ $lbl }}</li>
                            @endforeach
                        </ul>
                    @endif

                @elseif ($field === 'collaborations')
                    @php
                        $oldItems = $normalizeCollaborations($oldValue ?? []);
                        $newItems = $normalizeCollaborations($newValue ?? []);
                        $removed = array_diff($oldItems, $newItems);
                        $added = array_diff($newItems, $oldItems);
                        $kept = array_intersect($oldItems, $newItems);
                    @endphp
                    @if (empty($removed) && empty($added))
                        <p class="crf-empty">Aucun changement effectif.</p>
                    @else
                        <ul class="crf-tags">
                            @foreach ($kept as $item)
                                <li class="crf-tag crf-tag-kept">{{ $item }}</li>
                            @endforeach
                            @foreach ($removed as $item)
                                <li class="crf-tag crf-tag-del">− {{ $item }}</li>
                            @endforeach
                            @foreach ($added as $item)
                                <li class="crf-tag crf-tag-add">+ {{ $item }}</li>
                            @endforeach
                        </ul>
                    @endif

                @elseif ($isArray)
                    @php
                        $oldArr = is_array($oldValue) ? $oldValue : [];
                        $newArr = is_array($newValue) ? $newValue : [];
                        $removed = array_values(array_diff($oldArr, $newArr));
                        $added = array_values(array_diff($newArr, $oldArr));
                        $kept = array_values(array_intersect($oldArr, $newArr));
                    @endphp
                    @if (empty($removed) && empty($added))
                        <p class="crf-empty">Aucun changement effectif.</p>
                    @else
                        <ul class="crf-tags">
                            @foreach ($kept as $item)
                                <li class="crf-tag crf-tag-kept">{{ $item }}</li>
                            @endforeach
                            @foreach ($removed as $item)
                                <li class="crf-tag crf-tag-del">− {{ $item }}</li>
                            @endforeach
                            @foreach ($added as $item)
                                <li class="crf-tag crf-tag-add">+ {{ $item }}</li>
                            @endforeach
                        </ul>
                    @endif

                @elseif ($field === 'display_contact_button')
                    @php
                        $oldStr = $oldValue ? 'Oui' : 'Non';
                        $newStr = $newValue ? 'Oui' : 'Non';
                    @endphp
                    <div class="crf-cols">
                        <div class="crf-col crf-col-old">
                            <p class="crf-col-head">Actuel</p>
                            <span>{{ $oldStr }}</span>
                        </div>
                        <div class="crf-col crf-col-new">
                            <p class="crf-col-head">Proposé</p>
                            <span>{{ $newStr }}</span>
                        </div>
                    </div>

                @else
                    @php
                        $oldStr = is_scalar($oldValue) || $oldValue === null ? (string) $oldValue : json_encode($oldValue, JSON_UNESCAPED_UNICODE);
                        $newStr = is_scalar($newValue) || $newValue === null ? (string) $newValue : json_encode($newValue, JSON_UNESCAPED_UNICODE);
                    @endphp
                    @if ($oldStr === $newStr)
                        <p class="crf-empty">Aucun changement.</p>
                    @else
                        <div class="crf-cols">
                            <div class="crf-col crf-col-old">
                                <p class="crf-col-head">Actuel</p>
                                <p style="white-space:pre-wrap; margin:0;">{{ $oldStr ?: '—' }}</p>
                            </div>
                            <div class="crf-col crf-col-new">
                                <p class="crf-col-head">Proposé</p>
                                <p style="white-space:pre-wrap; margin:0;">{{ $newStr ?: '—' }}</p>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @empty
        <p class="crf-empty">Aucune modification proposée.</p>
    @endforelse
</div>
