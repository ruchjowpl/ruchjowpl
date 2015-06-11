<?php

namespace RuchJow\MessagesBundle;


class ThreadEvents {

    /**
     * Event triggered every time message has been created or changed.
     *
     * It allows to take action after user has been written to db.
     */
    const THREAD_CHANGED = 'ruch_jow_messages.thread_change';

    /**
     * Event triggered after message entity has been removed from db.
     */
    const THREAD_REMOVED = 'ruch_jow_messages.thread_removed';
}