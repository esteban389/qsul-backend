<?php

namespace App\DTOs\Auth;

enum QuestionType: string
{
    case Radio = 'radio';
    case YesNo = 'yesno';
}
