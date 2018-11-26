<?php
declare(strict_types=1);


namespace App\Domains\Collect\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string          body
 * @property string          status
 * @property array           headers
 * @property PreserveRequest request
 */
class PreserveResult extends Model
{
    protected $fillable = [
        'body',
        'status',
        'headers',
    ];

    protected $casts = [
        'headers' => 'array',
    ];

    public function request()
    {
        $this->hasOne(PreserveRequest::class);
    }
}