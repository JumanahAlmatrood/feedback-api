<?php

namespace App\Enums;

enum FeedbackCategory: string
{
    case General = 'general';
    case Support = 'support';
    case Product = 'product';
    case Bug = 'bug';
}
