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

?>
<h1>Buy <?php echo $project->title; ?></h1>
<h3>Price <?php echo $project->payment->get('price'); ?></h3>
<div><a href="<?php echo $pay; ?>" class="btn btn-success">To Pay</a></div>