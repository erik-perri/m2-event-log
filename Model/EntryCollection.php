<?php

namespace Ryvon\EventLog\Model;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method Entry getFirstItem()
 * @method Entry getLastItem()
 * @method Entry[] getItems()
 */
class EntryCollection extends AbstractCollection
{
    /**
     * @noinspection MagicMethodsValidityInspection
     * @return void
     */
    protected function _construct()
    {
        $this->_setIdFieldName('entry_id');
        $this->_init(Entry::class, EntryResourceModel::class);
    }
}
