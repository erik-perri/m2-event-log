<?php

namespace Ryvon\EventLog\Console\Command;

use Ryvon\EventLog\Helper\DigestSender;
use Ryvon\EventLog\Model\Config;
use Ryvon\EventLog\Model\DigestRepository;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to finish the most recent (or specified) digest.
 */
class FinishDigestCommand extends Command
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DigestSender
     */
    private $digestSender;

    /**
     * @var DigestRepository
     */
    private $digestRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param State $state
     * @param Config $config
     * @param DigestRepository $digestRepository
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        State $state,
        Config $config,
        DigestRepository $digestRepository,
        ObjectManagerInterface $objectManager
    ) {
        parent::__construct();

        $this->state = $state;
        $this->config = $config;
        $this->digestRepository = $digestRepository;
        $this->objectManager = $objectManager;
    }

    /**
     * Configures the command.
     */
    protected function configure()
    {
        $this->setName('event-log:finish-digest')
            ->setDescription('Finish the current event log digest and send it out if enabled')
            ->setDefinition([
                new InputOption('digest-id', null, InputOption::VALUE_REQUIRED, 'Override the digest by ID'),
            ]);

        parent::configure();
    }

    /**
     * Executes the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);

            // We need to initialize this after the area code has been set since we're using LayoutInterface in
            // a constructor it relies on.  If we don't the command handler ends up crashing and preventing commands
            // from being run.
            $this->digestSender = $this->objectManager->create(DigestSender::class);
        } catch (LocalizedException $e) {
        }

        if ($input->getOption('digest-id')) {
            $digest = $this->digestRepository->getById($input->getOption('digest-id'));
        } else {
            $digest = $this->digestRepository->findNewestUnfinishedDigest();
        }

        if (!$digest) {
            $output->writeln('No digest found to send.');
            return;
        }

        if (!$this->digestSender->finishDigest($digest)) {
            $output->writeln('Failed to finish digest.');
            return;
        }

        if ($this->config->getEnableDigestEmail()) {
            if (!$this->config->getRecipients()) {
                $output->writeln('No email recipients configured.');
                return;
            }

            if (!$this->digestSender->sendDigest($digest)) {
                $output->writeln('Failed to send digest.');
                return;
            }
        }
    }
}
