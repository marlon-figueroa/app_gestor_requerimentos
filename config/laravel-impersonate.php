<?php

return [

    /**
     * The session key used to store the original user id.
     */
    'session_key' => 'impersonated_by',

    /**
     * The session key used to stored the original user guard.
     */
    'session_guard' => 'impersonator_guard',

    /**
     * The session key used to stored what guard is impersonator using.
     */
    'session_guard_using' => 'impersonator_guard_using',

    /**
     * The default impersonator guard used.
     */
    'default_impersonator_guard' => 'web',

    /**
     * Nombre de ruta al entrar como otro usuario.
     * Use 'back' para volver a la página anterior.
     */
    'take_redirect_to' => 'home',

    /**
     * Nombre de ruta al volver a la cuenta del administrador.
     * Use 'back' para volver a la página anterior.
     */
    'leave_redirect_to' => 'admin.usuarios.index',

];
