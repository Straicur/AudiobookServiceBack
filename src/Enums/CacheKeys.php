<?php

namespace App\Enums;

/**
 * CacheKeys
 */
enum CacheKeys: string
{
    case ADMIN_CATEGORY_TREE = "AdminCategoryTree";
    case ADMIN_CATEGORIES = "AdminCategories";
    case ADMIN_CATEGORY = "AdminCategory";
    case ADMIN_CATEGORY_AUDIOBOOKS = "AdminCategoryAudiobooks";
    case ADMIN_AUDIOBOOK = "AdminAudiobook";
    case ADMIN_AUDIOBOOK_COMMENTS = "AdminAudiobookComments";
    case ADMIN_REPORTS = "AdminReports";
    case ADMIN_REPORT = "AdminReport";
    case ADMIN_STATISTICS = "AdminStatistics";
}