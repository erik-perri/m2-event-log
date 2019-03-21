<?php

namespace Ryvon\EventLog\Model;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class DigestResourceModel extends AbstractDb
{
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param EncryptorInterface $encryptor
     * @param Context $context
     * @param string $connectionName
     */
    public function __construct(
        EncryptorInterface $encryptor,
        Context $context,
        $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);

        $this->encryptor = $encryptor;
    }

    /**
     * @noinspection MagicMethodsValidityInspection
     * @return void
     */
    protected function _construct()
    {
        $this->_init('event_log_digest', 'digest_id');
    }

    /**
     * Process page data before saving
     *
     * @param AbstractModel $object
     * @return AbstractDb
     */
    protected function _beforeSave(AbstractModel $object): AbstractDb
    {
        if (($object instanceof Digest) && $object->isObjectNew() && !$object->hasDigestKey()) {
            $object->setDigestKey($this->encryptor->hash(uniqid('event-log', true)));
        }

        return parent::_beforeSave($object);
    }
}
