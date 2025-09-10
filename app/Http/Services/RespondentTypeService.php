<?php

namespace App\Http\Services;

use App\DTOs\Survey\CreateRespondentTypeRequestDto;
use App\Models\RespondentType;

readonly class RespondentTypeService
{

    public function getRespondentTypes()
    {
        return RespondentType::all();
    }

    public function getRespondentTypeById(int $id)
    {
        return RespondentType::query()->findOrFail($id);
    }

    public function createRespondentType(CreateRespondentTypeRequestDto $data)
    {
        return RespondentType::query()->create($data->toArray());
    }

    public function deleteRespondentType(RespondentType $respondentType)
    {
        $respondentType->delete();
    }
}
