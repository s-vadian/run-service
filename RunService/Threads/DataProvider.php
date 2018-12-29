<?php

namespace RunService\Threads;

/**
 * Провайдер данных для потоков.
 */
class DataProvider extends \Threaded
{
    /**
     * Всего задач
     * @var int
     */
    private $total = 0;
    /**
     * Обработано задач
     * @var int
     */
    private $processed = 0;
    /**
     * Список задач
     * @var array
     */
    private $tasklist = 0;

    /**
     * Отдает следующюу задачу на исполнение.
     * @return \RunService\Task
     */
    public function getNext()
    {
        if ($this->processed === $this->total) {
            return null;
        }
        $this->processed++;
        return $this->tasklist[$this->processed];
    }

    /**
     * @param array $value
     */
    public function setTasklist($tasklist)
    {
        $this->tasklist = $tasklist;
        $this->total = count($tasklist);
    }
}