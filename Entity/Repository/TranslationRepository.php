<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity\Repository;

use Gedmo\Translatable\Entity\Repository\TranslationRepository as BaseRepository;
use Gedmo\Translatable\TranslatableListener;
use Doctrine\ORM\Query;

/**
 * Translation repository.
 */
class TranslationRepository extends BaseRepository
{
    /**
     * Add hints to the query.
     *
     * @param Query       $query  Query
     * @param string|null $locale Lecale to which fallback
     *
     * @return Query
     */
    public function setTranslatableHints(Query $query, $locale = null)
    {
        $query->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );
        $query->setHint(
            TranslatableListener::HINT_INNER_JOIN,
            false
        );
        $query->setHint(
            TranslatableListener::HINT_TRANSLATABLE_LOCALE,
            $locale
        );
        $query->setHint(
            TranslatableListener::HINT_FALLBACK,
            true
        );

        return $query;
    }
}
