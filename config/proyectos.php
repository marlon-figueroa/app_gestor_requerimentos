<?php

return [

    'cierre' => [
        'exigir_evaluacion_baseline' => (bool) env('PROYECTO_EXIGIR_BASELINE', false),
        'bloquear_si_no_viable' => (bool) env('PROYECTO_BLOQUEAR_NO_VIABLE', false),
    ],

    'scope_responsable' => (bool) env('PROYECTO_SCOPE_RESPONSABLE', false),

];
