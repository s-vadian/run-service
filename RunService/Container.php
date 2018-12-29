<?php

namespace RunService;

class Container {

    /**
     * @var DB
     */
    private $_db;

    /**
     * @var Logger
     */
    private $_logger;

    /**
     * @var string
     */
    private $_query;

    /**
     * @var string
     */
    private $_queryIds;

    /**
     * @var string
     */
    private $_pidfile;

    /**
     * @var string
     */
    private $_logfile;

    /**
     * @var integer
     */
    private $_interval;

    /**
     * @var Tool[]
     */
    private $_toollist;

    /**
     * @var Service[]
     */
    private $_servicelist;

    /**
     * @var integer
     */
    private $_threadsCnt;

    public function __construct(string $pathToConfigureFile) {

        // подгрузка конфигурационного файла
        if (!file_exists($pathToConfigureFile)) {
            throw new \Exception("Не найден конфигурационный файл: " . $pathToConfigureFile);
        }
        $config = include $pathToConfigureFile;

        // инициализация логера
        if (!isset($config['logfile'])) {
            throw new \Exception("Не найден конфигурационный файл: " . $pathToConfigureFile);
        }
        $this->_logger = new Logger($config['logfile']);

        // инициализация подключения к бд
        if (!isset($config['db']['driver']) || !isset($config['db']['dbname']) || !isset($config['db']['host'])
            || !isset($config['db']['user']) || !isset($config['db']['password'])) {

            throw new \Exception("Не найден конфигурационный файл: " . $pathToConfigureFile);
        }
        $this->_db = new DB($config['db']['driver'], $config['db']['dbname'], $config['db']['host'],
            $config['db']['user'], $config['db']['password']);
        //$this->_db->execute('SELECT * FROM queue;');

        // запросы к БД
        if (!isset($config['query']) || !isset($config['query_ids'])) {
            throw new \Exception("Не найдены запросы: query, query_ids");
        }
        $this->_query = $config['query'];
        $this->_queryIds = $config['query_ids'];

        // путь к PID файлу
        if (!isset($config['pidfile'])) {
            throw new \Exception("Не указано расположение pidfile");
        }
        $this->_pidfile = $config['pidfile'];

        // путь к log файлу
        if (!isset($config['logfile'])) {
            throw new \Exception("Не указан путь к logfile");
        }
        $this->_logfile = $config['logfile'];

        // интервал
        if (!isset($config['interval'])) {
            throw new \Exception("Не найден interval");
        }
        $this->_interval = $config['interval'];

        // сервисы
        if (!isset($config['services']) || !is_array($config['services']) || count($config['services']) < 1) {
            throw new \Exception("Отсутвует раздел services");
        }
        $this->_servicelist = [];
        foreach ($config['services'] as $serviceName => $service) {
            if (!isset($config['tools'][$service['tool']])) {
                throw new Exeption('Отстуствеут инструмент: ' . $service['tool']);
            }
            $this->_servicelist[] = new Service(
                $service['id'],
                $serviceName,
                $service['max_ids'],
                new Tool(
                    $service['tool'],
                    $config['tools'][$service['tool']]['path']
                ),
                $service['threads_cnt'],
                $service['path']
            );
        }

        // количество потоков
        if (!isset($config['threads_cnt'])) {
            throw new \Exception("Не найден threads_cnt");
        }
        $this->_threadsCnt = $config['threads_cnt'];

        /**
         * TODO: доделать проверки
         */
    }

    /**
     * @return DB
     */
    public function getDb() {
        return $this->_db;
    }

    /**
     * @return string
     */
    public function getQuery() {
        return $this->_query;
    }

    /**
     * @return string
     */
    public function getQueryIds() {
        return $this->_queryIds;
    }

    /**
     * @return string
     */
    public function getPidfile() {
        return $this->_pidfile;
    }

    /**
     * @return string
     */
    public function getLogfile() {
        return $this->_logfile;
    }

    /**
     * @return integer
     */
    public function getInterval() {
        return $this->_interval;
    }

    /**
     * @return Tool[]
     */
    public function getToollist() {
        return $this->_toollist;
    }

    /**
     * @return Service[]
     */
    public function getServicelist() {
        return $this->_servicelist;
    }

    /**
     * @return Logger
     */
    public function getLogger() {
        return $this->_logger;
    }

    /**
     * @return integer
     */
    public function getThreadsCnt() {
        return $this->_threadsCnt;
    }
}

