<?php

namespace App\Models;

use App\Enums\Actor\GenderEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $address
 * @property GenderEnum $gender
 * @property string $description
 * @property int $height
 * @property int $weight
 * @property int $age
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Actor extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'description',
        'first_name',
        'last_name',
        'address',
        'height',
        'weight',
        'gender',
        'age',
    ];

    protected $casts = [
        'gender' => GenderEnum::class
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
