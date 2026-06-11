<?php

namespace App\Models;

use App\Enums\FormType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
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
