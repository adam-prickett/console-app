<?php
/**
 * Axo - Console Micro-Framework
 *
 * @author Ampersa Ltd <contact@ampersa.co.uk>
 * @license MIT
 * @copyright © Copyright Ampersa Ltd 2017.
 */

namespace System\Console;

trait ConsoleProgressBar
{
    protected $progressBarStarted = false;
    protected $progressBarActive = false;
    protected $progressBarLimit = 100;
    protected $progressBarCurrent = 0;
    protected $progressBarLength = 25;
    protected $progressBarCurrentPercent = 0;
    protected $progressBarTitle;
    protected $progressBarCompleteMessage;
    protected $progressBarStartTime;

    /**
     * Output and begin a progress bar
     * @param  int  $limit
     * @param  int  $start
     * @return void
     */
    public function progressBar(int $limit = 100, int $start = 0)
    {
        // Check that a progress bar isn't already running (we can only cope with 1)
        if ($this->progressBarStarted) {
            return;
        }

        // Reset figures and config
        $this->progressBarStarted = true;
        $this->progressBarActive = false;
        $this->progressBarLimit = $limit;
        $this->progressBarCurrent = $start;
        $this->progressBarCurrentPercent = floor($start / $limit);
        $this->progressBarTitle = null;
        $this->progressBarCompleteMessage = null;
        $this->progressBarStartTime = null;

        return $this;
    }

    /**
     * Start the progress bar
     * @return void
     */
    public function startProgressBar()
    {
        $this->progressBarStartTime = microtime(true);
        $this->drawProgressBar();
    }

    /**
     * Set the title to display above the progress bar
     * @param  string $title
     * @return self
     */
    public function setProgressBarTitle(string $title)
    {
        $this->progressBarTitle = $title;

        return $this;
    }

    /**
     * Set the message to display once the progress bar has completed
     * @param  string $message
     * @return self
     */
    public function setProgressBarCompleteMessage(string $message)
    {
        $this->progressBarCompleteMessage = $message;

        return $this;
    }

    /**
     * Advance the progess bar by 1 (or provided) unit(s)
     * @param  integer $num
     * @return void
     */
    public function advanceProgressBar(int $num = 1)
    {
        // Check that a progress bar has actually been started
        if (!$this->progressBarStarted) {
            return;
        }

        // Don't let the progress bar overflow
        if ($this->progressBarCurrent+$num > $this->progressBarLimit) {
            return;
        }

        $this->progressBarCurrent = $this->progressBarCurrent + $num;

        $this->drawProgressBar();
    }

    /**
     * Set the progress bar to $num
     * @param integer $num
     */
    public function setProgressBar(int $num)
    {
        // Check that a progress bar has actually been started
        if (!$this->progressBarStarted) {
            return;
        }

        // Don't let the progress bar overflow
        if ($num > $this->progressBarLimit) {
            return;
        }

        $this->progressBarCurrent = $num;

        $this->drawProgressBar();
    }

    /**
     * Complete a progress bar
     * @return void
     */
    public function completeProgressBar()
    {
        if (!empty($this->progressBarCompleteMessage)) {
            printf("%s", $this->progressBarCompleteMessage);
        }

        printf(PHP_EOL);
        $this->progressBarStarted = false;
        $this->progressBarActive = false;
    }

    /**
     * Draw the progress bar
     * @return void
     */
    private function drawProgressBar()
    {
        $percentComplete = ceil(($this->progressBarCurrent/$this->progressBarLimit) * 100);
        $percentPerBlock = 100 / $this->progressBarLength;
        $numberOfBlocks = ceil($percentComplete / $percentPerBlock);

        $this->progressBarCurrentPercent = $percentComplete;

        // Firstly, calculate the time that it's taken to get to this point to subtracting
        // the current time from the start time. Then we need to work out how much time
        // is taken per block and multiply by the total number of remaining blocks.
        $timeElapsed = microtime(true) - $this->progressBarStartTime;
        $timePerBlock = $timeElapsed / ($this->progressBarCurrent ?: 1);
        $timeRemaining = $timePerBlock * ($this->progressBarLimit - $this->progressBarCurrent);

        // Format and output the progress bar to the console. This will use VT100 escape
        // codes to delete any existing lines and overwrite them. We start with  title
        // if one is provided, followed by the progress bar and time elapsed/estimate
        if ($this->progressBarActive) {
            if (!empty($this->progressBarTitle)) {
                print("\033[1A\033[2K");
            }
            print("\033[1A\033[2K");
            print("\033[1A\033[2K");
        }

        if (!empty($this->progressBarTitle)) {
            printf("\033[37;41m%s\033[0m", $this->progressBarTitle);
            print PHP_EOL;
        }

        printf(
            "%d/%d [\033[33m%s\033[0m] %d%% (%.2fMb)",
            $this->progressBarCurrent,
            $this->progressBarLimit,
            str_pad(str_repeat('#', $numberOfBlocks), 25, ' ', STR_PAD_RIGHT),
            $this->progressBarCurrentPercent,
            round(memory_get_usage() / 1024 / 1024, 2)
        );
        print PHP_EOL;
        printf(
            "%s elapsed / %s remaining",
            $this->secondsToClock($timeElapsed),
            $this->secondsToClock($timeRemaining)
        );
        print PHP_EOL;

        $this->progressBarActive = true;

        return;
    }

    /**
     * Converts a number of seconds into a human readable string and clock format
     * @param  float  $ts
     * @return string
     */
    private function secondsToClock(float $ts) : string
    {
        $days = floor($ts / 86400);
        $hours = floor(($ts - ($days * 86400)) / 3600);
        $minutes = floor(($ts - ($days * 86400) - ($hours * 3600)) / 60);
        $seconds = floor($ts - ($days * 86400) - ($hours * 3600) - ($minutes * 60));

        $string = '';
        if (!empty($days)) {
            $string .= sprintf('%d days, ', $days);
        }

        if (!empty($hours)) {
            $string .= sprintf('%d hours, ', $hours);
        }

        return $string . sprintf('%02d:%02d', $minutes, $seconds);
    }
}
