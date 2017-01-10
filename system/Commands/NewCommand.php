<?php

namespace System\Commands;

use System\Console\Command;

class NewCommand extends Command
{
    /**
     * Setup the Command
     * @return void
     */
    public function setup()
    {
        $this->setCommand('new')
                ->setDescription('Create a new command from stub')
                ->requiresArgument('name')
                ->requiresArgument('command');
    }

    /**
     * Provides the help text for this command
     * @return string
     */
    public function help()
    {
        return <<<EOS
Create a new command from stub file and place in the commands directory

php run new NAME COMMAND

Options
---------

  --description     Set the description for the command listing
  --namespace       Override the default namespace for Commands
  --dir             Override the default commands directory (relative to run script)
  --quiet/-q        Silences output
  --help            Outputs this help message
EOS;
    }

    /**
     * Run the program
     * @return Response
     */
    public function run()
    {
        if (file_exists(FRONT_CONTROLLER_PATH.$this->option('dir', '/commands').'/'.$this->argument('name').'.php')) {
            $this->error(sprintf('Command %s already exists', $this->argument('name')));
            die;
        }

        if (!is_writable(FRONT_CONTROLLER_PATH.$this->option('dir', '/commands'))) {
            $this->error(sprintf('Command directory (%s) is not writable', $this->argument('dir', '/commands')));
            die;
        }

        $stubFile = file_get_contents(FRONT_CONTROLLER_PATH.'/storage/stubs/command.stub');

        $variables = [ 
            'COMMAND_NAME' => $this->argument('name'), 
            'COMMAND_COMMAND' => $this->argument('command'), 
            'COMMAND_DESCRIPTION' => $this->option('description', ''), 
            'COMMAND_NAMESPACE' => $this->option('namespace', 'Commands'),
        ];

        $newFileContents = $this->parseStubVariables($stubFile, $variables);

        // $this->output($newFile);
        $newFile = fopen(FRONT_CONTROLLER_PATH.$this->option('dir', '/commands').'/'.$this->argument('name').'.php', 'w');
        fwrite($newFile, $newFileContents);
        fclose($newFile);

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
