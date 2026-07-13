<div {{ $attributes->merge(['class' => 'rich-text-diff prose prose-sm max-w-none dark:prose-invert']) }}>
    {!! \App\Support\RichTextDiff::html($old, $new) !!}
</div>

@once
    <style>
        .rich-text-diff ins {
            background-color: rgb(187 247 208);
            color: rgb(20 83 45);
            text-decoration: none;
            padding: 0 .15rem;
            border-radius: .15rem;
        }
        .rich-text-diff del {
            background-color: rgb(254 202 202);
            color: rgb(127 29 29);
            padding: 0 .15rem;
            border-radius: .15rem;
        }
        .dark .rich-text-diff ins {
            background-color: rgba(34, 197, 94, .25);
            color: rgb(187 247 208);
        }
        .dark .rich-text-diff del {
            background-color: rgba(239, 68, 68, .25);
            color: rgb(254 202 202);
        }
    </style>
@endonce
