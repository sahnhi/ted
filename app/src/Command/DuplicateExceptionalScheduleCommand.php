<?php

declare(strict_types=1);

namespace App\Command;

use App\Doctrine\Entity\ExceptionalSchedule;
use App\Doctrine\Entity\ServiceRules;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Repository\ExceptionalScheduleRepository;
use App\Doctrine\Repository\ServiceRulesRepository;
use App\Doctrine\Repository\Sylius\ShippingMethodRepository;
use App\Services\Sentry\ReportingInterface;
use Sentry\Severity;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

final class DuplicateExceptionalScheduleCommand extends Command
{
    private const SHIPPING_METHOD_TO_COPY = [
        'VIR' => [
          'VIR RETOUR',
          'VIR-GO',
          'VIR-PREMIUM',
          'VIR-Petit-Colis',
          'VIR-CONFORT',
          'VIR-EXPRESS',
        ],
        'E-DELIV' => [
            'E-DELIV-PREMIUM',
            'E-DELIV-GO',
            'E-DELIV-Petit-colis',
            'E-DELIV-CONFORT',
            'E-DELIV-EXPRESS',
            'E-DELIV RETOUR',
            'E-DELIV-RETOUR',
            'E-DELIV_RETOUR_GO',
        ],
        'FRANCE-EXPRESS' => [
            'FRANCE-EXPRESS-Petit-Colis',
        ],
        'DHL' => [
            'DHL-Petit-Colis',
        ],
    ];

    private const SERVICE_RULES_TO_DUPLICATE = [
        'VIR Belgique' => [
            'VIR EXPRESS Belgique',
            'VIR CONFORT Belgique',
            'VIR PREMIUM Belgique',
        ],
        'E-DELIV 2H30 - Dpt 7895' => [
            'E-DELIV CONFORT 2H30 - Dpt 7895',
            'E-DELIV PREMIUM 2H30 - Dpt 7895',
            'E-DELIV GO - Dpt 7895',
        ],
        'E-DELIV 2H30 - Dpt 7791' => [
            'E-DELIV CONFORT 2H30 - Dpt 7791',
            'E-DELIV PREMIUM 2H30 - Dpt 7791',
            'E-DELIV GO - Dpt 7791',
        ],
        'E-DELIV 5H - Dpt 7895' => [
            'E-DELIV EXPRESS 5H- Dpt 7895',
            'E-DELIV 5H - Dpt 7895 Petit Colis',
            'E-DELIV RETOUR 5h - Dpt 7895',
        ],
        'E-DELIV 5H - Dpt 7791' => [
            'E-DELIV EXPRESS 5H - Dpt 7791',
            'E-DELIV 5H - Dpt 7791 Petit Colis',
            'E-DELIV RETOUR 5h - Dpt 7791',
        ],
        'FE-31' => [
            'FE31-Petit-Colis',
        ],
        'FE-34' => [
            'FE34-Petit-Colis',
        ],
    ];

    public function __construct(
        private readonly ShippingMethodRepository $shippingMethodRepository,
        private readonly ServiceRulesRepository $serviceRulesRepository,
        private readonly ReportingInterface $reporting,
        private readonly ExceptionalScheduleRepository $exceptionScheduleRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:duplicate-exceptionnal-schedule')
            ->setDescription('duplicate exceptionnal schedule from existing shippingMethod and serviceRules to new one for paid delivery')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'dry run mode')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputStyle = new SymfonyStyle($input, $output);
        $stopwatch = new Stopwatch();
        $stopwatch->start('duplicate-exceptionnal-schedule');

        $dryRun = $input->getOption('dry-run');

        if ($dryRun) {
            $outputStyle->newLine();
            $outputStyle->note('Dry run enable, nothing will be insert');
            $outputStyle->newLine();
        }

        foreach (self::SHIPPING_METHOD_TO_COPY as $originalShippingMethodCode => $newShippingMethodCodes) {
            /** @var ShippingMethod $originalShippingMethod */
            $originalShippingMethod = $this->shippingMethodRepository->findOneBy(['code' => $originalShippingMethodCode]);

            $exceptionalSchedulesToDuplicate = $originalShippingMethod->getExceptionalSchedule();

            foreach ($newShippingMethodCodes as $newShippingMethodCode) {
                /** @var ShippingMethod $newShippingMethod */
                $newShippingMethod = $this->shippingMethodRepository->findOneBy(['code' => $newShippingMethodCode]);

                if (!$newShippingMethod instanceof ShippingMethod) {
                    $this->reporting->reportIssue(
                        sprintf('[CLI][MIGRATION] shippingMethod %s does not exist', $newShippingMethodCode),
                        Severity::error(),
                    );

                    continue;
                }

                foreach ($exceptionalSchedulesToDuplicate as $exceptionalScheduleToDuplicate) {
                    $newExceptionalSchedule = new ExceptionalSchedule();
                    $newExceptionalSchedule->setEnd($exceptionalScheduleToDuplicate->getEnd());
                    $newExceptionalSchedule->setStart($exceptionalScheduleToDuplicate->getStart());
                    $newExceptionalSchedule->setType($exceptionalScheduleToDuplicate->getType());
                    $newExceptionalSchedule->setCalendarDays($exceptionalScheduleToDuplicate->getCalendarDays());
                    $newExceptionalSchedule->setWarehouse($exceptionalScheduleToDuplicate->getWarehouse());
                    $newExceptionalSchedule->setShippingMethod($newShippingMethod);

                    if (!$dryRun) {
                        $this->exceptionScheduleRepository->add($newExceptionalSchedule);
                    }
                }
            }
        }

        foreach (self::SERVICE_RULES_TO_DUPLICATE as $serviceRulesNameToDuplicate => $newServiceRulesNames) {
            $serviceRulesToDuplicate = $this->serviceRulesRepository->findOneBy(['name' => $serviceRulesNameToDuplicate]);
            if (!$serviceRulesToDuplicate instanceof ServiceRules) {
                continue;
            }

            $exceptionalSchedulesToDuplicate = $serviceRulesToDuplicate->getExceptionalSchedule();

            foreach ($newServiceRulesNames as $newServiceRulesName) {
                $newServiceRules = $this->serviceRulesRepository->findOneBy(['name' => $newServiceRulesName]);

                if (!$newServiceRules) {
                    continue;
                }

                foreach ($exceptionalSchedulesToDuplicate as $exceptionalScheduleToDuplicate) {
                    $newExceptionalSchedule = new ExceptionalSchedule();
                    $newExceptionalSchedule->setEnd($exceptionalScheduleToDuplicate->getEnd());
                    $newExceptionalSchedule->setStart($exceptionalScheduleToDuplicate->getStart());
                    $newExceptionalSchedule->setType($exceptionalScheduleToDuplicate->getType());
                    $newExceptionalSchedule->setCalendarDays($exceptionalScheduleToDuplicate->getCalendarDays());
                    $newExceptionalSchedule->setWarehouse($exceptionalScheduleToDuplicate->getWarehouse());
                    $newExceptionalSchedule->setServiceRules($newServiceRules);

                    if (!$dryRun) {
                        $this->exceptionScheduleRepository->add($newExceptionalSchedule);
                    }
                }
            }
        }

        // Summary
        $event = $stopwatch->stop('duplicate-exceptionnal-schedule');

        $outputStyle->newLine();
        $outputStyle->section('<info>Summary</info>');
        $outputStyle->definitionList(
            ['Duration' => sprintf('command runs for %s milliseconds', ceil($event->getDuration()))],
            ['Memory usage' => Helper::formatMemory($event->getMemory())],
        );

        return 0;
    }
}
