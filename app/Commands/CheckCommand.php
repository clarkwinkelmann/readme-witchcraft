<?php

namespace App\Commands;

use App\ReadmeWizard;
use NunoMaduro\ZeroFramework\Commands\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CheckCommand extends AbstractCommand
{
    protected $name = 'check';
    protected $description = 'Fix the given README';

    protected function getArguments()
    {
        return [
            ['path', InputArgument::OPTIONAL, 'Path to the README folder', '.'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['fix', null, InputOption::VALUE_NONE, 'Apply fixes without asking'],
        ];
    }

    public function handle(): void
    {
        $readme = (new ReadmeWizard($this->argument('path')))->fixedContent();

        if ($readme->currentMarkdown === $readme->fixedMarkdown) {
            $this->info('You\'re all good !');

            foreach ($readme->notes as $note) {
                $this->warn($note);
            }
        } else {
            $this->warn('Found issues with the readme !');

            echo $readme->fixedMarkdown;

            foreach ($readme->notes as $note) {
                $this->warn($note);
            }

            if ($this->option('fix') || $this->confirm('Fix the file with the content shown above ?')) {
                file_put_contents($readme->path, $readme->fixedMarkdown);

                $this->info('README updated');
            } else {
                $this->info('Did nothing');
            }
        }
    }
}
