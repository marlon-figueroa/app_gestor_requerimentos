<?php

namespace App\Notifications;

use App\Models\Requerimiento;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequerimientoFlujoNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Requerimiento $requerimiento,
        public string $evento,
        public ?string $detalle = null,
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
            ->subject($this->titulo())
            ->line($this->detalle ?? $this->titulo())
            ->action('Ver requerimiento', route('requerimientos.show', $this->requerimiento));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'requerimiento_id' => $this->requerimiento->id,
            'codigo' => $this->requerimiento->codigo,
            'evento' => $this->evento,
            'mensaje' => $this->detalle ?? $this->titulo(),
            'url' => route('requerimientos.show', $this->requerimiento),
        ];
    }

    private function titulo(): string
    {
        return match ($this->evento) {
            'enviar' => "{$this->requerimiento->codigo} enviado a revisión",
            'tomar' => "{$this->requerimiento->codigo} asignado a revisión",
            'rechazar' => "{$this->requerimiento->codigo} rechazado",
            'aprobar' => "{$this->requerimiento->codigo} aprobado",
            'vencimiento' => "Vencimiento próximo: {$this->requerimiento->codigo}",
            default => "Actualización en {$this->requerimiento->codigo}",
        };
    }
}
