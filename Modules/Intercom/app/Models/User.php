<?php

namespace Modules\Intercom\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users_can';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    
    protected $fillable = [
        'name',
        'usuario',
        'email_verified_at',
        'password',
        'remember_token',
    ];

    protected $username = 'usuario';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


   /**
     * Encuentra el usuario para el proceso de autenticación de Passport.
     * En este caso, se usa la columna "correo" en lugar de "email".
     *
     * @param  string  $identifier
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null
     */
    public function findForPassport($identifier)
    {
        return $this->where('usuario', $identifier)->first(); 
    }

    public function getAuthIdentifierName()
    {
        return 'id'; 
    }

    public function getAuthPassword()
    {
        return $this->password; 
    }

    public function getRememberToken()
    {
        return $this->remember_token;
    }

    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

}