<?php

return [

    'envio' => [
        'descripcion_min' => (int) env('REQ_ENVIO_DESCRIPCION_MIN', 20),
        'casos_uso_min' => (int) env('REQ_ENVIO_CASOS_USO_MIN', 0),
        'adjuntos_min' => (int) env('REQ_ENVIO_ADJUNTOS_MIN', 0),
    ],

];
