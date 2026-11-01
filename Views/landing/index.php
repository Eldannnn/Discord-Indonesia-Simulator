<?php

declare(strict_types=1);

/**
 * Main National Information Portal View (`Views/landing/index.php`).
 * Purely informational page displaying News, Macroeconomic Indicators, DPR Composition,
 * Active Laws, and Political Parties with their ideologies and descriptions.
 *
 * @var string $appName
 * @var string $activePage
 * @var string $simulationEra
 * @var array<string, mixed>|null $authUser
 * @var array<string, mixed> $telemetry
 * @var array<int, array<string, string>> $newsFeed
 * @var array<int, array<string, mixed>> $economyMetrics
 * @var array<int, array<string, mixed>> $politicalParties
 * @var array<string, mixed> $dprSummary
 * @var array<int, array<string, string>> $activeLaws
 * @var array<int, array<string, string>> $gazetteFeed
 * @var array<int, array<string, mixed>> $executiveUsers
 */
$componentsDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;

require $componentsDir . 'navbar.php';
?>

<main id="main-content" class="flex-1">
    <?php
    require $componentsDir . 'hero-section.php';
    require $componentsDir . 'economy-section.php';
    require $componentsDir . 'dpr-composition.php';
    require $componentsDir . 'active-laws-section.php';
    require $componentsDir . 'political-parties-section.php';
    require $componentsDir . 'gazette-timeline.php';
    ?>
</main>

<?php
require $componentsDir . 'login-modal.php';
require $componentsDir . 'citizenship-modal.php';
require $componentsDir . 'footer.php';
?>
