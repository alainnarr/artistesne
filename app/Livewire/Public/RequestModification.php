<?php

namespace App\Livewire\Public;

use App\Database\Models\User;
use App\Enums\UserRole;
use App\Notifications\ModificationRequestNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public')]
class RequestModification extends Component
{
    public string $email = '';

    public string $request_type = 'update';

    public string $turnstileToken = '';

    public bool $submitted = false;

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'request_type' => ['required', Rule::in(['update', 'delete'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'email.required' => 'Ce champ est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse e-mail valide.',
        ];
    }

    public function submit(): void
    {
        $data = $this->validate();

        $admins = User::where('role', UserRole::Admin)->get();

        Notification::send($admins, new ModificationRequestNotification(
            senderEmail: $data['email'],
            requestType: $data['request_type'],
        ));

        $this->submitted = true;
    }

    public function render(): View
    {
        return view('livewire.public.request-modification');
    }
}
