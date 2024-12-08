<?php

namespace App\Http\Controllers\Api\Auth;

use App\DTO\RegisterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Registration\RegisterRequest;
use App\Services\RegistrationService;

class RegisterController extends Controller
{
    public function __construct(
        private readonly RegistrationService $registrationService
    ) {
    }

    public function register(RegisterRequest $request): RegisterData
    {
        $registerData = RegisterData::from($request->validated());

        return $this->registrationService->registerUser($registerData);
    }

}
