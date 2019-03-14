<?php

namespace App;

use App\Badges\AbstractBadge;
use App\Badges\BadgeFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ReadmeWizard
{
    protected $path;
    protected $readme = 'README.md';
    protected $notes = [];

    public function __construct($path)
    {
        if (ends_with($path, '.md')) {
            $this->readme = basename($path);
            $this->path = dirname($path) . '/';
        } else if (!ends_with($path, '/')) {
            $this->path = $path . '/';
        } else {
            $this->path = $path;
        }
    }

    protected function note($message)
    {
        $this->notes[] = $message;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    protected function firstFile($filenames): ?string
    {
        foreach ($filenames as $filename) {
            if (file_exists($this->path . $filename)) {
                return $filename;
            }
        }

        return null;
    }

    protected function checkInvalidLinks($content)
    {
        $known_invalid_links = [
            // Never used, but mistaken with packagist.org
            '//packagist.com',
            '//flagrow.github.io', // Not used anymore
            // These websites use www prefix
            '//patreon.com',
            '//paypal.me',
            // Missing HTTPS
            'http://packagist.org',
            'http://github.com',
            'http://flagrow.io',
        ];

        $matches = [];
        preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $content, $matches);

        foreach ($matches[0] as $link) {
            foreach ($known_invalid_links as $invalid_link) {
                if (str_contains($link, $invalid_link)) {
                    $this->note("Warning: link $link is invalid (matches $invalid_link)");
                }
            }
        }
    }

    public function fixedContent(): Readme
    {
        $readme_file_name = $this->firstFile(['readme.md', 'README.md']);

        if (!$readme_file_name) {
            throw new \Exception('no README file found');
        }
        if ($readme_file_name !== 'README.md') {
            $this->note('Warning: README file use incorrect case');
        }

        $readme_file = $this->path . $readme_file_name;

        $composer_file = $this->path . 'composer.json';
        if (!file_exists($composer_file)) {
            throw new \Exception('no composer.json file found');
        }

        $raw_template = file_get_contents('templates/flagrow.md');
        $raw_readme = file_get_contents($readme_file);

        $composer = new Collection(json_decode(file_get_contents($composer_file), true));

        $project = new Project();
        $project->repo = $composer->get('name');
        $project->name = null;
        $project->license = $composer->get('license');
        if (!$project->license) {
            $this->note('No license found in composer.json');
        }

        $project->licenseFile = $this->firstFile(['license.md', 'LICENSE.md', 'LICENSE.txt']);
        if (!$project->licenseFile) {
            $this->note('Warning: No LICENSE file');
        } else if (explode('.', $project->licenseFile)[0] !== 'LICENSE') {
            $this->note('Warning: LICENSE file use incorrect case');
        }

        $project->changelogFile = $this->firstFile(['changelog.md', 'CHANGELOG.md']);

        if ($project->changelogFile && $project->changelogFile !== 'CHANGELOG.md') {
            $this->note('Warning: CHANGELOG file use incorrect case');
        }

        $project->usesPackagist = true;
        $project->usesTravis = file_exists($this->path . '.travis.yml');

        $project->discussLink = Arr::get($composer, 'extra.flagrow.discuss');
        if ($project->discussLink) {
            if (!starts_with($project->discussLink, 'https://discuss.flarum.org/d/')) {
                $this->note('Discuss link might me malformed (missing https:// ?)');
            }
        } else {
            $this->note('No Discuss link found in composer file. Attempting auto-discovery');

            $matches = [];
            if (preg_match('~\[.*flarum\s+discuss.*\]\((https://discuss.flarum.org/d/.+)\)~i', $raw_readme, $matches) >= 1) {
                $project->discussLink = $matches[1];
            } else {
                $this->note('No Discuss link found via auto-discovery');
            }

        }

        $existing_sections = new Collection();
        $existing_titles = new Collection();
        $raw_existing_sections = preg_split('/\n## /', $raw_readme);

        foreach ($raw_existing_sections as $key => $section) {
            if ($key === 0) {
                $intro_lines = explode("\n", $section);

                $section_without_title_and_badges = '';

                foreach ($intro_lines as $lineNumber => $line) {
                    if ($lineNumber < 4 && starts_with($line, ['# ', '[!['])) {
                        // If we are at the start of the README and encounter one of these beginnings, it's most certainly the main title and the list of badges
                        // We ignore them as they are re-created from the template

                        // Try to extract the project name from the README
                        $matches = [];
                        if (preg_match('/#\s+(.+?)\s+by/', $line, $matches)) {
                            $project->name = $matches[1];
                        }

                        continue;
                    }

                    $section_without_title_and_badges .= $line . "\n";
                }

                $existing_sections->put('_introduction', trim($section_without_title_and_badges, "\n"));
            } else {
                $section_parts = explode("\n", $section, 2);

                $title = trim($section_parts[0]);
                $title_simple = strtolower($title);
                $existing_sections->put($title_simple, trim($section_parts[1], "\n"));
                $existing_titles->put($title_simple, $title);
            }
        }

        if (!$project->name) {
            throw new \Exception('Could not find project name');
        }

        $raw_template_lines = explode("\n", $raw_template);
        $current_section = '_introduction';

        $output = [];
        $sections_in_output = [
            '_introduction',
        ];

        $badges_string = (new Collection(BadgeFactory::badgesForProject($project)))->map(function ($badge) {
            /** @var AbstractBadge $badge */
            return $badge->markdown();
        })->implode(' ');

        foreach ($raw_template_lines as $line) {
            // An `@if (var)` syntax at the start of the line allows for conditionally rendering the line
            // The variable will be queried against the $project object. If it is falsy the line won't be rendered
            $matches = [];
            if (preg_match('/^@if\s*\(([a-zA-Z0-9]+)\)(.+)$/', $line, $matches) === 1) {
                $keyname = $matches[1];
                if ($project->$keyname) {
                    $line = $matches[2];
                } else {
                    continue;
                }
            }

            $matches = [];
            if (preg_match('/^##(\??) (.+)$/', $line, $matches) === 1) {
                $current_section = strtolower($matches[2]);

                $sections_in_output[] = $current_section;

                if ($matches[1] === '?') {
                    if ($existing_sections->has($current_section)) {
                        $output[] = '## ' . $matches[2] . "\n";
                        $output[] = $existing_sections->get($current_section);
                    } else {
                        // When we skip sections we need to remove some blank lines, otherwise there are two many carriage returns in the output
                        // If the line before the removed section is blank, we remove it from the output
                        if (empty($output[count($output) - 1])) {
                            array_pop($output);
                        }
                    }
                } else {
                    $output[] = '## ' . $matches[2];
                }
            } else if (str_contains($line, '{{ ... }}')) {
                $section_content = $existing_sections->get($current_section);
                $output[] = str_replace('{{ ... }}', $section_content, $line);
                $sections_in_output[] = $section_content;
            } else {
                $line_out = $line;

                $replace_tokens = [
                    'repo',
                    'name',
                    'licenseFile',
                    'changelogFile',
                    'discussLink',
                ];

                foreach ($replace_tokens as $token) {
                    $line_out = str_replace("{{ $token }}", $project->$token, $line_out);
                }

                $line_out = str_replace("{{ badges }}", $badges_string, $line_out);

                $output[] = $line_out;
            }
        }

        // Make sure the file doesn't start with \n and does finish with a single \n
        $output_string = trim(implode("\n", $output), "\n") . "\n";

        $remaining_existing_sections = $existing_sections->filter(function ($section, $title) use ($sections_in_output) {
            return !in_array($title, $sections_in_output);
        });

        if (preg_match('/\n+##\?\n+/', $output_string)) {
            $jocker_output = '';

            if ($remaining_existing_sections->count()) {
                foreach ($remaining_existing_sections as $title_simple => $content) {
                    // TODO: keep original title format (here it's lowercased)
                    $jocker_output .= "\n\n" . '## ' . $existing_titles->get($title_simple) . "\n\n";
                    $jocker_output .= $content;
                }
            }

            $output_string = preg_replace('/\n+##\?\n+/', "\n\n" . trim($jocker_output, "\n") . "\n\n", $output_string);
        } else if ($remaining_existing_sections->count()) {
            throw new \Exception('Some existing sections can\'t fit in the template (' . $remaining_existing_sections->implode(', ') . ')');
        }

        $this->checkInvalidLinks($output_string);

        if (!str_contains($output_string, 'https://discuss.flarum.org/d/5151')) {
            $this->note('No Bazaar link found in the README');
        }

        $readme = new Readme();
        $readme->path = $readme_file;
        $readme->currentMarkdown = $raw_readme;
        $readme->fixedMarkdown = $output_string;
        $readme->notes = $this->getNotes();

        return $readme;
    }
}
