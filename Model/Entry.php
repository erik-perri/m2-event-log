<?php

namespace Ryvon\EventLog\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * @method int getEntryId()
 *
 * @method Entry setDigestId(int $id)
 * @method int getDigestId()
 *
 * @method Entry setEntryLevel(string $level)
 * @method string getEntryLevel()
 *
 * @method Entry setEntryMessage(string $message)
 * @method string getEntryMessage()
 *
 * @method Entry setEntryContext(\Magento\Framework\DataObject $context)
 * @method \Magento\Framework\DataObject getEntryContext()
 *
 * @method Entry setEntryGroup(string $group)
 * @method string getEntryGroup()
 *
 * @method bool hasCreatedAt()
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $time)
 */
class Entry extends AbstractModel
{
    /**
     * @noinspection MagicMethodsValidityInspection
     * @return void
     */
    protected function _construct()
    {
        $this->_init(EntryResourceModel::class);
    }
}
