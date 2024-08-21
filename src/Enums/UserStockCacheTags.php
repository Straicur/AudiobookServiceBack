<?php

namespace App\Enums;

enum UserStockCacheTags: string
{
    case USER_DELETED = 'UserDeleted';
    case USER_AUDIOBOOK_PART = 'UserAudiobookPart';
    case USER_NOTIFICATIONS = 'UserNotifications';
    case USER_AUDIOBOOKS = 'UserAudiobooks';
    case USER_CATEGORIES_TREE = 'UserCategoriesTree';
    case USER_AUDIOBOOK_DETAIL = 'UserAudiobookDetail';
    case USER_AUDIOBOOK_RATING = 'UserAudiobookRating';
    case USER_PROPOSED_AUDIOBOOKS = 'UserProposedAudiobooks';
    case AUDIOBOOK_COMMENTS = 'AudiobookComments';
}
