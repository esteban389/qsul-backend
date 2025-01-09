<?php

namespace App\DTOs\Survey;

enum ObservationType: string
{
    case Positive = 'positive';
    case Negative = 'negative';
    case Neutral = 'neutral';
}
