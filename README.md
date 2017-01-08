# Console App Framework

Simple framework for console app

## Usage

Run ```php run``` to list available commands:

```
$ php run
---------------
Available commands:
---------------
example - Example command - prints the date and time
---------------
```

### Global Options

```
--help			Print the help text for the command, if available
--quiet/-q 		Silence any output from the command
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