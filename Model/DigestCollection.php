<?php

namespace Ryvon\EventLog\Model;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method Digest getFirstItem()
 * @method Digest getLastItem()
 * @method Digest[] getItems()
 */
class DigestCollection extends AbstractCollection
{
    /**
     * @noinspection MagicMethodsValidityInspection
     * @return void
     */
    protected function _construct()
    {
        $this->_setIdFieldName('digest_id');
        $this->_init(Digest::class, DigestResourceModel::class);
    }
}
