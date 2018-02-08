# Axo
## A Console App Micro-Framework

![license](https://img.shields.io/github/license/ampersa/console-app.svg)
![Github tag](https://img.shields.io/github/tag/ampersa/console-app.svg)
![GitHub contributors](https://img.shields.io/github/contributors/ampersa/console-app.svg)

A simple framework for creating console apps and quick microservices

## Usage

Run ```php axo``` to list available commands:

```
$ php axo

Available commands:
--------------------
example    An example command
new        Create a new command
version    Print the current running version of Axo
```

### Create a new Command

```
$ php axo new --help
Create a new command

USAGE
php axo new NAME COMMAND

ARGUMENTS
name             The name of the command file
command          The command signature to run the new command

OPTIONS
--namespace      The namespace to create the command in
--description    A description for the command
--directory      The directory to store the new command in
--help           Displays this help text
--quiet          Silences the commands output
```

For example:
```
$ php axo new ProcessCommand process --description="Process a file"
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

use System\Log\Log;
use System\Console\Command;

class ExampleCommand extends Command
{
    /**
     * Signature formats:
     *  COMMAND (DESCRIPTION) {--OPTION|-O} {--OPTIONAL?} {ARGUMENT} {ARGUMENT?}
     *     1          2              3             4           5          6
     *
     * 1) The command to run this Command via run
     * 2) An optional description that displays in the command list
     * 3) A compulsary option, with short alias
     * 4) An optional option
     * 5) A compulsary argument
     * 6) An optional argument
     */
    protected $signature = 'example (Prints the date and time)  {--timestamp|-t}
                                                                {format}
                                                                {timezone?}';

    /**
     * Setup the Command
     * @return void
     */
    public function setup()
    {
        // The $signature can instead be specified fluently
        //
        // $this->setCommand('example')
        //        ->setDescription('Example command - prints the date and time')
        //        ->requiresOption(['timestamp', 't'])
        //        ->requiresArgument('format')
        //        ->acceptsArgument('timezone');
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

## Dependency Injection

Type-hinted dependencies are automatically injected into command constructors and the ```run()``` method.

**You must call ```parent::__construct();``` within a custom constructor to ensure the Command initialises correctly.**

Arguments are also automatically injected into the ```run()``` method with matching variable names:

```php
<?php

namespace Commands;

use Carbon\Carbon;
use System\Console\Command;

class ExampleCommand extends Command
{
    protected $signature = 'example (Prints the date and time)  {format}';

    /** @var Carbon\Carbon */
    protected $carbon;

    public function __construct(Carbon $carbon)
    {
        parent::__construct();

        $this->carbon = $carbon;
    }

    public function run(string $format)
    {
        $this->output('Format: '. $format);
        $this->output($this->carbon->now()->format($format));
    }
}

```

```
$ php axo example d M Y
Format: d M Y
08 Feb 2018
```

## Console Output

### Styled Output

Beautified console output can be achieved through utility functions, such as

```php
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

```php
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

## Shortcut usage

axo may be run without the _php_ prefix:

```
$ chmod +x axo

$ ./axo command
```
