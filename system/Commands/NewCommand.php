<?php
/**
 * Axo - Console Micro-Framework
 *
 * @author Ampersa Ltd <contact@ampersa.co.uk>
 * @license MIT
 * @copyright © Copyright Ampersa Ltd 2017.
 */

namespace System\Commands;

use System\Log\Log;
use System\Console\Command;

class NewCommand extends Command
{
    protected $signature = 'new (Create a new command)  {--namespace : The namespace to create the command in ?}
                                                        {--description : A description for the command ?}
                                                        {--directory : The directory to store the new command in ?}
                                                        {name : The name of the command file}
                                                        {command : The command signature to run the new command}';

    /**
     * Run the program
     * @return Response
     */
    public function run()
    {
        if (file_exists($this->option('dir', './commands').'/'.$this->argument('name').'.php')) {
            $this->error(sprintf('Command %s already exists', $this->argument('name')));
            die;
        }

        if (!is_writable($this->option('dir', './commands'))) {
            $this->error(sprintf('Command directory (%s) is not writable', $this->argument('dir', '/commands')));
            die;
        }

        $stubFile = file_get_contents(__DIR__.'/../Console/stubs/command.stub');

        $variables = [
            'COMMAND_NAME' => $this->argument('name'),
            'COMMAND_COMMAND' => $this->argument('command'),
            'COMMAND_DESCRIPTION' => $this->option('description', ''),
            'COMMAND_NAMESPACE' => $this->option('namespace', 'Commands'),
        ];

        $newFileContents = $this->parseStubVariables($stubFile, $variables);

        // Create the new Command file in place and put contents
        $newFile = fopen($this->option('dir', './commands').'/'.$this->argument('name').'.php', 'w');
        fwrite($newFile, $newFileContents);
        fclose($newFile);

        Log::info(sprintf('New Command %s created at %s', $this->argument('name'), $this->option('dir', './commands').'/'.$this->argument('name').'.php'));

        $this->output(sprintf('New command %s created', $this->argument('name')));
    }

    /**
     * Parse the stub variables
     * @param  string $stub
     * @param  array  $variables
     * @return string
     */
    private function parseStubVariables($stub, $variables)
    {
        return preg_replace_callback('/\{\$([\w]+)\}/', function ($matches) use ($variables) {
            if (array_key_exists($matches[1], $variables)) {
                return $variables[$matches[1]];
            }

            return null;
        }, $stub);
    }
}
