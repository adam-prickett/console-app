# Console App Framework

Simple framework for console apps

## Usage

Run ```php run``` to list available commands:

```
$ php run
---------------
Available commands:
---------------
example - Example command - prints the date and time
new - Create a new command from stub
---------------
```

### Create a new Command

```
$ php run new --help

Create a new command from stub file and place in the commands directory

php run new NAME COMMAND

Options
---------

  --description     Set the description for the command listing
  --namespace       Override the default namespace for Commands
  --dir             Override the default commands directory (relative to run script)
  --quiet/-q        Silences output
  --help            Outputs this help message
```

For example: 
```
$ php run new ProcessCommand process --description="Process a file"
```

### Global Options

```
--help          Print the help text for the command, if available
--quiet/-q      Silence any output from the command
```

## Commands

Commands are placed in the commands/ directory and are enumerated automatically.

### Command Template

```php
<?php

namespace Commands;

use System\Console\Command;

class ExampleCommand extends Command
{
    /**
     * Setup the Command
     * @return void
     */
    public function setup()
    {
        $this->setCommand('example')
                ->setDescription('Example command - prints the date and time')
                ->requiresOption(['timestamp', 't'])
                ->requiresArgument('format')
                ->acceptsArgument('timezone');
    }

    /**
     * Provides the help text for this command
     * @return string
     */
    public function help()
    {
        return <<<EOS
This is the help text for this command.
Here we can explain the various options available.

Options
---------

  -t/--timestamp    Provides the timestamp to use, defaults to current
  -q/--quiet        Silences output
EOS;
    }

    /**
     * Run the program
     * @return Response
     */
    public function run()
    {
        $timestamp = $this->option(['timestamp', 't'], time());

        $this->output(date($this->argument('format'), $timestamp));
    }
}

```

## Console Output

### Styled Output

Beautified console output can be achieved through utility functions, such as 

```
$this->output()
$this->info()
$this->highlight()
$this->warn()
$this->danger()
$this->error()
```

You may also apply styling to text using the second argument:

```
$this->danger('Danger text', ['underline', 'bold']);
```

### Progress Bar

Display a progress bar for long-running tasks:

```
// Init a progress bar
// Pass the upper limit of the progress as the first argument
$this->progressBar(10);

// Set a title
$this->setProgressBarTitle('Progress...');

// Set a message to display on completion
$this->setProgressBarCompleteMessage('Completed task');

// Begin
$this->startProgressBar();

for ($number = 0; $number < 10; $number++) {
    // Increment the progress bar
    $this->advanceProgressBar();

    // Or, advance by multiple
    // $this->advanceProgressBar(2);

    // Alternatively, set the progress manually
    // $this->setProgressBar(5);
}

// Mark the progress bar as complete
$this->completeProgressBar();
```

Example:

```
Progress...
5/10 [#############            ] 50%

```
