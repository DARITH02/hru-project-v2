<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    public function findByLoginIdentifier(string $identifier): ?User
    {
        $phoneCandidates = $this->phoneLoginCandidates($identifier);

        return User::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->when($phoneCandidates !== [], function ($query) use ($phoneCandidates) {
                $query->orWhereIn($this->normalizedPhoneColumn(), $phoneCandidates);
            })
            ->orWhereHas('student', function ($query) use ($identifier) {
                $query->where('student_code', $identifier);
            })
            ->first();
    }

    private function phoneLoginCandidates(string $value): array
    {
        $digits = preg_replace('/\D+/', '', $value);

        if (!$digits) {
            return [];
        }

        $candidates = [$digits];

        if (str_starts_with($digits, '0')) {
            $candidates[] = '855' . substr($digits, 1);
        } elseif (str_starts_with($digits, '855')) {
            $candidates[] = '0' . substr($digits, 3);
        }

        return array_values(array_unique($candidates));
    }

    private function normalizedPhoneColumn(): Expression
    {
        return DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '+', ''), '-', ''), '(', ''), ')', '')");
    }
}
