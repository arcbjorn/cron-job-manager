<?php

namespace App\Services;

use Cron\CronExpression;

class CronJobManager
{
    protected $jobs = [];

    public static function create(array $config)
    {
        $job = new CronJob($config);
        return $job;
    }

    public function register(CronJob $job)
    {
        $this->jobs[$job->getId()] = $job;
    }

    public function getJobs()
    {
        return $this->jobs;
    }

    public function run($jobId)
    {
        if (!isset($this->jobs[$jobId])) {
            throw new \Exception("Job not found: {$jobId}");
        }

        $job = $this->jobs[$jobId];

        if (!$job->isEnabled()) {
            return false;
        }

        return $job->execute();
    }

    public function getSchedule()
    {
        $schedule = [];

        foreach ($this->jobs as $id => $job) {
            $schedule[$id] = [
                'name' => $job->getName(),
                'next_run' => $job->getNextRunDate(),
                'expression' => $job->getExpression()
            ];
        }

        return $schedule;
    }

    public function getDueJobs()
    {
        $due = [];

        foreach ($this->jobs as $id => $job) {
            if ($job->isDue()) {
                $due[] = $job;
            }
        }

        return $due;
    }
}

class CronJob
{
    protected $id;
    protected $name;
    protected $command;
    protected $expression;
    protected $enabled = true;
    protected $timeout = 3600;
    protected $onFailure = null;

    public function __construct(array $config)
    {
        $this->id = $config['id'] ?? uniqid('job_');
        $this->name = $config['name'];
        $this->command = $config['command'];
        $this->expression = $config['expression'];
        $this->enabled = $config['enabled'] ?? true;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getExpression()
    {
        return $this->expression;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function enable()
    {
        $this->enabled = true;
        return $this;
    }

    public function disable()
    {
        $this->enabled = false;
        return $this;
    }

    public function onFailure(callable $callback)
    {
        $this->onFailure = $callback;
        return $this;
    }

    public function timeout($seconds)
    {
        $this->timeout = $seconds;
        return $this;
    }

    public function isDue()
    {
        $cron = new CronExpression($this->expression);
        return $cron->isDue();
    }

    public function getNextRunDate()
    {
        $cron = new CronExpression($this->expression);
        return $cron->getNextRunDate()->format('Y-m-d H:i:s');
    }

    public function execute()
    {
        $startTime = microtime(true);

        try {
            exec($this->command, $output, $returnCode);

            $duration = microtime(true) - $startTime;

            $this->log('success', implode("\n", $output), $duration);

            return [
                'success' => true,
                'output' => $output,
                'duration' => $duration
            ];

        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;

            $this->log('failed', $e->getMessage(), $duration);

            if ($this->onFailure) {
                call_user_func($this->onFailure, $e->getMessage());
            }

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'duration' => $duration
            ];
        }
    }

    protected function log($status, $output, $duration)
    {
        // Log to database or file
        $log = [
            'job_id' => $this->id,
            'name' => $this->name,
            'status' => $status,
            'output' => $output,
            'duration' => $duration,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Implementation depends on logging system
    }
}
