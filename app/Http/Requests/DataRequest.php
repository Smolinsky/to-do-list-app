<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\WithData;

abstract class DataRequest extends FormRequest
{
    use WithData {
        getData as private getSpatieData;
    }

    abstract protected function dataClass(): string;

    public function getDTO(): BaseData
    {
        return $this->getData();
    }

    public function getData(): BaseData
    {
        if ($this->dtoContext() === []) {
            return $this->getSpatieData();
        }

        $dataClass = $this->dataClass();

        return $dataClass::from(array_merge(
            $this->validated(),
            $this->dtoContext()
        ));
    }

    protected function dtoContext(): array
    {
        return [];
    }

    /**
     * @throws AuthenticationException
     */
    protected function authenticatedUser(): User
    {
        $user = $this->user();

        if (! $user instanceof User) {
            throw new AuthenticationException();
        }

        return $user;
    }
}
