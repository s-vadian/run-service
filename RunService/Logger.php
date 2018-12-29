<?php

namespace RunService;

class Logger {

    const ERROR = 1; // Уровень журналирования: только ошибки
    const INFO = 2; // Уровень журналирования: ошибки и дополнительная информация
    const DEBUG = 3; // Уровень журналирования: ошибки, дополнительная и отладочная информация

    /**
     * @var array
     */
    private $levelMap = [
        self::ERROR => 'ERROR',
        self::INFO => 'INFO',
        self::DEBUG => 'DEBUG'
    ];

    /**
     * @var string
     */
    private $defaultLogfile;

    /**
     * @param string $defaultLogfile - логфайл по умолчанию
     */
    public function __construct(string $defaultLogfile)
    {
        /**
         * TODO: проверить наличие файла, каталога на существование, при их
         * отстуствии создать.
         */
        $this->defaultLogfile = $defaultLogfile;
    }

    /**
     * @param string $message - сообщение
     * @param integer $level - уровень журналирования [ERROR|INFO|DEBUG]
     * @param integer $logfile - логфайл
     */
    public function log($message, $level, $logfile = null)
    {
        if ($logfile === null) {
            $logfile = $this->defaultLogfile;
        }
        /**
         * TODO:
         * - Создавать каталог и файл перед логированием;
         * - Учитывать уровни логирования;
         */
        error_log(date('Y-m-d H:i:sO') . ' | ' . $this->levelMap[$level] . ': ' .  $message . "\n", 3, $logfile);
    }
}
