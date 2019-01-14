<?php

namespace Ryvon\EventLog\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Ryvon\EventLog\Helper\DigestHelper;
use Ryvon\EventLog\Helper\DigestSender;
use Ryvon\EventLog\Model\Config;
use Ryvon\EventLog\Model\DigestRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @var DigestHelper
     */
    private $digestHelper;

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
     * @param DigestHelper $digestHelper
     * @param DigestRepository $digestRepository
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        State $state,
        Config $config,
        DigestHelper $digestHelper,
        DigestRepository $digestRepository,
        ObjectManagerInterface $objectManager
    )
    {
        parent::__construct();

        $this->state = $state;
        $this->config = $config;
        $this->digestHelper = $digestHelper;
        $this->digestRepository = $digestRepository;
        $this->objectManager = $objectManager;
    }

    /**
     *
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
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);

            // We need to initialize this after the area code has been set since we're using LayoutInterface in
            // a constructor it relies on
            $this->digestSender = $this->objectManager->create(DigestSender::class);
        } catch (LocalizedException $e) {
        }

        if (!$this->config->getEnableDigestEmail()) {
            $output->writeln('Digest email is not enabled.');
            return;
        }

        if (!$this->config->getRecipients()) {
            $output->writeln('No email recipients configured.');
            return;
        }

        if ($input->getOption('digest-id')) {
            $digest = $this->digestRepository->getById($input->getOption('digest-id'));
        } else {
            $digest = $this->digestHelper->findUnfinishedDigest();
        }

        if (!$digest) {
            $output->writeln('No digest found to send.');
            return;
        }

        if (!$this->digestSender->finishDigest($digest) || !$this->digestSender->sendDigest($digest)) {
            $output->writeln('Failed to send digest.');
        }
    }
}
