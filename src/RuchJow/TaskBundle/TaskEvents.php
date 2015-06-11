<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 9/22/14
 * Time: 4:41 PM
 */

namespace RuchJow\TaskBundle;


class TaskEvents {

    /**
     * Event triggered every time task has been created or changed.
     *
     * It allows to take action after task is written to db.
     */
    const TASK_CHANGE = 'ruch_jow_task.task_change';

    /**
     * Event triggered before new task is persisted to db (called during doctrine prePersist)
     */
    const TASK_PRE_PERSIST = 'ruch_jow_task.pre_persist';

    /**
     * Event triggered before task is updated in db (called during doctrine preUpdate)
     */
    const TASK_PRE_UPDATE = 'ruch_jow_task.pre_update';
}