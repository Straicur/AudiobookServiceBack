<?php

namespace App\Enums\Cache;

enum UserCacheKeys: string
{
    case USER_DELETED = 'UserDeleted';
    case USER_AUDIOBOOK_PART = 'UserAudiobookPart';
    case USER_AUDIOBOOKS = 'UserAudiobooks';
    case USER_AUDIOBOOK = 'UserAudiobook';
    case USER_AUDIOBOOK_RATING = 'UserAudiobookRating';
    case USER_PROPOSED_AUDIOBOOKS = 'UserProposedAudiobooks';
    case USER_AUDIOBOOK_COMMENTS = 'UserAudiobookComments';
    case USER_NOTIFICATIONS = 'UserNotifications';
    case USER_CATEGORY_TREE = 'UserCategoriesTree';
}
