<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait Multitenantable
{
    /**
     * Boot the multitenantable trait for a model.
     *
     * @return void
     */
    protected static function bootMultitenantable()
    {
        // Don't apply scopes when running in console (e.g., migrations, seeders)
        // unless you specifically want to. Typically, commands need full access.
        if (app()->runningInConsole()) {
            return;
        }

        static::addGlobalScope('multitenant', function (Builder $builder) {
            // Use hasUser() instead of check() to prevent infinite recursion
            // when Laravel is trying to resolve the User model for authentication.
            if (Auth::hasUser()) {
                $user = Auth::user();
                
                // Super admins see everything
                if ($user->hasRole('super-admin')) {
                    return;
                }

                $table = $builder->getModel()->getTable();

                // Logic for the 'empresas' table itself
                if ($table === 'empresas') {
                    if ($user->empresa_id) {
                        $builder->where($table . '.id', $user->empresa_id);
                    }
                    return;
                }

                // Logic for the 'sucursales' table itself
                if ($table === 'sucursales') {
                    if ($user->empresa_id) {
                        $builder->where($table . '.empresa_id', $user->empresa_id);
                    }
                    if ($user->sucursal_id) {
                        $builder->where($table . '.id', $user->sucursal_id);
                    }
                    return;
                }

                // Generic logic for other tables
                if (Schema::hasColumn($table, 'empresa_id') && $user->empresa_id) {
                    $builder->where($table . '.empresa_id', $user->empresa_id);
                }

                if (Schema::hasColumn($table, 'sucursal_id') && $user->sucursal_id) {
                    $builder->where($table . '.sucursal_id', $user->sucursal_id);
                }
            }
        });

        static::creating(function ($model) {
            if (Auth::hasUser() && !app()->runningInConsole()) {
                $user = Auth::user();
                
                if (!$user->hasRole('super-admin')) {
                    $table = $model->getTable();
                    
                    // Don't assign empresa_id or sucursal_id if creating an empresa
                    if ($table !== 'empresas') {
                        // Automatically assign empresa_id if not present
                        if (Schema::hasColumn($table, 'empresa_id') && empty($model->empresa_id) && $user->empresa_id) {
                            $model->empresa_id = $user->empresa_id;
                        }
                        
                        // Automatically assign sucursal_id if not present
                        if ($table !== 'sucursales' && Schema::hasColumn($table, 'sucursal_id') && empty($model->sucursal_id) && $user->sucursal_id) {
                            $model->sucursal_id = $user->sucursal_id;
                        }
                    }
                }
            }
        });
    }
}
