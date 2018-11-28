<?php
declare(strict_types=1);


namespace App\Domains\Collect\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Domains\Collect\Models\PreserveResponse
 *
 * @property int $id
 * @property string|null $body
 * @property string $status
 * @property array $headers
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domains\Collect\Models\PreserveRequest $preserveRequest
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveResponse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveResponse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveResponse query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveResponse whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveResponse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveResponse whereHeaders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveResponse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveResponse whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveResponse whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PreserveResponse extends Model
{
    protected $fillable = [
        'body',
        'status',
        'headers',
    ];

    protected $casts = [
        'headers' => 'array',
    ];

    /**
     * @return HasOne
     */
    public function preserveRequest(): HasOne
    {
        return $this->hasOne(PreserveRequest::class);
    }
}