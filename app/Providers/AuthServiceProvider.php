<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Facades\Hash;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Aquí van tus políticas (si tienes).
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot()
    {
        $this->registerPolicies();

        // 1️⃣ Registramos el driver 'legacy'
        Auth::provider('legacy', function ($app, array $config) {
            return new class ($app['hash'], $config['model']) extends EloquentUserProvider {
                public function validateCredentials($user, array $credentials)
                {
                    $plain = $credentials['password'];       // lo que ingresó el usuario
                    $stored = $user->getAuthPassword();      // lo que está en BD

                    // Si tu hash legacy era MD5:
                    if (md5($plain) === $stored) {
                        // (opcional) re-hashea a bcrypt para futuras veces:
                        $user->password = Hash::make($plain);
                        $user->save();
                        return true;
                    }

                    return false;
                }
            };
        });
    }
}
