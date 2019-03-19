<?php

namespace Ryvon\EventLog\Model;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Stdlib\DateTime\DateTime;

class EntryResourceModel extends AbstractDb
{
    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param DateTime $date
     * @param JsonSerializer $jsonSerializer
     * @param DataObjectFactory $dataObjectFactory
     * @param Context $context
     * @param null $connectionName
     */
    public function __construct(
        DateTime $date,
        JsonSerializer $jsonSerializer,
        DataObjectFactory $dataObjectFactory,
        Context $context,
        $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);

        $this->date = $date;
        $this->jsonSerializer = $jsonSerializer;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @noinspection MagicMethodsValidityInspection
     * @return void
     */
    protected function _construct()
    {
        $this->_init('event_log_entry', 'entry_id');
    }

    /**
     * Process page data before saving
     *
     * @param AbstractModel $object
     * @return AbstractDb
     */
    protected function _beforeSave(AbstractModel $object): AbstractDb
    {
        try {
            if ($object instanceof Entry) {
                if ($object->isObjectNew() && !$object->hasCreatedAt()) {
                    $object->setCreatedAt($this->date->gmtDate());
                }

                $context = $object->getEntryContext();
                if ($context instanceof DataObject) {
                    $object->setData('entry_context', $context->convertToJson());
                }
            }
        } catch (\Exception $e) {
            $object->setData('entry_context', '[]');
        }

        return parent::_beforeSave($object);
    }

    /**
     * @param AbstractModel $object
     * @return AbstractDb
     */
    protected function _afterLoad(AbstractModel $object): AbstractDb
    {
        try {
            if ($object instanceof Entry) {
                $context = $object->getEntryContext();
                if (!($context instanceof DataObject)) {
                    $unserialized = $this->jsonSerializer->unserialize($context);
                    $object->setData('entry_context', $this->dataObjectFactory->create(['data' => $unserialized]));
                }
            }
        } catch (\Exception $e) {
            $object->setData('entry_context', $this->dataObjectFactory->create());
        }

        return parent::_afterLoad($object);
    }

}
