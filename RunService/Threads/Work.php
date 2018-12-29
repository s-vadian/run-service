<?php

namespace RunService\Threads;

class Work extends \Threaded
{
    public function run()
    {
        do {
            $task = null;

            $provider = $this->worker->getProvider();

            // Синхронизируем получение данных
            $provider->synchronized(function($provider) use (&$task) {
               $task = $provider->getNext();
            }, $provider);

            if ($task === null) {
                continue;
            }

            $task->execute();
        }
        while ($task !== null);
    }

}

