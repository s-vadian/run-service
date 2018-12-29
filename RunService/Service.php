<?php

namespace RunService;

class Service {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $maxIds;

    /**
     * @var Tool
     */
    private $tool;

    /**
     * @var integer
     */
    private $threadsCnt;

    /**
     * @var string
     */
    private $dir;

    /**
     * @var string
     */
    private $template = '${SERVICE_ID}';

    /**
     * @param \RunService\Container $container
     * @param integer $id
     * @param string $name
     * @param integer $maxIds
     * @param \RunService\Tool $tool
     * @param integer $threadsCnt
     * @param string $dir
     */
    public function __construct($id, $name, $maxIds, Tool $tool, $threadsCnt, $dir) {
        $this->id = $id;
        $this->name = $name;
        $this->maxIds = $maxIds;
        $this->tool = $tool;
        $this->threadsCnt = $threadsCnt;
        $this->dir = $dir;
    }

    /**
     * @return Tool
     */
    public function getTool()
    {
        return $this->tool;
    }

    /**
     * Задачи по сервису
     * @return Task[] | []
     */
    public function getTasklist(Container $container)
    {
        $result = [];
        $sql = str_replace($this->template, $this->id, $container->getQueryIds());
        $taskRecords = $container->getDb()->execute($sql);

        $taskRecordsByArgs = [];
        $i = 1; $j = 1;
        foreach ($taskRecords as $taskRecord) {
            $taskRecordsByArgs[$j][] = $taskRecord;
            if ($i == $this->maxIds) {$i = 0; $j++;}
            $i++;
        }

        foreach ($taskRecordsByArgs as $arguments) {
            $task = new Task($this, $container->getLogger());
            foreach ($arguments as $argument) {
                $task->addArg($argument['id']);
            }
            $result[] = $task;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        /**
         * TODO: изменить path на dir!
         */
        return $this->dir;
    }

    /**
     * @return string
     */
    public function getThreadsCnt()
    {
        return $this->threadsCnt;
    }

    /**
     * @return string
     */
    public function getContainer()
    {
        return $this->container;
    }
}