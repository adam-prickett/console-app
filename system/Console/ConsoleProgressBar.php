<?php
/**
 * Console App Framework
 *
 * @author Adam Prickett <adam.prickett@ampersa.co.uk>
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

    /**
     * Output and begin a progress bar
     * @param  int $limit
     * @return void
     */
    public function progressBar($limit = 100, $start = 0)
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
        $this->progressBarCurrentPercent = floor($start/$limit);
        $this->progressBarTitle = null;
        $this->progressBarCompleteMessage = null;

        return $this;
    }

    /**
     * Start the progress bar
     * @return void
     */
    public function startProgressBar()
    {
        $this->drawProgressBar();
    }

    /**
     * Set the Progress Bar title
     * @param  string $title
     * @return self
     */
    public function setProgressBarTitle($title)
    {
        $this->progressBarTitle = $title;

        return $this;
    }

    /**
     * Set the message to display once the progress bar has completed
     * @param  string $message
     * @return self
     */
    public function setProgressBarCompleteMessage($message)
    {
        $this->progressBarCompleteMessage = $message;

        return $this;
    }

    /**
     * Advance the progess bar by 1 (or provided) unit(s)
     * @param  integer $num
     * @return void
     */
    public function advanceProgressBar($num = 1)
    {
        // Check that a progress bar has actually been started
        if (!$this->progressBarStarted) {
            return;
        }

        // Don't let the progress bar overflow
        if ($this->progressBarCurrent+$num > $this->progressBarLimit) {
            return;
        }

        $this->progressBarCurrent = $this->progressBarCurrent+$num;

        $this->drawProgressBar();
    }

    /**
     * Set the progress bar to $num
     * @param integer $num
     */
    public function setProgressBar($num)
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
        $percentComplete = ceil(($this->progressBarCurrent/$this->progressBarLimit)*100);
        $percentPerBlock = 100/$this->progressBarLength;
        $numberOfBlocks = ceil($percentComplete/$percentPerBlock);

        $this->progressBarCurrentPercent = $percentComplete;

        // Print the progress bar
        if ($this->progressBarActive) {
            // Move up 1 line and delete the entire line (via VT100 escape codes)
            if (!empty($this->progressBarTitle)) {
                print("\033[1A\033[2K");
            }
            print("\033[1A\033[2K");
        }
        if (!empty($this->progressBarTitle)) {
            printf("\033[37;41m%s\033[0m", $this->progressBarTitle);
            print PHP_EOL;
        }
        printf("%d/%d [\033[33m%s\033[0m] %d%%", $this->progressBarCurrent, $this->progressBarLimit, str_pad(str_repeat('#', $numberOfBlocks), 25, ' ', STR_PAD_RIGHT), $this->progressBarCurrentPercent);
        print PHP_EOL;

        $this->progressBarActive = true;

        return;
    }
}
