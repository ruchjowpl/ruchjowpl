<?php

namespace RuchJow\StatisticsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class RuchJowStatisticsBundle extends Bundle
{

    const STAT_SUPPORTING_USERS           = 'statistics.supporting_users';
    const STAT_SUPPORTING_USERS_LOCAL_GOV = 'statistics.supporting_users_local_gov';

    const STAT_DONATIONS           = 'statistics.donations';
    const STAT_DONATIONS_COUNT     = 'statistics.donations_count';
    const STAT_DONATIONS_AVG       = 'statistics.donations_avg';
    const STAT_POINTS_TOTAL        = 'statistics.points.total';
    const STAT_POINTS_USER         = 'statistics.points.user';
    const STAT_POINTS_COMMUNE      = 'statistics.points.commune';
    const STAT_POINTS_DISTRICT     = 'statistics.points.district';
    const STAT_POINTS_REGION       = 'statistics.points.region';
    const STAT_POINTS_ORGANISATION = 'statistics.points.organisation';
}
