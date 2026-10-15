<?php

namespace App\Enums;

enum CaracteristicaGeneralPf: int
{
    case ComunicacionDatos = 0;
    case ProcesamientoDistribuido = 1;
    case Rendimiento = 2;
    case ConfiguracionAlta = 3;
    case VolumenTransaccional = 4;
    case EntradaEnLinea = 5;
    case EficienciaUsuarioFinal = 6;
    case ActualizacionEnLinea = 7;
    case ComplejidadProcesamiento = 8;
    case Reusabilidad = 9;
    case FacilidadInstalacion = 10;
    case FacilidadOperacion = 11;
    case MultiplesSitios = 12;
    case FacilidadCambios = 13;

    public function label(): string
    {
        return match ($this) {
            self::ComunicacionDatos => 'Comunicación de datos',
            self::ProcesamientoDistribuido => 'Procesamiento distribuido',
            self::Rendimiento => 'Rendimiento',
            self::ConfiguracionAlta => 'Configuración de alta carga',
            self::VolumenTransaccional => 'Volumen transaccional',
            self::EntradaEnLinea => 'Entrada de datos en línea',
            self::EficienciaUsuarioFinal => 'Eficiencia para el usuario final',
            self::ActualizacionEnLinea => 'Actualización en línea',
            self::ComplejidadProcesamiento => 'Complejidad del procesamiento',
            self::Reusabilidad => 'Reusabilidad',
            self::FacilidadInstalacion => 'Facilidad de instalación',
            self::FacilidadOperacion => 'Facilidad de operación',
            self::MultiplesSitios => 'Múltiples sitios',
            self::FacilidadCambios => 'Facilidad de cambios',
        };
    }

    public function descripcion(): string
    {
        return match ($this) {
            self::ComunicacionDatos => 'Grado en que la aplicación se comunica con otros sistemas o módulos.',
            self::ProcesamientoDistribuido => 'Grado en que el procesamiento o los datos están distribuidos.',
            self::Rendimiento => 'Requisitos de tiempo de respuesta o rendimiento.',
            self::ConfiguracionAlta => 'Grado en que la aplicación debe soportar picos de carga.',
            self::VolumenTransaccional => 'Volumen de transacciones previsto (frecuencia e intensidad).',
            self::EntradaEnLinea => 'Porcentaje de entradas interactivas frente a procesamiento por lotes.',
            self::EficienciaUsuarioFinal => 'Ayudas al usuario (mensajes, atajos, diseño de pantallas).',
            self::ActualizacionEnLinea => 'Porcentaje de funciones actualizadas en línea frente a lotes.',
            self::ComplejidadProcesamiento => 'Complejidad de la lógica de negocio (cálculos, reglas, algoritmos).',
            self::Reusabilidad => 'Grado en que el software puede reutilizarse en otras aplicaciones.',
            self::FacilidadInstalacion => 'Facilidad de conversión e instalación en el entorno destino.',
            self::FacilidadOperacion => 'Facilidad de respaldo, recuperación y operación diaria.',
            self::MultiplesSitios => 'Número de instalaciones o sitios previstos.',
            self::FacilidadCambios => 'Facilidad para incorporar cambios (mantenimiento, parametrización).',
        };
    }

    /**
     * @return list<self>
     */
    public static function ordenadas(): array
    {
        return self::cases();
    }
}
