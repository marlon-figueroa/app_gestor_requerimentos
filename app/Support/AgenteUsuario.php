<?php

namespace App\Support;

class AgenteUsuario
{
    /**
     * @return array{0: ?string, 1: ?string, 2: ?string}
     */
    public static function analizar(?string $userAgent): array
    {
        if (blank($userAgent)) {
            return [null, null, null];
        }

        $navegadores = [
            'Edge' => '/Edg(e|A|iOS)?\//',
            'Opera' => '/OPR\/|Opera/',
            'Chrome' => '/Chrome\/|CriOS\//',
            'Firefox' => '/Firefox\/|FxiOS\//',
            'Safari' => '/Safari\//',
            'curl' => '/curl\//i',
        ];

        $sistemas = [
            'Windows' => '/Windows NT/',
            'Android' => '/Android/',
            'iOS' => '/iPhone|iPad|iPod/',
            'macOS' => '/Mac OS X|Macintosh/',
            'Linux' => '/Linux/',
        ];

        $navegador = collect($navegadores)->search(fn ($regex) => preg_match($regex, $userAgent)) ?: 'Otro';
        $sistema = collect($sistemas)->search(fn ($regex) => preg_match($regex, $userAgent)) ?: 'Otro';

        $dispositivo = match (true) {
            (bool) preg_match('/iPad|Tablet/i', $userAgent) => 'Tablet',
            (bool) preg_match('/Mobile|Android|iPhone/i', $userAgent) => 'Móvil',
            (bool) preg_match('/bot|crawl|spider|curl/i', $userAgent) => 'Bot',
            default => 'Escritorio',
        };

        return [$navegador, $sistema, $dispositivo];
    }
}
