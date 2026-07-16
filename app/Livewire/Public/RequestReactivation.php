<?php

namespace App\Livewire\Public;

use App\Database\Models\User;
use App\Enums\UserRole;
use App\Notifications\ReactivationRequestNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public')]
class RequestReactivation extends Component
{
    public string $email = '';

    public bool $submitted = false;

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
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

        Notification::send($admins, new ReactivationRequestNotification(
            senderEmail: $data['email'],
        ));

        $this->submitted = true;
    }

    public function render(): View
    {
        return view('livewire.public.request-reactivation');
    }
}
