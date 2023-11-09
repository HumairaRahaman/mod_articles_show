<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_show
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\ArticlesShow\Site\Helper;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\Component\Content\Site\Model\ArticlesModel;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_articles_show
 *
 * @since  1.6
 */
class ArticlesShowHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Retrieve a list of article
     *
     * @param   Registry       $params  The module parameters.
     * @param   ArticlesModel  $model   The model.
     *
     * @return  mixed
     *
     * @since   4.2.0
     */
    public function getArticles(Registry $params, SiteApplication $app)
    {
        // Get the Dbo and User object
        $db   = $this->getDatabase();

        /** @var ArticlesModel $model */
        $model = $app->bootComponent('com_content')->getMVCFactory()->createModel('Articles', 'Site', ['ignore_request' => true]);

        // Set application parameters in model
        $model->setState('params', $app->getParams());

        $model->setState('list.start', 0);
        $model->setState('filter.published', 1);


        // Set the filters based on the module params
        $model->setState('list.limit', (int) $params->get('count', 5));


        // This module does not use tags data
        $model->setState('load_tags', false);



        // Category filter
        $model->setState('filter.category_id', $params->get('catid', []));

        // State filter
        $model->setState('filter.condition', 1);



        // Filter by language
        $model->setState('filter.language', $app->getLanguageFilter());

        // Featured switch
        $featured = $params->get('show_featured', '');
        if ($featured === '') {
            $model->setState('filter.featured', 'show');
        }

        // Set ordering
        $model->setState('list.ordering', $db->getQuery(true)->rand());

        $items = $model->getItems();

        return $items;
    }

    /**
     * Retrieve a list of articles
     *
     * @param   Registry       $params  The module parameters.
     * @param   ArticlesModel  $model   The model.
     *
     * @return  mixed
     *
     * @since   1.6
     *
     * @deprecated 4.3 will be removed in 6.0
     *             Use the non-static method getArticles
     *             Example: Factory::getApplication()->bootModule('mod_articles_show', 'site')
     *                          ->getHelper('ArticlesShowHelper')
     *                          ->getArticles($params, Factory::getApplication())
     */
    public static function getList(Registry $params, ArticlesModel $model)
    {
        return (new self())->getArticles($params, Factory::getApplication());
    }
}
