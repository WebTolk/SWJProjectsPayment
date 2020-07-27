<?php
/**
 * @package    SW JProjects Payment
 * @version    __DEPLOY_VERSION__
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2019 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;

class PlgSystemSWJProjects_Payment extends CMSPlugin
{

	/**
	 * Add fields.
	 *
	 * @param   Form   $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function onContentPrepareForm($form, $data)
	{
		$formName = $form->getName();
		if (preg_match('#com_swjprojects\.project_#', $formName))
		{
			$app = Factory::getApplication();

			Form::addFormPath(__DIR__);
			Form::addFormPath(__DIR__);
			$form->loadFile('payment', false);

			if ($app->isClient('administrator') && $app->input->get('view') == 'project'
				&& $app->input->get('task') != 'apply')
			{
				$form->removeField('price', 'payment');
				$form->removeField('link', 'payment');
				if (empty($app->input->get('id')))
				{
					$form->removeField('payment_plugin_price', 'payment');
					$form->removeField('payment_plugin_currency', 'payment');
					$app->enqueueMessage('Payment enable only after save', 'warning');
				}
			}
		}
	}


	/**
	 * Change before save.
	 *
	 * @param   string  $context  Save context.
	 * @param   object  $data     The associated data for the item.
	 * @param   Form    $form     The associated data for the item.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function onContentNormaliseRequestData($context, $data, $form)
	{
		if ($context === 'com_swjprojects.project' && !empty($data->id) && !empty($data->translates)
			&& $data->download_type == 'paid')
		{
			$link = 'index.php?option=com_ajax&plugin=swjprojects_payment&group=system&step=checkout&format=html&id='
				. $data->id;
			foreach ($data->translates as $code => $translate)
			{
				if (!empty($translate['payment']) && !empty($translate['payment']['payment_plugin_price']))
				{
					$translate['payment']['link']  = $link;
					$translate['payment']['price'] = $translate['payment']['payment_plugin_price'];
					if (!empty($translate['payment']['payment_plugin_currency']))
					{
						$translate['payment']['price'] .= ' ' . $translate['payment']['payment_plugin_currency'];
					}

					$data->translates[$code] = $translate;
				}
			}
		}
	}

	/**
	 * Functions conteoloer.
	 *
	 * @throws  Exception
	 *
	 * @return mixed Function result.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function onAjaxSWJProjects_Payment()
	{
		$action = Factory::getApplication()->input->get('step');
		if (empty($action) || !method_exists($this, $action))
		{
			throw new Exception('Incorrect step', 500);
		}

		return $this->$action();
	}

	public function checkout()
	{
		JLoader::register('SWJProjectsHelperRoute', JPATH_SITE . '/components/com_swjprojects/helpers/route.php');
		JLoader::register('SWJProjectsHelperImages', JPATH_SITE . '/components/com_swjprojects/helpers/images.php');
		JLoader::register('SWJProjectsHelperTranslation', JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/translation.php');
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_swjprojects/models');
		$model = BaseDatabaseModel::getInstance('Project', 'SWJProjectsModel', array('ignore_request' => false));
		if ($project = $model->getItem())
		{
			$pay = 'index.php?option=com_ajax&plugin=swjprojects_payment&group=system&step=success&format=html&id='
				. $project->id;
			Factory::getDocument()->setTitle('Buy ' . $project->title);

			include_once(__DIR__ . '/checkout.php');
		}
	}

	public function success()
	{
		JLoader::register('SWJProjectsHelperRoute', JPATH_SITE . '/components/com_swjprojects/helpers/route.php');
		JLoader::register('SWJProjectsHelperImages', JPATH_SITE . '/components/com_swjprojects/helpers/images.php');

		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_swjprojects/models');
		$modelProject = BaseDatabaseModel::getInstance('Project', 'SWJProjectsModel', array('ignore_request' => false));
		if ($project = $modelProject->getItem())
		{
			JLoader::register('SWJProjectsHelperKeys', JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/keys.php');
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_swjprojects/tables');
			BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_swjprojects/models');

			$modelKey = BaseDatabaseModel::getInstance('Key', 'SWJProjectsModel', array('ignore_request' => true));
			$data     = array(
				'key'        => '',
				'id'         => 0,
				'project_id' => $project->id,
				'order'      => 'Payment plugin ' . rand(),
				'email'      => '',
				'date_start' => '',
				'date_end'   => '',
				'state'      => 1,
				'note'       => 'Generate from paymnet plugin',
			);
			if ($modelKey->save($data))
			{
				$key          = $modelKey->getItem();
				$downloadLink = Route::_(SWJProjectsHelperRoute::getDownloadRoute(null, null,
					$project->element, $key->key));

				Factory::getDocument()->setTitle('Thank you for your purchase');

				include_once(__DIR__ . '/success.php');
			}
		}
	}
}
