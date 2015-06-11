<?php

namespace RuchJow\MessagesBundle;


class MessageEvents {

    /**
     * Event triggered every time message has been created or changed.
     *
     * It allows to take action after user has been written to db.
     */
    const MESSAGE_CHANGED = 'ruch_jow_messages.message_change';

    /**
     * Event triggered after message entity has been removed from db.
     */
    const MESSAGE_REMOVED = 'ruch_jow_messages.message_removed';
}