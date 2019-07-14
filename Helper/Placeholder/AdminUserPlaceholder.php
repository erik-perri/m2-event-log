<?php

namespace Ryvon\EventLog\Helper\Placeholder;

/**
 * Placeholder to replace {admin-user} with a link to edit user form.
 */
class AdminUserPlaceholder extends UserNamePlaceholder
{
    /**
     * The name context key.
     */
    const NAME_KEY = 'admin-user';

    /**
     * The ID context key.
     */
    const ID_KEY = 'admin-user-id';

    /**
     * @inheritDoc
     */
    public function getSearchString(): string
    {
        return 'admin-user';
    }
}
