<?php

namespace Modules\Intercom\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IntercomOAuthToken extends Model
{
    use HasFactory;

    protected $table = 'intercom_oauth_access_tokens';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['client_id', 'access_token', 'expires_in'];


}
