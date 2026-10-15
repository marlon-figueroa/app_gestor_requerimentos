<x-form.input name="codigo" label="Código" :value="$casoUso->codigo" required maxlength="20" col="col-md-3" />
<x-form.input name="nombre" label="Nombre" :value="$casoUso->nombre" required autofocus maxlength="255" col="col-md-7" />
<x-form.input name="orden" label="Orden" type="number" :value="$casoUso->orden" min="0" col="col-md-2" />

<x-form.input name="pantalla" label="Pantalla o parte del sistema" :value="$casoUso->pantalla" maxlength="255"
              help="Pantalla, módulo o sección donde se usa esta funcionalidad." />
<x-form.input name="actor" label="Actor" :value="$casoUso->actor" maxlength="255" help="Quién ejecuta el caso de uso." />

<x-form.textarea name="descripcion" label="Descripción" :value="$casoUso->descripcion" rows="3" />
<x-form.textarea name="precondiciones" label="Precondiciones" :value="$casoUso->precondiciones" rows="2" col="col-md-6" />
<x-form.textarea name="postcondiciones" label="Postcondiciones" :value="$casoUso->postcondiciones" rows="2" col="col-md-6" />
<x-form.textarea name="flujo_principal" label="Flujo principal" :value="$casoUso->flujo_principal" rows="6"
                 help="Un paso por línea, por ejemplo: 1. El usuario abre la pantalla..." />
