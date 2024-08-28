<?php

namespace App\Logger;

use App\DataBase\LogDataBase;
use App\Traits\SingletonTrait;
use App\Config;

class Logger
{
    use SingletonTrait;

    private Config $config;
    private string $logFile;
    private LogDataBase $logDataBase;
    private string $prefix = '';
    private int $appId = 0;
    public bool $echoLog = false;

    protected function init(): void
    {
        $this->config = Config::instance();
        $this->unlinkExtraLimit();
        $this->logFile = sprintf($this->config->conf('log_file'), date('Y-m-d'));
        $this->appId = $this->config->conf('app_id');
    }

    public function log(string $log, int $level = 0): void
    {
        $this->prefix = $level ? $this->config->level_names[$level] : '';
        if ($this->echoLog) {
            $this->logConsole($log);
        }
        $this->logFile($log);
//        return; // test
        if (isset($this->logDataBase) && in_array($level, Config::LOG_DB)) {
            $this->logDataBase->log($log, $level);
        }
        $this->prefix = '';
    }

    public function setLogDataBase(int $appId): void
    {
        try {
            $this->logDataBase = new LogDataBase($appId);
        } catch (\Exception $e) {
            unset($this->logDataBase);
            $this->log("!!!Error connect log DB\n" . $e->getMessage(), Config::ERROR);
        }
    }

    public function logConsole(string $log): void
    {
        echo $this->prefix . "$log\n";
    }

    public function logFile(string $log): void
    {
        file_put_contents(
            $this->logFile,
            sprintf("%s %s %s\r\n", date('H:i:s'), $this->prefix, $log),
            FILE_APPEND
        );
    }

    private function unlinkExtraLimit(): void
    {
        $logLimit = $this->config->conf('log_limit');
        $logFiles = glob($this->config->conf('log_dir') . 'log*');
        if (!$logFiles || count($logFiles) < $logLimit)
            return;
        for ($i = count($logFiles) - $logLimit; $i > 0; $i--) {
            unlink($logFiles[$i]);
        }
    }
}