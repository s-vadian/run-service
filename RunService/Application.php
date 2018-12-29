<?php

// Без этой директивы PHP не будет перехватывать сигналы
declare(ticks=1);

namespace RunService;

use RunService\Threads\DataProvider;
use RunService\Threads\MyWorker;
use RunService\Threads\Work;

/**
 * Многопоточный разгрузчик очереди.
 */
class Application {

    /**
     * @var Container
     */
    private $_container;

    /**
     * @var boolean
     */
    private $stopApplication = false;

    /**
     * Конструктор класса.
     */
    public function __construct() {
        pcntl_signal(SIGTERM, array($this, "signalHandler"));
    }

    /**
    * Метод выполняет демонизацию процесса, через double fork
    * @throws \Exception
    */
    protected function demonize() {
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new \Exception('Not fork process!');
        } else if ($pid) {
            exit(0);
        }

        posix_setsid();
        chdir('/');

        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new \Exception('Not double fork process!');
        } else if ($pid) {
            $fpid = fopen($this->_container->getPidfile(), 'wb');
            fwrite($fpid, $pid);
            fclose($fpid);
            exit(0);
        }

        posix_setsid();
        chdir('/');

        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);
        $STDIN = fopen('/dev/null', 'r');

        $STDOUT = fopen($this->_container->getLogfile(), 'ab');
        if (!is_writable($this->_container->getLogfile()))
            throw new \Exception('LOG-file is not writable!');
        $STDERR = fopen($this->_container->getLogfile(), 'ab');
        if (!is_writable($this->_container->getLogfile()))
            throw new \Exception('LOG-file is not writable!');
        $this->run();
    }

    /**
    * Метод возвращает PID процесса
    * @return int PID процесса либо 0
    */
    protected function getPID() {
        if (file_exists($this->_container->getPidfile())) {
            $pid = (int) file_get_contents($this->_container->getPidfile());
            if (posix_kill($pid, SIG_DFL)) {
                return $pid;
            } else {
                //Если демон не откликается, а PID-файл существует
                unlink($this->_container->getPidfile());
                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     * Метод стартует работу и вызывает метод demonize()
     */
    public function start() {
        $this->_container->getLogger()->log('Start run-sevice', Logger::INFO);
        if (($pid = $this->getPID()) > 0) {
            echo "Process is running on PID: " . $pid . PHP_EOL;
            $this->_container->getLogger()->log("Process is running on PID: " . $pid, Logger::INFO);
        } else {
            echo "Starting..." . PHP_EOL;
            $this->demonize();
        }
    }

    /**
    * Метод останавливает демон
    */
    public function stop() {
        $this->_container->getLogger()->log('Stop run-sevice', Logger::INFO);
        if (($pid = $this->getPID()) > 0) {
            echo "Stopping ... ";
            posix_kill($pid, SIGTERM);
            unlink($this->_container->getPidfile());
            echo "OK" . PHP_EOL;
        } else {
            echo "Process not running!" . PHP_EOL;
            $this->_container->getLogger()->log('Don\'t stop. Process not running!', Logger::INFO);
        }
    }

    /**
    * Метод рестартует демон последовательно вызвав stop() и start()
    * @see start()
    * @see stop()
    */
    public function restart() {
        $this->_container->getLogger()->log('Restart run-sevice', Logger::INFO);
        $this->stop();
        $this->start();
    }

    /**
    * Метод проверяет работу демона
    */
    public function status() {
        $this->_container->getLogger()->log('Status run-sevice', Logger::INFO);
        if (($pid = $this->getPID()) > 0) {
            echo "Process is running on PID: " . $pid . PHP_EOL;
        } else {
            echo "Process not running!" . PHP_EOL;
        }
    }

    /**
    * Метод обрабатывает аргументы командной строки
    * @param array $argv - массив с аргументами коммандной строки
    */
    public function handle($argv) {

        if (!isset($argv[1]) || !in_array($argv[1], ['start', 'stop', 'restart', 'status'])) {
            echo "Unknown command!" . PHP_EOL .
                "Use: " . $argv[0] . " " . "{start|stop|restart|status}" . " " . "{path_to_configure_file}" . PHP_EOL;
            die();
        }

        if (in_array($argv[1], ['start', 'restart'])) {
            if (!isset($argv[2]) || !file_exists($argv[2])) {
                echo "Unknown command!" . PHP_EOL .
                    "Use: " . $argv[0] . " " . "{start|stop|restart|status}" . " " . "{path_to_configure_file}" . PHP_EOL;
                die();
            }
        }

        $this->_container = new Container($argv[2]);
        $this->_container->getLogger()->log('Create container', Logger::INFO);

        switch ($argv[1]) {
            case 'start':
                $this->start();
                break;
            case 'stop':
                $this->stop();
                break;
            case 'restart':
                $this->restart();
                break;
            case 'status':
                $this->status();
                break;
        }
    }

    /**
    * Основной класс демона, в котором выполняется работа.
    */
    public function run()
    {
        $servicelist = $this->_container->getServicelist();
        while (!$this->stopApplication) {
            $start = microtime(true);
            $this->_container->getLogger()->log('Start iteration', Logger::INFO);
            $tasklist = [];
            foreach ($servicelist as $service) {
                $tasklistByService = $service->getTasklist($this->_container);
                $tasklist = array_merge($tasklist, $tasklistByService);
            }

            $i = 1;
            $tasklist2 = [];
            foreach ($tasklist as $task) {
                $tasklist2[$i] = $task;
                $i++;
            }

            $threads = $this->_container->getThreadsCnt();
            $this->_container->getLogger()->log('Count threads: ' . $threads, Logger::DEBUG);

            $provider = new DataProvider();
            $provider->setTasklist($tasklist2);
            $pool = new \Pool($threads, 'RunService\Threads\MyWorker', [$provider]);

            $workers = $threads;
            for ($i = 0; $i < $workers; $i++) {
                $pool->submit(new Work());
            }

            $pool->shutdown();

            sleep($this->_container->getInterval());
            $this->_container->getLogger()->log('End iteration. Time: ' .round(microtime(true) - $start, 4).' sec.', Logger::INFO);
        }
    }

    /**
     * Обработчик сигналов
     * @param integer $signo
     */
    public function signalHandler($signo) {
        switch($signo) {
            case SIGTERM:
                // Получение сигнала завершения работы
                $this->stopApplication = true;
                break;
            default:
                // все остальные сигналы
        }
    }
}