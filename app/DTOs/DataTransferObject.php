<?php

namespace App\DTOs;

use Illuminate\Foundation\Http\FormRequest;

interface DataTransferObject
{
    /**
     * @template T of FormRequest
     * @param T $request
     * @return self
     */
    public static function fromRequest($request): self;

     /**
      * @return array
      */
    public function toArray(): array;
}
