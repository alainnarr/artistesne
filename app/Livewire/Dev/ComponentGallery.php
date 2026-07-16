<?php

namespace App\Livewire\Dev;

use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.dev', ['sections' => [
    'foundations' => 'Fondations',
    'page-shell' => 'Page shell (section / hero / page-header)',
    'buttons' => 'Buttons',
    'tags' => 'Tags',
    'badges' => 'Badges',
    'inputs' => 'Field (input / select / textarea)',
    'selection' => 'Checkbox / Radio',
    'datepicker' => 'Datepicker',
    'search' => 'Search',
    'sort-menu' => 'Sort menu',
    'card-artist' => 'Card Artist',
    'list' => 'List',
    'list-header' => 'List header',
    'profile-section' => 'Profile section',
    'link-list-item' => 'Link list item',
    'accordion' => 'Accordion',
    'banner' => 'Banners',
    'empty-state' => 'Empty state',
    'modal' => 'Modal',
    'cookies' => 'Cookies banner',
    'email' => 'E-mail layout',
]])]
class ComponentGallery extends Component
{
    public function render(): View
    {
        return view('livewire.dev.component-gallery');
    }
}
