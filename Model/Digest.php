<?php

namespace Ryvon\EventLog\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * @method bool hasCreatedAt()
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $time)
 *
 * @method bool hasUpdatedAt()
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $time)
 *
 * @method bool hasStartedAt()
 * @method string getStartedAt()
 * @method void setStartedAt(string $time)
 *
 * @method bool hasFinishedAt()
 * @method string getFinishedAt()
 * @method void setFinishedAt(string $time)
 *
 * @method bool hasDigestKey()
 * @method string getDigestKey()
 * @method void setDigestKey(string $key)
 */
class Digest extends AbstractModel
{
    /**
     * @noinspection MagicMethodsValidityInspection
     * @return void
     */
    protected function _construct()
    {
        $this->_init(DigestResourceModel::class);
    }
}
