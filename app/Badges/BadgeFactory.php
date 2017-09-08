<?php

namespace App\Badges;

use App\Project;

class BadgeFactory
{
    public static function badgesForProject(Project $project): array
    {
        $badges = [];

        switch ($project->license) {
            case 'mit':
                $badges[] = new MITLicenseBadge($project);
                break;
            case 'cc-by-nc-nd':
                $badges[] = new CCByNCNDBadge($project);
                break;
        }

        if ($project->usesTravis) {
            $badges[] = new TravisBadge($project);
        }

        if ($project->usesPackagist) {
            $badges[] = new PackagistVersionBadge($project);
            $badges[] = new PackagistDownloadsBadge($project);
        }

        $badges[] = new PatreonBadge();
        $badges[] = new DiscordBadge();

        return $badges;
    }
}
