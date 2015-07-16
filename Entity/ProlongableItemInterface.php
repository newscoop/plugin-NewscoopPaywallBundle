<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Entity;

/**
 * Prolongable Item interface.
 */
interface ProlongableItemInterface
{
    /**
     * Gets the Is prolonged?.
     *
     * @return bool
     */
    public function getProlonged();

    /**
     * Sets the Is prolonged?.
     *
     * @param bool $prolonged the prolonged
     *
     * @return self
     */
    public function setProlonged($prolonged);
}
