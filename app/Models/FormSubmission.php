<?php

namespace App\Models;

use App\Enums\FormType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'form_type',
        'data',
        'ip_address',
        'user_agent',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'form_type' => FormType::class,
            'data' => 'array',
            'is_read' => 'boolean',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizedData(): array
    {
        $data = $this->data;

        if (is_array($data)) {
            return $data;
        }

        if (is_string($data) && $data !== '') {
            $decoded = json_decode($data, true);

            return is_array($decoded) ? $decoded : ['value' => $data];
        }

        return [];
    }
}
