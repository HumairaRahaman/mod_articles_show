<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_show
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\ArticlesShow\Site\Helper;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
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
        $user = $app->getIdentity();

        /** @var ArticlesModel $model */
        $model = $app->bootComponent('com_content')->getMVCFactory()->createModel('Articles', 'Site', ['ignore_request' => true]);

        // Set application parameters in model
        $model->setState('params', $app->getParams());

        $model->setState('list.start', 0);
        $model->setState('filter.published', 1);

        // Set the filters based on the module params

        // This module does not use tags data
        $model->setState('load_tags', false);

        // Access filter
        $access     = !ComponentHelper::getParams('com_content')->get('show_noauth');
        $authorised = Access::getAuthorisedViewLevels($user->get('id'));
        $model->setState('filter.access', $access);

        // Category filter
        $model->setState('filter.category_id', $params->get('catid', []));

        // State filter
        $model->setState('filter.condition', 1);

        // User filter
        $model->setState('option.value', (int) $params->get('user_id'));


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

        foreach ($items as &$item) {
            $item->slug    = $item->id . ':' . $item->alias;

            if ($access || \in_array($item->access, $authorised)) {
                // We know that user has the privilege to view the article
                $item->link = Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language));
            } else {
                $item->link = Route::_('index.php?option=com_users&view=login');
            }
        }

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