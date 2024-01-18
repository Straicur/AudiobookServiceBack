<?php

namespace App\Enums;

/**
 * StockCacheTags
 */
enum StockCacheTags: string
{
    case ADMIN_CATEGORY = "AdminCategory";
    case ADMIN_CATEGORY_AUDIOBOOKS = "AdminCategoryAudiobooks";
    case ADMIN_AUDIOBOOK = "AdminAudiobook";
    case ADMIN_AUDIOBOOK_COMMENTS = "AdminAudiobookComments";
    case ADMIN_STATISTICS = "AdminStatistics";
    case ADMIN_ROLES = "AdminRoles";
    case USER_AUDIOBOOK_PART = "UserAudiobookPart";
}