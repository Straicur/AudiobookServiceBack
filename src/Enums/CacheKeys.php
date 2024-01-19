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
    case ADMIN_STATISTICS = "AdminStatistics";
    case ADMIN_STATISTICS_AUDIOBOOKS = "AdminStatisticsAudiobooks";
    case ADMIN_ROLES = "AdminRoles";
    case USER_AUDIOBOOK_PART = "UserAudiobookPart";
    case USER_AUDIOBOOKS = "UserAudiobooks";
    case USER_AUDIOBOOK = "UserAudiobook";
    case USER_AUDIOBOOK_RATING = "UserAudiobookRating";
    case USER_PROPOSED_AUDIOBOOKS = "UserProposedAudiobooks";
    case USER_AUDIOBOOK_COMMENTS = "UserAudiobookComments";
    case USER_NOTIFICATIONS = "UserNotifications";
}