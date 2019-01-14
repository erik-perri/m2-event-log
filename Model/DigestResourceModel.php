<?php

namespace Ryvon\EventLog\Model;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\DateTime\DateTime;

class DigestResourceModel extends AbstractDb
{
    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param DateTime $date
     * @param EncryptorInterface $encryptor
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string $connectionName
     */
    public function __construct(
        DateTime $date,
        EncryptorInterface $encryptor,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);

        $this->date = $date;
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
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object instanceof Digest) {
            if ($object->isObjectNew()) {
                if (!$object->hasCreatedAt()) {
                    $object->setCreatedAt($this->date->gmtDate());
                }
                if (!$object->hasDigestKey()) {
                    $object->setDigestKey($this->encryptor->hash(uniqid('event-log', true)));
                }
            }
            $object->setUpdatedAt($this->date->gmtDate());
        }

        return parent::_beforeSave($object);
    }

}
