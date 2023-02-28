<?php

declare(strict_types=1);
/**
 * Job
 */

namespace App\Job;

use Hyperf\AsyncQueue\Job as AsyncQueueJob;

abstract class Job extends AsyncQueueJob
{

}
