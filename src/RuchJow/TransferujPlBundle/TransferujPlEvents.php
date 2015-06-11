<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 10/14/14
 * Time: 11:17 AM
 */

namespace RuchJow\TransferujPlBundle;


class TransferujPlEvents
{
    const PAYMENT_NEW = 'ruch_jow_transferuj_pl.payment_new';
    const PAYMENT_UPDATED = 'ruch_jow_transferuj_pl.payment_updated';
    const PAYMENT_CONFIRMED = 'ruch_jow_transferuj_pl.payment_confirmed';
    const PAYMENT_CONFIRMED_UPDATE = 'ruch_jow_transferuj_pl.payment_confirmed_updated';
}