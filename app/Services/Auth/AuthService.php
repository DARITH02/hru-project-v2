<?php

namespace App\Services\Auth;

use App\Models\Student;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function attempt(string $identifier, string $password): ?User
    {
        $user = $this->users->findByLoginIdentifier($identifier);

        if (!$user) {
            return null;
        }

        if ($this->credentialsAreValid($user, $password)) {
            return $user;
        }

        return null;
    }

    public function studentPayload(User $user): ?array
    {
        if ($user->role !== 'student') {
            return null;
        }

        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return null;
        }

        return [
            'student_code' => $student->student_code,
            'group_id' => $student->group_id,
            'id' => $student->id,
        ];
    }

    private function credentialsAreValid(User $user, string $password): bool
    {
        if ($user->role !== 'student') {
            return Hash::check($password, $user->password);
        }

        $student = Student::where('user_id', $user->id)->first();
        $allowsCodeLogin = config('auth.allow_student_code_login')
            && $student
            && strcasecmp($student->student_code, $password) === 0;

        return $student && ($allowsCodeLogin || Hash::check($password, $user->password));
    }
}
