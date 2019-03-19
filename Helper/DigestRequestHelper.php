<?php

namespace Ryvon\EventLog\Helper;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Ryvon\EventLog\Model\Digest;
use Ryvon\EventLog\Model\DigestRepository;

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
     * DigestRequestHelper constructor.
     * @param DigestRepository $digestRepository
     * @param UrlInterface $urlInterface
     */
    public function __construct(DigestRepository $digestRepository, UrlInterface $urlInterface)
    {
        $this->digestRepository = $digestRepository;
        $this->urlInterface = $urlInterface;
    }

    /**
     * @param RequestInterface $request
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
    public function getDigestUrl(Digest $digest, $params = [])
    {
        return $this->urlInterface->getUrl('event_log/digest/index', array_merge($params, [
            'digest_id' => $digest->getId(),
        ]));
    }
}
