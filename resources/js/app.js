import intlTelInput from 'intl-tel-input';
import 'intl-tel-input/dist/css/intlTelInput.css';
import collapse from '@alpinejs/collapse';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

// Register Alpine plugins (Alpine is bundled by Livewire 4).
document.addEventListener('alpine:init', () => {
    window.Alpine?.plugin(collapse);

    /**
     * localityCombobox — grouped locality selector with live search.
     * Extracted here from the Blade @push('scripts') to ensure it is registered
     * before Alpine processes the DOM (avoids 'search is not defined' errors).
     */
    window.Alpine?.data('localityCombobox', ({ groups, model }) => ({
        open: false,
        search: '',
        groups,
        model,
        selected: model,
        listMaxHeight: 280,
        panelStyle: '',

        init() {
            this.$watch('selected', (value) => { this.model = value; });
            this.$watch('model', (value) => { this.selected = value; });
        },

        get filteredGroups() {
            const term = this.search.trim().toLowerCase();
            const result = {};
            for (const [region, communes] of Object.entries(this.groups)) {
                const matches = term === ''
                    ? communes
                    : communes.filter((c) => c.toLowerCase().includes(term));
                if (matches.length) result[region] = matches;
            }
            return result;
        },

        get isEmpty() {
            return Object.keys(this.filteredGroups).length === 0;
        },

        toggle() { this.open ? this.close() : this.openPanel(); },

        openPanel() {
            this.open = true;
            this.computeHeight();
            this.$nextTick(() => this.$refs.search?.focus());
        },

        close() { this.open = false; this.search = ''; },

        choose(commune) { this.selected = commune; this.model = commune; this.close(); },

        computeHeight() {
            const rect = this.$refs.trigger.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom - 24;
            this.listMaxHeight = Math.max(160, Math.min(320, spaceBelow - 56));
        },
    }));

    /**
     * imageCropper — Alpine data component for the portrait upload + crop flow.
     *
     * Usage:
     *   <div x-data="imageCropper">…</div>
     *
     * Publishes:
     *   hasPreview    — bool, true once a file is selected
     *   previewSrc    — data URL for the <img> fed to Cropper
     *   uploading     — bool, true while Livewire is receiving the cropped file
     *
     * Methods:
     *   handleFileSelect($event) — read selected file and init Cropper
     *   confirmCrop()            — crop canvas → blob → $wire.upload('photo', …)
     *   cancelCrop()             — destroy cropper, reset state
     */
    window.Alpine?.data('imageCropper', () => ({
        cropper: null,
        hasPreview: false,
        previewSrc: null,
        uploading: false,

        handleFileSelect(event) {
            const file = event.target.files?.[0];
            if (!file) return;

            // Client-side size guard (5 MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('L\'image ne doit pas dépasser 5 Mo.');
                event.target.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                this.previewSrc = e.target.result;
                this.hasPreview = true;
                this.$nextTick(() => {
                    const img = this.$refs.cropperImage;
                    if (!img) return;
                    if (this.cropper) this.cropper.destroy();
                    this.cropper = new Cropper(img, {
                        aspectRatio: 4 / 5,
                        viewMode: 1,
                        autoCropArea: 0.9,
                        dragMode: 'move',
                        guides: false,
                        center: false,
                        highlight: false,
                        background: false,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                    });
                });
            };
            reader.readAsDataURL(file);
        },

        confirmCrop() {
            if (!this.cropper) return;
            this.uploading = true;

            const canvas = this.cropper.getCroppedCanvas({ width: 800, height: 1000, imageSmoothingQuality: 'high' });
            canvas.toBlob((blob) => {
                if (!blob) { this.uploading = false; return; }
                const file = new File([blob], 'portrait.jpg', { type: 'image/jpeg' });

                this.$wire.upload(
                    'photo',
                    file,
                    () => { this.uploading = false; this.hasPreview = false; },
                    () => { this.uploading = false; alert('Erreur lors du téléversement.'); },
                );
            }, 'image/jpeg', 0.9);
        },

        cancelCrop() {
            if (this.cropper) { this.cropper.destroy(); this.cropper = null; }
            this.hasPreview = false;
            this.previewSrc = null;
            this.uploading = false;
            const input = this.$el.querySelector('input[type="file"]');
            if (input) input.value = '';
        },
    }));
});

/**
 * Initialize intl-tel-input on every [data-intl-phone-field] wrapper.
 *
 * The wrapper carries wire:ignore so Livewire never morphs it — the widget
 * stays alive across re-renders. Country and formatted number are pushed back
 * to Livewire via Livewire.find() on blur and country-change.
 */
function initPhoneFields(root = document) {
    root.querySelectorAll('[data-intl-phone-field]').forEach((wrapper) => {
        if (wrapper._iti) {
            return;
        }

        const phoneInput = wrapper.querySelector('input[type="tel"]');

        if (!phoneInput) {
            return;
        }

        const initialCountry = (wrapper.dataset.phoneInitialCountry || 'CH').toLowerCase();

        const iti = intlTelInput(phoneInput, {
            initialCountry,
            countryOrder: ['ch', 'fr', 'de', 'it'],
            separateDialCode: true,
            numberDisplayFormat: 'NATIONAL',
            loadUtils: () => import('intl-tel-input/dist/js/utils.js'),
        });

        wrapper._iti = iti;

        const getLivewireComponent = () => {
            const componentEl = wrapper.closest('[wire\\:id]');

            if (!componentEl) {
                return null;
            }

            return window.Livewire?.find(componentEl.getAttribute('wire:id')) ?? null;
        };

        phoneInput.addEventListener('countrychange', () => {
            // Sync country code to Livewire immediately on change
            const component = getLivewireComponent();

            if (component) {
                const iso = (iti.getSelectedCountry()?.iso2 ?? 'ch').toUpperCase();
                component.set('phoneCountry', iso);
            }
        });

        phoneInput.addEventListener('blur', () => {
            const component = getLivewireComponent();

            if (!component) {
                return;
            }

            const number = iti.getNumber() || phoneInput.value.trim();

            if (number) {
                // Writing phone triggers the updated() lifecycle which runs validateOnly('phone')
                component.set('phone', number);
            }
        });
    });
}

document.addEventListener('livewire:navigated', () => {
    document.querySelectorAll('[data-intl-phone-field]').forEach((w) => {
        delete w._iti;
    });

    initPhoneFields();
});

