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
}