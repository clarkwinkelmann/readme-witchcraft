<?php

namespace App\Badges;

use App\Project;

class BadgeFactory
{
    public static function badgesForProject(Project $project): array
    {
        $badges = [];

        if ($project->license) {
            switch ($project->license) {
                case 'mit':
                case 'MIT':
                    $badges[] = new MITLicenseBadge($project);
                    break;
                case 'cc-by-nc-nd':
                    $badges[] = new CCByNCNDBadge($project);
                    break;
                default:
                    throw new \Exception('Unknown license ' . $project->license);
            }
        }

        if ($project->usesTravis) {
            $badges[] = new TravisBadge($project);
        }

        if ($project->usesPackagist) {
            $badges[] = new PackagistVersionBadge($project);
            $badges[] = new PackagistDownloadsBadge($project);
        }

        $badges[] = new SupportUsBadge();
        $badges[] = new DiscordBadge();

        return $badges;
    }
}
