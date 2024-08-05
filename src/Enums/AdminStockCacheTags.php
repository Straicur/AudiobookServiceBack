<?php

namespace App\Enums;

enum AdminStockCacheTags: string
{
    case ADMIN_CATEGORY = 'AdminCategory';
    case ADMIN_CATEGORY_AUDIOBOOKS = 'AdminCategoryAudiobooks';
    case ADMIN_AUDIOBOOK = 'AdminAudiobook';
    case ADMIN_STATISTICS = 'AdminStatistics';
    case ADMIN_ROLES = 'AdminRoles';
    case ADMIN_TECHNICAL_BREAK = 'AdminTechnicalBreak';
}
