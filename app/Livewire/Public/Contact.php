<?php

namespace App\Livewire\Public;

use App\Database\Models\User;
use App\Enums\UserRole;
use App\Notifications\ContactMessageNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public')]
class Contact extends Component
{
    public string $last_name = '';

    public string $first_name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    public string $turnstileToken = '';

    public bool $submitted = false;

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'last_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'last_name.required' => 'Ce champ est obligatoire.',
            'first_name.required' => 'Ce champ est obligatoire.',
            'email.required' => 'Ce champ est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse e-mail valide.',
            'subject.required' => 'Ce champ est obligatoire.',
            'message.required' => 'Ce champ est obligatoire.',
        ];
    }

    public function submit(): void
    {
        $data = $this->validate();

        $admins = User::where('role', UserRole::ADMIN)->get();

        Notification::send($admins, new ContactMessageNotification(
            lastName: $data['last_name'],
            firstName: $data['first_name'],
            senderEmail: $data['email'],
            messageSubject: $data['subject'],
            body: $data['message'],
        ));

        $this->submitted = true;
    }

    public function render(): View
    {
        return view('livewire.public.contact');
    }
}
