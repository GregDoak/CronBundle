# CronBundle
This bundle provides cron management utilities for your Symfony Project

### Installation
Add greg-doak/cron-bundle to your composer.json
```sh
$ composer require greg-doak/cron-bundle
```

Register the bundle in config/bundles.php
```sh
return [
    // ...
    GregDoak\CronBundle\GregDoakCronBundle::class => ['all' => true],
];
```

Create the database tables
```sh
$ php bin/console doctrine:migrations:diff
$ php bin/console doctrine:migrations:migrate
```
Or
```sh
$ php bin/console doctrine:schema:update --force
```

Create your first task
```sh
$ php bin/console cron:create
```

### Configuration
Add the scheduler to your cron
```sh
*/5 * * * * php /root/to/your/application/bin/console cron:run:scheduled
```

Or alternatively to run the scheduler on page request
Configure services.yaml
```sh
parameters:
    // ...
    gregdoak.cron.run_on_request: true
```
This can have performance issues so is recommended to run on Development environments only 

### Usage
Run a single task, you will be prompted for which task to run
```sh
$ php bin/console cron:run:single
```

Kill a running task, you will be prompted for the running task
```sh
$ php bin/console cron:kill
```

Update a task
```sh
$ php bin/console cron:update
```

### Notes

There are 3 database tables created:

**cron_jobs** - A record is created whenever the scheduler or single task is run and ensures tasks cannot be ran 
simultaneously.  It also records a start and end time to measure performance of the entire job

**cron_job_tasks** - Each record consists of a single task, running tasks will be reserved by recording a cron_job_id

**cron_job_logs** - A log is created for each task to record the output, exit code, start and end times

