<?php

namespace App\Notifications;

use App\Models\Proyecto;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProyectoPlazoNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Proyecto $proyecto,
        public string $mensaje,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Proyecto {$this->proyecto->codigo}: plazo")
            ->line($this->mensaje)
            ->action('Ver proyecto', route('proyectos.show', $this->proyecto));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'proyecto_id' => $this->proyecto->id,
            'codigo' => $this->proyecto->codigo,
            'mensaje' => $this->mensaje,
            'url' => route('proyectos.show', $this->proyecto),
        ];
    }
}
