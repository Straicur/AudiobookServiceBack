<?php

namespace App\Enums;

enum AdminCacheKeys: string
{
    case ADMIN_CATEGORY_TREE = 'AdminCategoryTree';
    case ADMIN_CATEGORIES = 'AdminCategories';
    case ADMIN_CATEGORY = 'AdminCategory';
    case ADMIN_CATEGORY_AUDIOBOOKS = 'AdminCategoryAudiobooks';
    case ADMIN_AUDIOBOOK = 'AdminAudiobook';
    case ADMIN_AUDIOBOOK_COMMENTS = 'AdminAudiobookComments';
    case ADMIN_STATISTICS = 'AdminStatistics';
    case ADMIN_STATISTICS_AUDIOBOOKS = 'AdminStatisticsAudiobooks';
    case ADMIN_ROLES = 'AdminRoles';
    case ADMIN_TECHNICAL_BREAK = 'AdminTechnicalBreak';
}
