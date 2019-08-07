<?php

namespace Ryvon\EventLog\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Digest model
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
     * Initialize the model
     *
     * @noinspection MagicMethodsValidityInspection
     * @return void
     */
    protected function _construct()
    {
        $this->_init(DigestResourceModel::class);
    }

    /**
     * @return \DateTime|null
     */
    public function getStartedAtDateTime()
    {
        if (!$this->getStartedAt()) {
            return null;
        }

        try {
            return new \DateTime($this->getStartedAt());
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return \DateTime|null
     */
    public function getFinishedAtDateTime()
    {
        if (!$this->getFinishedAt()) {
            return null;
        }

        try {
            return new \DateTime($this->getFinishedAt());
        } catch (\Exception $e) {
            return null;
        }
    }
}
