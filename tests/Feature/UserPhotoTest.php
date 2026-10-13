<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('public');
    }

    public function test_crear_usuario_guarda_la_foto_en_usuarios_fotos(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');

        $this->actingAs($admin)->post(route('admin.usuarios.store'), [
            'name' => 'Ana López',
            'email' => 'ana@example.com',
            'password' => 'secreto-12',
            'password_confirmation' => 'secreto-12',
            'photo' => UploadedFile::fake()->image('ana.jpg'),
        ])->assertRedirect(route('admin.usuarios.index'));

        $user = User::where('email', 'ana@example.com')->firstOrFail();

        $this->assertNotNull($user->path_photo);
        $this->assertStringStartsWith(User::PHOTO_DIRECTORY.'/', $user->path_photo);
        Storage::disk('public')->assertExists($user->path_photo);
        $this->assertStringContainsString(basename($user->path_photo), $user->photoUrl());
        $this->assertSame(
            $user->path_photo,
            User::PHOTO_DIRECTORY.'/'.basename($user->path_photo)
        );
    }

    public function test_path_photo_solo_con_nombre_resuelve_la_misma_foto(): void
    {
        $archivo = UploadedFile::fake()->image('legacy.jpg');
        $rutaCompleta = User::guardarFotoSubida($archivo);
        $user = User::factory()->create(['path_photo' => basename($rutaCompleta)]);

        $this->assertStringContainsString(basename($rutaCompleta), $user->photoUrl());
        Storage::disk('public')->assertExists($rutaCompleta);
    }

    public function test_editar_usuario_reemplaza_la_foto_anterior(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');
        $anterior = UploadedFile::fake()->image('vieja.jpg')->store(User::PHOTO_DIRECTORY, 'public');
        $user = User::factory()->create(['path_photo' => $anterior]);

        $this->actingAs($admin)->put(route('admin.usuarios.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'photo' => UploadedFile::fake()->image('nueva.png'),
        ])->assertRedirect(route('admin.usuarios.index'));

        $user->refresh();

        $this->assertNotSame($anterior, $user->path_photo);
        $this->assertStringStartsWith(User::PHOTO_DIRECTORY.'/', $user->path_photo);
        Storage::disk('public')->assertMissing($anterior);
        Storage::disk('public')->assertExists($user->path_photo);
    }

    public function test_editar_sin_foto_conserva_la_actual(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');
        $anterior = UploadedFile::fake()->image('actual.jpg')->store(User::PHOTO_DIRECTORY, 'public');
        $user = User::factory()->create(['path_photo' => $anterior]);

        $this->actingAs($admin)->put(route('admin.usuarios.update', $user), [
            'name' => 'Nombre nuevo',
            'email' => $user->email,
        ])->assertRedirect(route('admin.usuarios.index'));

        $user->refresh();

        $this->assertSame($anterior, $user->path_photo);
        Storage::disk('public')->assertExists($anterior);
    }

    public function test_una_foto_invalida_no_reemplaza_la_actual(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');
        $anterior = UploadedFile::fake()->image('actual.jpg')->store(User::PHOTO_DIRECTORY, 'public');
        $user = User::factory()->create(['path_photo' => $anterior]);

        $this->actingAs($admin)->from(route('admin.usuarios.edit', $user))->put(route('admin.usuarios.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'photo' => UploadedFile::fake()->create('notas.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('admin.usuarios.edit', $user))
            ->assertSessionHasErrors('photo');

        $user->refresh();

        $this->assertSame($anterior, $user->path_photo);
        Storage::disk('public')->assertExists($anterior);
    }

    public function test_eliminar_usuario_borra_su_foto(): void
    {
        $admin = User::factory()->create()->assignRole('Administrador');
        $ruta = UploadedFile::fake()->image('borrar.jpg')->store(User::PHOTO_DIRECTORY, 'public');
        $user = User::factory()->create(['path_photo' => $ruta]);

        $this->actingAs($admin)->deleteJson(route('admin.usuarios.destroy', $user))->assertOk();

        $this->assertModelMissing($user);
        Storage::disk('public')->assertMissing($ruta);
    }
}
