<?php

namespace App\DTOs\Survey;

enum QuestionType: string
{
    case Radio = 'radio';
    case YesNo = 'yesno';
}
