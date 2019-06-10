<?php
namespace Core\Lib;

include_once __DIR__ . '/logger/LogLevel.php';
include_once __DIR__ . '/logger/LoggerInterface.php';
include_once __DIR__ . '/logger/AbstractLogger.php';

use Psr\Log\LogLevel;
use Psr\Log\AbstractLogger;

/**
 * Finally, a light, permissions-checking logging class.
 *
 * Originally written for use with wpSearch
 *
 * Usage:
 * $log = new Katzgrau\KLogger\Logger('/var/log/', Psr\Log\LogLevel::INFO);
 * $log->info('Returned a million search results'); //Prints to the log file
 * $log->error('Oh dear.'); //Prints to the log file
 * $log->debug('x = 5'); //Prints nothing due to current severity threshhold
 *
 * @author  Kenny Katzgrau <katzgrau@gmail.com>
 * @since   July 26, 2008
 * @link    https://github.com/katzgrau/KLogger
 * @version 1.0.0
 */

/**
 * Class documentation
 */
class Logger extends AbstractLogger {
    
    /**
     * KLogger options
     *  Anything options not considered 'core' to the logging library should be
     *  settable view the third parameter in the constructor
     *
     *  Core options include the log file path and the log threshold
     *
     * @var array
     */
    
    protected $options = [
        'extension'      => 'txt',
        'dateFormat'     => 'Y-m-d G:i:s.u',
        'filename'       => false,
        'flushFrequency' => false,
        'prefix'         => 'log_',
        'logFormat'      => false,
        'appendContext'  => true,
    ];

    /**
     * Path to the log file
     * @var string
     */
    private $logFilePath;

    /**
     * Current minimum logging threshold
     * @var integer
     */
    protected $logLevelThreshold = LogLevel::DEBUG;

    /**
     * The number of lines logged in this instance's lifetime
     * @var int
     */
    private $logLineCount = 0;

    /**
     * Log Levels
     * @var array
     */
    protected $logLevels = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT     => 1,
        LogLevel::CRITICAL  => 2,
        LogLevel::ERROR     => 3,
        LogLevel::WARNING   => 4,
        LogLevel::NOTICE    => 5,
        LogLevel::INFO      => 6,
        LogLevel::DEBUG     => 7
    ];

    /**
     * This holds the file handle for this instance's log file
     * @var resource
     */
    private $fileHandle;

    /**
     * This holds the last line logged to the logger
     *  Used for unit tests
     * @var string
     */
    private $lastLine = '';

    /**
     * Octal notation for default permissions of the log file
     * @var integer
     */
    private $defaultPermissions = 0755;

    /**
     * Class constructor
     *
     * @param string $logDirectory      File path to the logging directory
     * @param string $logLevelThreshold The LogLevel Threshold
     * @param array  $options
     *
     * @internal param string $logFilePrefix The prefix for the log file name
     * @internal param string $logFileExt The extension for the log file
     */
    public function __construct(string $logDirectory, string $logLevelThreshold = LogLevel::DEBUG, array $options = []) {
        $this->logLevelThreshold = $logLevelThreshold;
        $this->options = \array_merge($this->options, $options);

        $logDirectory = \rtrim($logDirectory, \DIRECTORY_SEPARATOR);
        if ( ! \file_exists($logDirectory)) {
            \mkdir($logDirectory, $this->defaultPermissions, true);
        }

        if (0 === \strpos($logDirectory, 'php://')) {
            $this->setLogToStdOut($logDirectory);
            $this->setFileHandle('w+');
        } 
        else {
            $this->setLogFilePath($logDirectory);
            if (\file_exists($this->logFilePath) && ! \is_writable($this->logFilePath)) {
                throw new \RuntimeException('The file could not be written to. Check that appropriate permissions have been set.');
            }
            $this->setFileHandle('a');
        }

        if (!$this->fileHandle) {
            throw new \RuntimeException('The file could not be opened. Check permissions.');
        }
    }

    /**
     * Class destructor
     */
    public function __destruct() {
        if ($this->fileHandle) {
            \fclose($this->fileHandle);
        }
    }
    
    /**
     * @param string $stdOutPath
     */
    public function setLogToStdOut(string $stdOutPath) : void {
        $this->logFilePath = $stdOutPath;
    }

    /**
     * @param string $logDirectory
     */
    public function setLogFilePath(string $logDirectory) : void {
        if ($this->options['filename']) {
            if (\strpos($this->options['filename'], '.log') !== false || \strpos($this->options['filename'], '.txt') !== false) {
                $this->logFilePath = $logDirectory . \DIRECTORY_SEPARATOR . $this->options['filename'];
            }
            else {
                $this->logFilePath = $logDirectory . \DIRECTORY_SEPARATOR . $this->options['filename'] . '.' . $this->options['extension'];
            }
        } else {
            $this->logFilePath = $logDirectory . \DIRECTORY_SEPARATOR . $this->options['prefix'] . \date('Y-m-d') . '.' . $this->options['extension'];
        }
    }

    /**
     * @param $writeMode
     *
     * @internal param resource $fileHandle
     */
    public function setFileHandle($writeMode) : void {
        $this->fileHandle = \fopen($this->logFilePath, $writeMode);
    }


    
    /**
     * Sets the date format used by all instances of KLogger
     *
     * @param string $dateFormat Valid format string for date()
     */
    public function setDateFormat(string $dateFormat) : void {
        $this->options['dateFormat'] = $dateFormat;
    }

    /**
     * Sets the Log Level Threshold
     *
     * @param string $logLevelThreshold The log level threshold
     */
    public function setLogLevelThreshold(string $logLevelThreshold) : void {
        $this->logLevelThreshold = $logLevelThreshold;
    }

    /**
    * Returns the calling function through a backtrace
    */
    private function get_calling_function() : string {
        // a funciton x has called a function y which called this
        // see stackoverflow.com/questions/190421
        $caller = \debug_backtrace();
        $caller = $caller[4];
        $r = $caller['function'] . '('.($caller['line']??''). ')';
        if (isset($caller['class'])) {
            $r = $caller['class'].'.'.$r;
        }
        //if (isset($caller['object'])) {
        //    $r .= ' (' . get_class($caller['object']) . ')';
        //}
        return $r;
    }


    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log(string $level, string $message, array $context = []) : bool {
        if ($this->logLevels[$this->logLevelThreshold] < $this->logLevels[$level]) {
            return false;
        }
        $message = $this->formatMessage($level, $message, $context);
        return $this->write($message);
    }

    /**
     * Writes a line to the log without prepending a status or timestamp
     *
     * @param string $message Line to write to the log
     * @return void
     */
    public function write(string $message) : bool {
        if (null === $this->fileHandle) {
            return false;
        }
        if (false === \fwrite($this->fileHandle, $message)) {
            throw new \RuntimeException('The file could not be written to. Check that appropriate permissions have been set.');
        }
        $this->lastLine = \trim($message);
        ++$this->logLineCount;
        if ($this->options['flushFrequency'] && 0 === $this->logLineCount % $this->options['flushFrequency']) {
            \fflush($this->fileHandle);
        }
        return true;
    }

    /**
     * Get the file path that the log is currently writing to
     *
     * @return string
     */
    public function getLogFilePath() : string {
        return $this->logFilePath;
    }

    /**
     * Get the last line logged to the log file
     *
     * @return string
     */
    public function getLastLogLine() : string {
        return $this->lastLine;
    }

    /**
     * Formats the message for logging.
     *
     * @param  string $level   The Log Level of the message
     * @param  string $message The message to log
     * @param  array  $context The context
     * @return string
     */
    protected function formatMessage(string $level, string $message, array $context) : string {
        if ($this->options['logFormat']) {
            $parts = [
                'date'          => $this->getTimestamp(),
                'level'         => \strtoupper($level),
                'level-padding' => \str_repeat(' ', 9 - \strlen($level)),
                'priority'      => $this->logLevels[$level],
                'message'       => $message,
                'context'       => \json_encode($context),
            ];
            $message = $this->options['logFormat'];
            foreach ($parts as $part => $value) {
                \error_log($value);
                $message = \str_replace('{' . $part . '}', $value, $message);
            }
        } 
        else {
            $message = "[{$this->getTimestamp()}] [{$level}] {$this->get_calling_function()}\t{$message}";
        }

        if ($this->options['appendContext'] && ! empty($context)) {
            $message .= \PHP_EOL . $this->indent($this->contextToString($context));
        }

        return $message . \PHP_EOL;

    }

    /**
     * Gets the correctly formatted Date/Time for the log entry.
     *
     * PHP DateTime is dump, and you have to resort to trickery to get microseconds
     * to work correctly, so here it is.
     *
     * @return string
     */
    private function getTimestamp() : string {
        $originalTime = \microtime(true);
        $micro = \sprintf("%06d", ($originalTime - \floor($originalTime)) * 1000000);
        $date = new \DateTime(\date('Y-m-d H:i:s.'.$micro, $originalTime));

        return $date->format($this->options['dateFormat']);
    }

    /**
     * Takes the given context and coverts it to a string.
     *
     * @param  array $context The Context
     * @return string
     */
    protected function contextToString($context) {
        $export = '';
        foreach ($context as $key => $value) {
            $export .= "{$key}: ";
            $export .= \preg_replace([
                '/=>\s+([a-zA-Z])/im',
                '/array\(\s+\)/im',
                '/^  |\G  /m'
            ], [
                '=> $1',
                'array()',
                '    '
            ], \str_replace('array (', 'array(', \var_export($value, true)));
            $export .= \PHP_EOL;
        }
        return \str_replace(['\\\\', '\\\''], ['\\', '\''], \rtrim($export));
    }

    /**
     * Indents the given string with the given indent.
     *
     * @param  string $string The string to indent
     * @param  string $indent What to use as the indent.
     * @return string
     */
    protected function indent(string $string, string $indent = '    ') : string {
        return $indent . \str_replace("\n", "\n" . $indent, $string);
    }
    
}