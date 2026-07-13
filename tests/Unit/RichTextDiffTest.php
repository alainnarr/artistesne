<?php

use App\Support\RichTextDiff;

it('renders an html diff with insertions and deletions', function () {
    $old = '<p>Bonjour le monde</p>';
    $new = '<p>Bonjour la France</p>';

    $diff = RichTextDiff::html($old, $new);

    expect($diff)->toContain('<del');
    expect($diff)->toContain('<ins');
    expect($diff)->toContain('France');
});

it('returns the new content when old is null', function () {
    $diff = RichTextDiff::html(null, '<p>Nouveau</p>');

    expect($diff)->toContain('Nouveau');
});

it('returns the same content when nothing changed', function () {
    $diff = RichTextDiff::html('<p>Identique</p>', '<p>Identique</p>');

    expect($diff)->toContain('Identique');
    expect($diff)->not->toContain('<ins');
    expect($diff)->not->toContain('<del');
});
