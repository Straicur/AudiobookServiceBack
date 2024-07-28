<?php

namespace App\Enums;

enum StockCacheTags: string
{
    case ADMIN_CATEGORY = 'AdminCategory';
    case ADMIN_CATEGORY_AUDIOBOOKS = 'AdminCategoryAudiobooks';
    case ADMIN_AUDIOBOOK = 'AdminAudiobook';
    case ADMIN_STATISTICS = 'AdminStatistics';
    case ADMIN_ROLES = 'AdminRoles';
    case ADMIN_TECHNICAL_BREAK = 'AdminTechnicalBreak';
    case USER_DELETED = 'UserDeleted';
    case USER_AUDIOBOOK_PART = 'UserAudiobookPart';
    case USER_NOTIFICATIONS = 'UserNotifications';
    case USER_AUDIOBOOKS = 'UserAudiobooks';
    case USER_AUDIOBOOK_DETAIL = 'UserAudiobookDetail';
    case USER_AUDIOBOOK_RATING = 'UserAudiobookRating';
    case USER_PROPOSED_AUDIOBOOKS = 'UserProposedAudiobooks';
    case AUDIOBOOK_COMMENTS = 'AudiobookComments';
}
