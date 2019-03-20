<?php

namespace Ryvon\EventLog\Helper;

use Ryvon\EventLog\Model\Digest;
use Ryvon\EventLog\Model\DigestRepository;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\RequestInterface;

class DigestRequestHelper
{
    /**
     * @var DigestRepository
     */
    private $digestRepository;

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @param DigestRepository $digestRepository
     * @param UrlInterface $urlInterface
     */
    public function __construct(DigestRepository $digestRepository, UrlInterface $urlInterface)
    {
        $this->digestRepository = $digestRepository;
        $this->urlInterface = $urlInterface;
    }

    /**
     * @param RequestInterface|null $request
     * @return Digest|null
     */
    public function getCurrentDigest($request)
    {
        $digestId = $request ? $request->getParam('digest_id') : null;
        return $digestId ?
            $this->digestRepository->getById($request->getParam('digest_id')) :
            $this->digestRepository->findNewestUnfinishedDigest();
    }

    /**
     * @param Digest $digest
     * @param array $params
     * @return string
     */
    public function getDigestUrl(Digest $digest, $params = []): string
    {
        return $this->urlInterface->getUrl('event_log/digest/index', array_merge($params, [
            'digest_id' => $digest->getId(),
        ]));
    }
}
