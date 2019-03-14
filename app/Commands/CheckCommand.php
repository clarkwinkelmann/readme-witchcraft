<?php

namespace App\Commands;

use App\ReadmeWizard;
use LaravelZero\Framework\Commands\Command;

class CheckCommand extends Command
{
    protected $signature = 'check {path : Path to the README folder} {--fix : Apply fixes without asking}';
    protected $description = 'Fix the given README';

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
