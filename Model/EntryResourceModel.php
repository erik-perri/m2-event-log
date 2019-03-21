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
     * @param string|null $connectionName
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
        if ($object instanceof Entry) {
            if ($object->isObjectNew() && !$object->hasCreatedAt()) {
                $object->setCreatedAt($this->date->gmtDate());
            }

            $this->storeEntryContext($object);
        }

        return parent::_beforeSave($object);
    }

    /**
     * @param Entry $entry
     */
    public function storeEntryContext(Entry $entry)
    {
        try {
            $context = $entry->getEntryContext();
            if ($context instanceof DataObject) {
                $entry->setData('entry_context', $context->convertToJson());
            }
        } catch (\Exception $e) {
            $entry->setData('entry_context', '[]');
        }
    }

    /**
     * @param AbstractModel $object
     * @return AbstractDb
     */
    protected function _afterLoad(AbstractModel $object): AbstractDb
    {
        if ($object instanceof Entry) {
            $this->loadEntryContext($object);
        }

        return parent::_afterLoad($object);
    }

    /**
     * @param Entry $entry
     */
    public function loadEntryContext(Entry $entry)
    {
        try {
            $context = $entry->getEntryContext();
            if (!($context instanceof DataObject)) {
                $unserialized = $this->jsonSerializer->unserialize($context);
                $entry->setData('entry_context', $this->dataObjectFactory->create(['data' => $unserialized]));
            }
        } catch (\Exception $e) {
            $entry->setData('entry_context', $this->dataObjectFactory->create());
        }
    }
}
