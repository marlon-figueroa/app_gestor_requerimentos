<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Productividad para estimar esfuerzo
    |--------------------------------------------------------------------------
    |
    | Los PF ajustados ya incorporan el VAF; las horas se calculan sobre
    | total_ajustado × horas_por_pf (referencia conservadora IFPUG).
    |
    */
    'productividad' => [
        'horas_por_pf' => (float) env('PF_HORAS_POR_PF', 8),
        'horas_jornada' => (float) env('PF_HORAS_JORNADA', 8),
        'dias_semana' => 5,
        'recursos' => max(1, (int) env('PF_RECURSOS', 1)),
    ],

    'costos' => [
        'tarifa_hora' => (float) env('PF_TARIFA_HORA', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Umbrales del diagnóstico de viabilidad
    |--------------------------------------------------------------------------
    */
    'viabilidad' => [
        'pf_elevado' => 250,
        'vaf_alto' => 1.15,
        'suma_influencia_alta' => 55,
        'ratio_complejidad_alta' => 0.45,
        'margen_plazo' => 1.15,
        'puntaje_alta' => 75,
        'puntaje_media' => 50,
    ],
];
