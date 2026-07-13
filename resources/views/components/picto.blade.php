@props([
    'name',
    'set' => 'icons', // icons | logos | social
    'class' => 'h-5 w-5',
])
@php
    $relPath = "svg/{$set}/{$name}.svg";
    $fullPath = resource_path($relPath);
    $svg = file_exists($fullPath) ? file_get_contents($fullPath) : '';
    // Inject classes & aria-hidden onto the root <svg> tag.
    if ($svg !== '') {
        $classAttr = trim($class);
        $svg = preg_replace(
            '/<svg\b([^>]*)>/i',
            '<svg$1 class="'.e($classAttr).'" aria-hidden="true" focusable="false">',
            $svg,
            1
        );
    }
@endphp
{!! $svg !!}
