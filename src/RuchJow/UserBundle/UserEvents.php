<?php

namespace RuchJow\UserBundle;


class UserEvents {

    /**
     * Event triggered every time user has been created or changed.
     *
     * It allows to take action after user has been written to db.
     */
    const USER_CHANGED = 'ruch_jow_user.user_change';

    /**
     * Event triggered before new task is persisted (called during doctrine prePersist)
     */
    const USER_PRE_PERSIST = 'ruch_jow_user.pre_persist';

    /**
     * Event triggered before user is updated (called during doctrine preUpdate)
     */
    const USER_PRE_UPDATE = 'ruch_jow_user.pre_update';

    /**
     * Event triggered after new user is persisted (called during doctrine postPersist)
     */
    const USER_POST_PERSIST = 'ruch_jow_user.post_persist';

    /**
     * Event triggered after user is updated (called during doctrine postUpdate)
     */
    const USER_POST_UPDATE = 'ruch_jow_user.post_update';
}